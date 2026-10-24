<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\ReportTemplates\Configuration\Blocks;
use App\Modules\ReportTemplates\Models\ReportTemplate;
use Tests\Support\ReportFixture as TemplateSampleData;
use App\Modules\ReportTemplates\Rendering\PreviewRenderer;
use App\Modules\ReportTemplates\Rendering\TemplateRenderer;
use App\Modules\ReportTemplates\UI\Pages\EditReportTemplate;
use Database\Seeders\DatabaseSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportEditorSelectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp(); $this->seed(DatabaseSeeder::class);
        $user = User::factory()->create(['can_access_admin' => true]);
        $user->assignRole('SuperAdmin'); $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_preview_selection_edits_the_printed_line_instead_of_the_block_name(): void
    {
        $template = ReportTemplate::first();
        $editor = Livewire::test(EditReportTemplate::class, ['record' => $template->id])
            ->call('selectPreviewElement', 0, 'center', 0)->assertSet('selectedLine', 0);
        $keys = array_keys($editor->get('blockData')['header_columns']['center']['lines']);
        $editor->set('blockData.header_columns.center.lines.'.$keys[0].'.text', 'Título seleccionado')
            ->set('blockData.header_columns.center.lines.'.$keys[0].'.size', 19)
            ->call('save')->assertHasNoErrors();
        $block = $template->fresh()->definition['blocks'][0];
        $line = array_values($block['header_columns']['center']['lines'])[0];
        $this->assertSame('Título seleccionado', $line['text']);
        $this->assertSame(19, (int) $line['size']);
        $this->assertSame('INFORME TÉCNICO', $block['title']);
        $editor->call('selectBlock', 1)->assertSet('selectedColumn', null)->assertSet('selectedLine', null);
    }

    public function test_block_font_override_is_saved_and_rendered(): void
    {
        $template = ReportTemplate::first();
        Livewire::test(EditReportTemplate::class, ['record' => $template->id])
            ->call('selectPreviewElement', 1)->set('blockData.font_size', 14)->call('save')->assertHasNoErrors();
        $definition = $template->fresh()->definition;
        $this->assertSame(14, (int) $definition['blocks'][1]['font_size']);
        $html = app(TemplateRenderer::class)->execute($definition, app(TemplateSampleData::class)->execute());
        $this->assertStringContainsString('--report-font-size:14pt', $html);
    }

    public function test_selection_outside_the_allowed_header_is_rejected(): void
    {
        $template = ReportTemplate::first();
        Livewire::test(EditReportTemplate::class, ['record' => $template->id])
            ->call('selectPreviewElement', 0, 'center', 999)->assertStatus(422);
        Livewire::test(EditReportTemplate::class, ['record' => $template->id])
            ->call('selectPreviewElement', 1, 'center', 0)->assertStatus(422);
    }

    public function test_selection_bridge_and_outline_are_excluded_from_the_pdf_html(): void
    {
        $data = app(TemplateSampleData::class)->execute();
        $preview = app(PreviewRenderer::class)->execute(Blocks::defaults(), $data, 0, 'center', 0);
        $print = app(TemplateRenderer::class)->execute(Blocks::defaults(), $data);
        $this->assertStringContainsString('report-template-select', $preview);
        $this->assertStringContainsString('outline: 2px solid #2563eb', $preview);
        $this->assertStringNotContainsString('<script>', $print);
        $this->assertStringNotContainsString('report-template-select', $print);
    }
}
