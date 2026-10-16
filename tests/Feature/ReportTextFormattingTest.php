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
use App\Support\Forms\HexColor;
use Database\Seeders\DatabaseSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class ReportTextFormattingTest extends TestCase
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

    public function test_column_headers_override_the_table_format(): void
    {
        $block = Blocks::make('data_table');
        $block['table_header_style'] = ['background' => '#112233', 'color' => '#ffffff', 'size' => 14, 'font' => 'serif'];
        $block['data_sections'][0]['title_style'] = ['background' => '#445566', 'alignment' => 'center', 'bold' => false];
        $html = $this->render($block);
        $this->assertStringContainsString('background:#445566;color:#ffffff;font-size:14pt;font-family:DejaVu Serif;text-align:center;font-weight:normal', $html);
        $this->assertStringContainsString('background:#112233;color:#ffffff;font-size:14pt;font-family:DejaVu Serif', $html);
    }

    public function test_row_format_overrides_the_column_and_blank_settings_inherit(): void
    {
        $block = Blocks::make('data_table');
        $block['data_sections'][0]['value_style'] = ['color' => '#112233', 'size' => 10.5, 'font' => 'mono', 'bold' => true];
        $block['data_sections'][0]['fields'][0]['value_style'] = ['color' => '#abcdef', 'size' => null, 'font' => null, 'bold' => false];
        $html = $this->render($block);
        $this->assertStringContainsString('color:#abcdef;font-size:10.5pt;font-family:DejaVu Sans Mono;font-weight:normal', $html);
        $this->assertStringContainsString('color:#112233;font-size:10.5pt;font-family:DejaVu Sans Mono;font-weight:bold', $html);
    }

    public function test_activity_headers_format_without_changing_body_cells_or_widths(): void
    {
        $block = Blocks::make('activities');
        $block['table_header_style'] = ['background' => '#00858B', 'alignment' => 'center', 'size' => 12];
        $html = $this->render($block);
        $this->assertSame(4, substr_count($html, 'background:#00858B;text-align:center;font-size:12pt'));
        $this->assertStringContainsString('<col style="width:8mm">', $html);
        $this->assertStringContainsString('style="width:50%"', $html);
        $this->assertStringContainsString('<td>1</td>', $html);
    }

    public function test_editor_persists_five_and_seven_independently_formatted_data_rows(): void
    {
        $template = ReportTemplate::first(); $block = Blocks::make('data_table');
        foreach ([5, 7] as $column => $count) {
            $block['data_sections'][$column]['fields'] = array_fill(0, $count, DataTable::field('Cliente:', 'client.name'));
        }
        $block['data_sections'][0]['title_style'] = ['font' => 'serif', 'alignment' => 'center', 'background' => '#00858B'];
        $block['data_sections'][1]['fields'][6] = ['label' => 'Observación:', 'mode' => 'manual', 'text' => 'Texto propio', 'item_alignment' => 'inherit', 'item_indent' => 0,
            'label_style' => ['color' => '#00858B'], 'value_style' => ['size' => 10.5, 'color' => '#112233']];
        Livewire::test(EditReportTemplate::class, ['record' => $template->id])
            ->set('newBlock', 'data_table')->call('addBlock')->set('blockData', $block)
            ->set('definition.page.font_size', 10.5)->call('save')->assertHasNoErrors();
        $definition = $template->fresh()->definition;
        $columns = $definition['blocks'][8]['data_sections'];
        $this->assertCount(5, $columns[0]['fields']); $this->assertCount(7, $columns[1]['fields']);
        $last = array_values($columns[1]['fields'])[6];
        $this->assertEquals(10.5, $last['value_style']['size']);
        $this->assertSame('Texto propio', $last['text']); $this->assertEquals(10.5, $definition['page']['font_size']);
    }

    public function test_large_word_sizes_and_fractional_sizes_use_points_in_the_pdf_html(): void
    {
        $definition = Blocks::defaults(); $definition['page']['font_size'] = 10.5;
        $definition['blocks'][0]['header_columns']['center']['lines'][0]['size'] = 36;
        $definition['blocks'][0]['header_columns']['center']['lines'][0]['font'] = 'serif';
        $definition['blocks'][0]['header_columns']['center']['lines'][0]['italic'] = true;
        $html = app(TemplateRenderer::class)->execute($definition, app(TemplateSampleData::class)->execute());
        $this->assertStringContainsString('font-size: 10.5pt', $html);
        $this->assertStringContainsString('font-size:36pt', $html);
        $this->assertStringContainsString('font-family:DejaVu Serif;font-style:italic', $html);
    }

    public function test_non_hex_colors_and_unapproved_fonts_cannot_enter_saved_styles(): void
    {
        $definition = Blocks::defaults(); $definition['blocks'][0]['table_header_style'] = ['font' => 'url(file:///etc/passwd)', 'color' => 'rgb(0,0,0)'];
        try { Rules::validate($definition); $this->fail('Unsafe styles accepted'); }
        catch (ValidationException $error) {
            $this->assertArrayHasKey('blocks.0.table_header_style.font', $error->errors());
            $this->assertArrayHasKey('blocks.0.table_header_style.color', $error->errors());
        }
        $this->assertSame('hex', HexColor::make('color')->getFormat());
    }
}
