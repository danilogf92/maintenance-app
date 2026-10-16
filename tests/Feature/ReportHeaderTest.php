<?php

namespace Tests\Feature;

use App\Models\BrandSetting;
use App\Models\User;
use App\Modules\ReportTemplates\Configuration\Blocks;
use App\Modules\ReportTemplates\Configuration\Header;
use App\Modules\ReportTemplates\Configuration\Rules;
use App\Modules\ReportTemplates\Models\ReportTemplate;
use App\Modules\ReportTemplates\Queries\TemplateSampleData;
use App\Modules\ReportTemplates\Rendering\TemplateRenderer;
use App\Modules\ReportTemplates\UI\Pages\EditReportTemplate;
use Database\Seeders\DatabaseSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class ReportHeaderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $user = User::factory()->create(['can_access_admin' => true]);
        $user->assignRole('SuperAdmin'); $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_legacy_header_keeps_custom_title_fields_and_uploaded_logo(): void
    {
        $definition = Blocks::defaults();
        unset($definition['blocks'][0]['header_columns']);
        $definition['blocks'][0]['title'] = 'Título anterior';
        $definition['blocks'][0]['fields'] = ['ticket.number'];
        $definition['blocks'][0]['company_logo'] = 'report-assets/company.png';
        $header = Rules::validate($definition)['blocks'][0];
        $this->assertSame('company_custom', $header['header_columns']['left']['logo_source']);
        $this->assertSame('Título anterior', $header['header_columns']['center']['lines'][0]['text']);
        $this->assertSame('ticket.number', $header['header_columns']['center']['lines'][1]['variable']);
    }

    public function test_editor_persists_column_format_and_manual_and_dynamic_lines(): void
    {
        $template = ReportTemplate::first();
        Livewire::test(EditReportTemplate::class, ['record' => $template->id])
            ->set('blockData.header_columns.center.lines', [Header::line('Referencia: ', 'service.type', '#662288', 14)])
            ->set('blockData.header_columns.left.border', true)
            ->set('blockData.header_columns.left.border_color', '#ff0000')
            ->set('blockData.header_repeat', true)->call('save')->assertHasNoErrors();
        $header = $template->fresh()->definition['blocks'][0];
        $line = array_values($header['header_columns']['center']['lines'])[0];
        $this->assertSame('#662288', $line['color']);
        $this->assertSame('service.type', $line['variable']);
        $this->assertTrue($header['header_repeat']);
        $this->assertSame('#ff0000', $header['header_columns']['left']['border_color']);
    }

    public function test_system_company_and_record_factory_logos_are_captured_without_uploading_again(): void
    {
        Storage::fake('public');
        $photo = file_get_contents(public_path('report-assets/demo-equipment.png'));
        Storage::disk('public')->put('branding/company.png', $photo);
        Storage::disk('public')->put('report-assets/factory.png', $photo);
        BrandSetting::updateOrCreate(['id' => 1], ['name' => 'Empresa de BD', 'logo_path' => 'branding/company.png']);
        $data = app(TemplateSampleData::class)->execute();
        $data['equipment']['factory_logo'] = 'report-assets/factory.png';
        $brand = app(TemplateRenderer::class)->captureBranding(Blocks::defaults(), $data);
        $this->assertStringStartsWith('data:image/png;base64,', $brand['images'][0]['company']);
        $this->assertStringStartsWith('data:image/png;base64,', $brand['images'][0]['factory']);
        $this->assertNull($brand['images'][0]['company_custom']);
    }

    public function test_manual_text_is_escaped_and_dynamic_content_renders_with_its_color(): void
    {
        $definition = Blocks::defaults();
        $definition['blocks'][0]['header_columns']['center']['lines'] = [
            Header::line('<script>bad()</script>'), Header::line('Referencia: ', 'service.type', '#662288', 14)];
        $html = app(TemplateRenderer::class)->execute($definition, app(TemplateSampleData::class)->execute());
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringContainsString('Referencia: Mantenimiento preventivo', $html);
        $this->assertStringContainsString('color:#662288;font-size:14pt', $html);
    }

    public function test_header_rejects_css_injection_and_missing_column(): void
    {
        $definition = Blocks::defaults();
        $definition['blocks'][0]['header_columns']['left']['border_color'] = 'red; background:url(file:///etc/passwd)';
        unset($definition['blocks'][0]['header_columns']['right']);
        $this->expectException(ValidationException::class);
        Rules::validate($definition);
    }
}
