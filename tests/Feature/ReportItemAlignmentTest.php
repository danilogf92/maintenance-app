<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\ReportTemplates\Configuration\Blocks;
use App\Modules\ReportTemplates\Configuration\DataTable;
use App\Modules\ReportTemplates\Configuration\Rules;
use App\Modules\ReportTemplates\Models\ReportTemplate;
use App\Modules\ReportTemplates\Queries\TemplateSampleData;
use App\Modules\ReportTemplates\Rendering\TemplateRenderer;
use App\Modules\ReportTemplates\UI\Pages\EditReportTemplate;
use Database\Seeders\DatabaseSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class ReportItemAlignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp(); $this->seed(DatabaseSeeder::class);
        $user = User::factory()->create(['can_access_admin' => true]);
        $user->assignRole('SuperAdmin'); $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    private function render(array $block): string
    {
        $definition = Blocks::defaults(); $definition['blocks'] = [$block];

        return app(TemplateRenderer::class)->execute($definition, app(TemplateSampleData::class)->execute());
    }

    public function test_items_can_override_column_alignment_without_fixed_label_cells(): void
    {
        $block = Blocks::make('data_table'); $section = &$block['data_sections'][0];
        $section['item_alignment'] = 'right';
        $section['fields'][0]['item_alignment'] = 'left';
        $section['fields'][1]['item_alignment'] = 'center';
        $section['fields'][1]['item_indent'] = 2.5;
        $html = $this->render($block);
        $this->assertStringContainsString('data-item-alignment="left" style="text-align:left;padding-left:1mm"', $html);
        $this->assertStringContainsString('data-item-alignment="center" style="text-align:center;padding-left:3.5mm"', $html);
        $this->assertStringContainsString('data-item-alignment="right"', $html);
        $this->assertStringNotContainsString('<table class="data-values">', $html);
        $this->assertStringContainsString('Cliente:</span><span class="data-value"', $html);
    }

    public function test_editor_saves_each_item_alignment_and_indent_and_reloads_them(): void
    {
        $template = ReportTemplate::first(); $block = Blocks::make('data_table');
        $block['data_sections'][0]['fields'][0]['item_alignment'] = 'left';
        $block['data_sections'][0]['fields'][1]['item_alignment'] = 'right';
        $block['data_sections'][0]['fields'][1]['item_indent'] = 4.5;
        Livewire::test(EditReportTemplate::class, ['record' => $template->id])
            ->set('newBlock', 'data_table')->call('addBlock')->set('blockData', $block)
            ->call('save')->assertHasNoErrors();
        $saved = $template->fresh()->definition['blocks'][8];
        $fields = array_values(array_values($saved['data_sections'])[0]['fields']);
        $this->assertSame('left', $fields[0]['item_alignment']);
        $this->assertSame('right', $fields[1]['item_alignment']);
        $this->assertEquals(4.5, $fields[1]['item_indent']);
        Livewire::test(EditReportTemplate::class, ['record' => $template->id])->call('selectBlock', 8)
            ->assertHasNoErrors()->assertSee('Alineación del ítem (etiqueta + valor)');
        $this->assertStringContainsString('text-align:right;padding-left:5.5mm', $this->render($saved));
    }

    public function test_existing_templates_keep_their_layout_until_alignment_is_chosen(): void
    {
        $block = Blocks::make('data_table');
        foreach ($block['data_sections'] as &$section) {
            unset($section['item_alignment']);
            foreach ($section['fields'] as &$field) { unset($field['item_alignment'], $field['item_indent']); }
            unset($field);
        }
        unset($section);
        $definition = Blocks::defaults(); $definition['blocks'] = [$block];
        $upgraded = DataTable::upgrade($definition)['blocks'][0];
        $this->assertSame('legacy', $upgraded['data_sections'][0]['item_alignment']);
        $this->assertSame('inherit', $upgraded['data_sections'][0]['fields'][0]['item_alignment']);
        $this->assertSame(8, substr_count($this->render($upgraded), '<table class="data-values">'));
        $upgraded['data_sections'][0]['fields'][0]['item_alignment'] = 'left';
        $html = $this->render($upgraded);
        $this->assertSame(7, substr_count($html, '<table class="data-values">'));
        $this->assertSame(1, substr_count($html, 'data-item-alignment="left"'));
    }

    public function test_alignment_and_indent_reject_css_injection_and_invalid_values(): void
    {
        $definition = Blocks::defaults(); $definition['blocks'] = [Blocks::make('data_table')];
        $definition['blocks'][0]['data_sections'][0]['fields'][0]['item_alignment'] = 'left;display:none';
        $definition['blocks'][0]['data_sections'][0]['fields'][0]['item_indent'] = -1;
        try { Rules::validate($definition); $this->fail('Invalid positioning accepted'); }
        catch (ValidationException $error) {
            $this->assertArrayHasKey('blocks.0.data_sections.0.fields.0.item_alignment', $error->errors());
            $this->assertArrayHasKey('blocks.0.data_sections.0.fields.0.item_indent', $error->errors());
        }
    }

    public function test_hiding_labels_preserves_the_aligned_value_and_its_typography(): void
    {
        $block = Blocks::make('data_table'); $block['show_labels'] = false;
        $block['data_sections'][0]['fields'][0]['value_style'] = ['size' => 12, 'color' => '#224466'];
        $html = $this->render($block);
        $this->assertStringNotContainsString('<span class="data-item-label"', $html);
        $this->assertStringContainsString('color:#224466;font-size:12pt', $html);
        $this->assertStringContainsString('Cliente de ejemplo', $html);
    }
}
