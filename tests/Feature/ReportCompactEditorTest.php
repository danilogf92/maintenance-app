<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\ReportTemplates\Configuration\Blocks;
use App\Modules\ReportTemplates\Configuration\DataTable;
use App\Modules\ReportTemplates\Configuration\Rules;
use App\Modules\ReportTemplates\Configuration\Signature;
use App\Modules\ReportTemplates\Models\ReportTemplate;
use Tests\Support\ReportFixture as TemplateSampleData;
use App\Modules\ReportTemplates\Rendering\SignatureRenderer;
use App\Modules\ReportTemplates\Rendering\TemplateRenderer;
use App\Modules\ReportTemplates\UI\Pages\EditReportTemplate;
use Database\Seeders\DatabaseSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class ReportCompactEditorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp(); $this->seed(DatabaseSeeder::class);
        $user = User::factory()->create(['can_access_admin' => true]);
        $user->assignRole('SuperAdmin'); $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    private function editor()
    {
        return Livewire::test(EditReportTemplate::class, ['record' => ReportTemplate::first()->id])
            ->set('newBlock', 'data_table')->call('addBlock');
    }

    public function test_compact_editor_adds_signature_to_the_service_column_and_persists_it(): void
    {
        $editor = $this->editor()->call('selectDataItem', 8, 1)
            ->set('newDataFieldKind', 'signature')->call('addDataItem')
            ->assertSet('dataRowIndex', 4)->assertSee('Formato del elemento seleccionado')
            ->assertSee('Firma individual')->call('save')->assertHasNoErrors();
        $path = $editor->instance()->dataItemPath();
        $editor->set($path.'.signature.style.size', 12)->set($path.'.signature.style.color', '#224466')->call('save')->assertHasNoErrors();
        $saved = ReportTemplate::first()->definition['blocks'][8];
        $sections = array_values($saved['data_sections']);
        $fields = array_values($sections[1]['fields']);
        $this->assertSame('signature', $fields[4]['mode']);
        $this->assertSame('Firma:', $fields[4]['label']);
        $this->assertSame('blank', $fields[4]['signature']['source']);
        $this->assertEquals(12, $fields[4]['signature']['style']['size']);
        $this->assertSame('#224466', $fields[4]['signature']['style']['color']);
        $this->assertCount(4, $sections[0]['fields']);
    }

    public function test_selected_item_paths_follow_repeater_keys_and_order_changes(): void
    {
        $editor = $this->editor()->call('selectDataItem', 8, 0, 0);
        $path = $editor->instance()->dataItemPath();
        $editor->set($path.'.label', 'Cliente seleccionado:')->set($path.'.value_style.color', '#112233')
            ->call('moveDataItem', 1)->assertSet('dataRowIndex', 1)
            ->call('save')->assertHasNoErrors();
        $sections = array_values(ReportTemplate::first()->definition['blocks'][8]['data_sections']);
        $fields = array_values($sections[0]['fields']);
        $this->assertSame('Cliente seleccionado:', $fields[1]['label']);
        $this->assertSame('#112233', $fields[1]['value_style']['color']);
        $editor->call('removeDataItem')->call('save')->assertHasNoErrors();
        $this->assertCount(3, array_values(ReportTemplate::first()->definition['blocks'][8]['data_sections'])[0]['fields']);
    }

    public function test_compact_editor_adds_and_removes_columns_without_losing_existing_data(): void
    {
        $editor = $this->editor()->call('addDataColumn')->assertSet('dataColumnIndex', 2)
            ->call('save')->assertHasNoErrors();
        $sections = array_values(ReportTemplate::first()->definition['blocks'][8]['data_sections']);
        $this->assertCount(3, $sections); $this->assertSame('DATOS DEL CLIENTE', $sections[0]['title']);
        $editor->call('removeDataColumn')->call('save')->assertHasNoErrors();
        $this->assertCount(2, ReportTemplate::first()->definition['blocks'][8]['data_sections']);
    }

    public function test_an_existing_variable_can_be_changed_to_signature_with_valid_defaults(): void
    {
        $editor = $this->editor()->call('selectDataItem', 8, 1, 0);
        $path = $editor->instance()->dataItemPath();
        $editor->set($path.'.mode', 'signature')->call('save')->assertHasNoErrors();
        $field = array_values(array_values(ReportTemplate::first()->definition['blocks'][8]['data_sections'])[1]['fields'])[0];
        $this->assertSame('blank', $field['signature']['source']);
        $this->assertSame(60, $field['signature']['width']);
    }

    public function test_individual_signature_block_and_service_signature_render_separately(): void
    {
        $definition = Blocks::defaults(); $table = Blocks::make('data_table');
        $field = DataTable::signatureField(); $field['signature']['caption'] = 'Recepción del servicio';
        $table['data_sections'][1]['fields'][] = $field;
        $single = Blocks::make('signature'); $single['signature']['caption'] = 'Firma independiente';
        $single['signature']['name_variable'] = ''; $single['signature']['name'] = '<script>Nombre</script>';
        $definition['blocks'] = [$table, $single];
        $html = app(TemplateRenderer::class)->execute($definition, app(TemplateSampleData::class)->execute());
        $this->assertSame(2, substr_count($html, '<span class="individual-signature"'));
        $this->assertStringContainsString('Recepción del servicio', $html);
        $this->assertStringContainsString('Firma independiente', $html);
        $this->assertStringContainsString('&lt;script&gt;Nombre&lt;/script&gt;', $html);
        $this->assertStringNotContainsString('<script>', $html);
    }

    public function test_signature_images_come_only_from_current_report_raster_data(): void
    {
        $png = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aP9sAAAAASUVORK5CYII=';
        $data = ['signatures' => ['technician' => ['image' => $png], 'recipient' => ['image' => 'https://example.com/signature.png']]];
        $this->assertSame($png, SignatureRenderer::image($data, 'technician'));
        $this->assertNull(SignatureRenderer::image($data, 'blank'));
        $this->assertNull(SignatureRenderer::image($data, 'recipient'));
        $this->assertNull(SignatureRenderer::image(['signatures' => ['technician' => ['image' => 'data:image/svg+xml;base64,AAAA']]], 'technician'));
    }

    public function test_signature_sources_sizes_and_colors_are_validated(): void
    {
        $definition = Blocks::defaults(); $definition['blocks'] = [Blocks::make('signature')];
        $definition['blocks'][0]['signature']['source'] = 'file:///etc/passwd';
        $definition['blocks'][0]['signature']['width'] = 999;
        $definition['blocks'][0]['signature']['line_color'] = 'red';
        try { Rules::validate($definition); $this->fail('Invalid signature accepted'); }
        catch (ValidationException $error) {
            $this->assertArrayHasKey('blocks.0.signature.source', $error->errors());
            $this->assertArrayHasKey('blocks.0.signature.width', $error->errors());
            $this->assertArrayHasKey('blocks.0.signature.line_color', $error->errors());
        }
    }

    public function test_data_selection_rejects_invalid_ordinals_and_other_block_types(): void
    {
        $this->editor()->call('selectDataItem', 8, 99)->assertStatus(422);
        $this->editor()->call('selectDataItem', 8, 0, 99)->assertStatus(422);
        $this->editor()->call('selectDataItem', 0, 0, 0)->assertStatus(422);
    }
}
