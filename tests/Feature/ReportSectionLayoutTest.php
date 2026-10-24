<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\ReportTemplates\Configuration\Blocks;
use App\Modules\ReportTemplates\Configuration\Rules;
use App\Modules\ReportTemplates\Models\ReportTemplate;
use Tests\Support\ReportFixture as TemplateSampleData;
use App\Modules\ReportTemplates\Rendering\TemplateRenderer;
use App\Modules\ReportTemplates\UI\Pages\EditReportTemplate;
use Database\Seeders\DatabaseSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class ReportSectionLayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp(); $this->seed(DatabaseSeeder::class);
        $user = User::factory()->create(['can_access_admin' => true]);
        $user->assignRole('SuperAdmin'); $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    private function render(array $blocks): string
    {
        $definition = Blocks::defaults(); $definition['blocks'] = $blocks;

        return app(TemplateRenderer::class)->execute($definition, app(TemplateSampleData::class)->execute());
    }

    public function test_separator_and_two_columns_render_without_the_sample_banner(): void
    {
        $separator = Blocks::make('separator'); $separator['title'] = 'INFORMACIÓN DEL EQUIPO';
        $html = $this->render([$separator, Blocks::make('data_table')]);
        $this->assertStringContainsString('INFORMACIÓN DEL EQUIPO', $html);
        $this->assertStringContainsString('DATOS DEL CLIENTE', $html);
        $this->assertStringContainsString('INFORMACIÓN DEL SERVICIO', $html);
        $this->assertStringContainsString('background:#00858b;color:#ffffff', $html);
        $this->assertSame(2, substr_count($html, '<h3 class="data-section-title"'));
        $this->assertStringNotContainsString('PRUEBA · DATOS DE EJEMPLO · NO EMITIDO', $html);
    }

    public function test_table_title_and_column_headers_can_be_hidden_independently(): void
    {
        $block = Blocks::make('activities'); $block['title'] = 'Título oculto';
        $block['show_title'] = false; $block['show_table_header'] = false;
        $html = $this->render([$block]);
        $this->assertStringNotContainsString('Título oculto', $html);
        $this->assertStringNotContainsString('<thead', $html);
        $this->assertStringContainsString('Actividad 1:', $html);
        $block['show_title'] = true;
        $this->assertStringContainsString('Título oculto', $this->render([$block]));
    }

    public function test_column_titles_labels_and_manual_values_are_configurable_and_escaped(): void
    {
        $block = Blocks::make('data_table');
        $block['show_table_header'] = false; $block['show_labels'] = false;
        $block['data_sections'][0]['fields'] = [['label' => 'Etiqueta privada:', 'mode' => 'manual', 'text' => '<script>Texto manual</script>']];
        $html = $this->render([$block]);
        $this->assertStringNotContainsString('DATOS DEL CLIENTE', $html);
        $this->assertStringNotContainsString('Etiqueta privada:', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;Texto manual&lt;/script&gt;', $html);
    }

    public function test_editor_adds_and_persists_separator_and_data_table(): void
    {
        $template = ReportTemplate::first();
        Livewire::test(EditReportTemplate::class, ['record' => $template->id])
            ->set('newBlock', 'separator')->call('addBlock')->set('blockData.title', 'INFORMACIÓN DEL EQUIPO')
            ->set('blockData.separator_background', '#00858b')->call('save')->assertHasNoErrors()
            ->set('newBlock', 'data_table')->call('addBlock')->call('save')->assertHasNoErrors();
        $blocks = $template->fresh()->definition['blocks'];
        $this->assertSame('separator', $blocks[8]['type']);
        $this->assertSame('INFORMACIÓN DEL EQUIPO', $blocks[8]['title']);
        $this->assertSame('data_table', $blocks[9]['type']);
        $this->assertCount(2, $blocks[9]['data_sections']);
    }

    public function test_invalid_variable_or_style_in_data_table_is_rejected(): void
    {
        $definition = Blocks::defaults(); $definition['blocks'] = [Blocks::make('data_table')];
        $definition['blocks'][0]['data_sections'][0]['fields'][0]['variable'] = 'users.password';
        $definition['blocks'][0]['section_background'] = 'url(file:///etc/passwd)';
        $this->expectException(ValidationException::class);
        Rules::validate($definition);
    }
}
