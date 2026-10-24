<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\ReportTemplates\Actions\SaveReportTemplate;
use App\Modules\ReportTemplates\Actions\PublishReportTemplate;
use App\Modules\ReportTemplates\Configuration\Blocks;
use App\Modules\ReportTemplates\Models\ReportTemplate;
use App\Modules\ReportTemplates\Rendering\SafeRichText;
use App\Modules\ReportTemplates\Rendering\TemplateRenderer;
use App\Modules\ReportTemplates\Queries\TemplateSampleData;
use App\Modules\ReportTemplates\UI\Pages\EditReportTemplate;
use Database\Seeders\DatabaseSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class ReportTemplatesTest extends TestCase
{
    use RefreshDatabase;

    private function actor(): User
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::factory()->create(['can_access_admin' => true]);
        $user->assignRole('SuperAdmin');
        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        return $user;
    }

    public function test_editor_and_list_render_in_filament(): void
    {
        $this->actor();
        $template = ReportTemplate::first();
        $this->get('/admin/report-templates')->assertOk()->assertSee('Informe de mantenimiento');
        $this->get('/admin/report-templates/'.$template->id.'/edit')->assertOk()->assertSee('PDF de prueba');
    }

    public function test_user_without_permission_cannot_edit_templates(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::factory()->create())->get('/admin/report-templates')->assertForbidden();
    }

    public function test_editor_changes_blocks_saves_and_publishes(): void
    {
        $this->actor();
        $template = ReportTemplate::first();
        Livewire::test(EditReportTemplate::class, ['record' => $template->id])
            ->call('selectBlock', 4)->set('blockData.photo_columns', 1)
            ->set('blockData.title', 'Evidencias configuradas')->call('save')->assertHasNoErrors()
            ->set('newBlock', 'text')->call('addBlock')->set('blockData.text', '<p>Texto <strong>importante</strong></p>')
            ->call('moveBlock', 8, -1)->call('save')->assertHasNoErrors()->call('publish')->assertHasNoErrors();
        $template->refresh();
        $this->assertSame('Evidencias configuradas', $template->definition['blocks'][4]['title']);
        $this->assertSame(1, (int) $template->definition['blocks'][4]['photo_columns']);
        $this->assertCount(9, $template->definition['blocks']);
        $this->assertNotNull($template->published_version_id);
    }

    public function test_stale_save_does_not_overwrite_another_session(): void
    {
        $this->actor(); $template = ReportTemplate::first();
        $action = app(SaveReportTemplate::class);
        $action->execute($template->id, 0, ['name' => 'Primera edición'], $template->definition);
        $this->expectException(ValidationException::class);
        $action->execute($template->id, 0, ['name' => 'Segunda edición'], $template->definition);
    }

    public function test_invalid_variable_and_image_path_are_rejected(): void
    {
        $this->actor(); $template = ReportTemplate::first();
        $definition = Blocks::defaults();
        $definition['blocks'][0]['fields'] = ['users.password'];
        $definition['blocks'][0]['company_logo'] = '../../.env';
        $this->expectException(ValidationException::class);
        app(SaveReportTemplate::class)->execute($template->id, 0, ['name' => 'Inválida'], $definition);
    }

    public function test_rich_text_drops_scripts_and_attributes(): void
    {
        $result = SafeRichText::render('<p onclick="alert(1)">Texto<strong>seguro</strong><script>alert(2)</script><img src="file:///etc/passwd"></p>');
        $this->assertStringContainsString('<strong>seguro</strong>', $result);
        foreach (['onclick', '<script', 'alert(', '<img', 'file:'] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $result);
        }
    }

    public function test_published_version_remains_unchanged_after_draft_edit(): void
    {
        $this->actor(); $template = ReportTemplate::first();
        app(PublishReportTemplate::class)->execute($template->id);
        $published = $template->versions()->first()->definition;
        $draft = $template->definition; $draft['blocks'][0]['title'] = 'Cambio en borrador';
        app(SaveReportTemplate::class)->execute($template->id, 0, ['name' => $template->name], $draft);
        $this->assertSame($published, $template->versions()->first()->definition);
    }

    public function test_rendering_escapes_values_and_repeats_table_headers(): void
    {
        $this->actor(); $data = app(TemplateSampleData::class)->execute(24);
        $data['institution']['name'] = '<script>attack()</script>';
        $html = app(TemplateRenderer::class)->execute(Blocks::defaults(), $data);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('table-header-group', $html);
        $this->assertSame([], $data['activities']);
        $this->assertStringNotContainsString('Evidencia 24 B', $html);
    }
}
