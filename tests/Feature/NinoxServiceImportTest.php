<?php
namespace Tests\Feature;
use App\Models\User;
use App\Modules\Services\Import\{SourceRecords, ApprovedMappings, ImportServiceLogs};
use App\Modules\Services\Models\{Service, NinoxServiceImport, ServiceDocument};
use App\Modules\Services\UI\ServicePage;
use App\Modules\Workflow\Actions\SaveRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB, Storage};
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\Support\WorkflowFixture;
use Tests\TestCase;
class NinoxServiceImportTest extends TestCase
{
    use RefreshDatabase, WorkflowFixture;
    protected function setUp(): void { parent::setUp(); $this->bootWorkflow(); Storage::fake('local'); }
    private function source(): array { return app(SourceRecords::class)->read(base_path('database/imports/ninox-service-logs/export/records.jsonl')); }
    private function runImport(array $records, array $map = [], array $roots = []): array
    {
        return app(ImportServiceLogs::class)->execute($records, ['tickets' => [], 'technicians' => [], ...$map], $roots);
    }
    public function test_all_real_records_import_idempotently_without_fake_relations(): void
    {
        $users = User::count(); $records = $this->source();
        $roots = [base_path('database/imports/ninox-service-logs')];
        $first = $this->runImport($records, [], $roots);
        $this->assertSame(369, $first['created']); $this->assertSame(332, $first['ready']); $this->assertSame(34, $first['missing']);
        $this->assertSame(0, $first['invalid']); $this->assertSame($users, User::count());
        $this->assertSame(369, Service::whereNull('ticket_id')->whereNull('technician_id')->count());
        $before = NinoxServiceImport::firstOrFail()->getRawOriginal('payload');
        $this->assertStringNotContainsString('report_code', $before);
        $second = $this->runImport($records, [], $roots);
        $this->assertSame(369, $second['existing']); $this->assertSame(0, $second['created']);
        $this->assertSame(369, Service::count()); $this->assertSame(366, ServiceDocument::count());
        $this->assertSame($before, NinoxServiceImport::firstOrFail()->getRawOriginal('payload'));
    }
    public function test_approved_mappings_resolve_links_without_matching_local_numeric_ids(): void
    {
        $f = $this->workflow(); $record = $this->source()[0];
        $record['tickets_associated'] = $f['ticket']->id;
        $this->runImport([$record]); $import = NinoxServiceImport::firstOrFail();
        $this->assertNull($import->service->ticket_id);
        $map = ['tickets' => [(string)$record['tickets_associated'] => $f['ticket']->id], 'technicians' => [$record['engineer_assigned'] => $this->admin->id]];
        $this->runImport([$record], $map);
        $service = $import->service->fresh(); $this->assertSame($f['ticket']->id, $service->ticket_id);
        $this->assertSame($this->admin->id, $service->technician_id);
        $other = $this->workflow(); $map['tickets'][(string)$record['tickets_associated']] = $other['ticket']->id;
        $this->assertSame(1, $this->runImport([$record], $map)['conflicts']);
        $this->assertSame($f['ticket']->id, $service->fresh()->ticket_id);
    }
    public function test_changed_source_is_reported_without_overwriting_history(): void
    {
        $record = $this->source()[0]; $this->runImport([$record]);
        $record['visit_reason'] = 'Cambio posterior';
        $this->assertSame(1, $this->runImport([$record])['conflicts']);
        $this->assertNotSame('Cambio posterior', Service::firstOrFail()->summary);
    }
    public function test_original_pdf_can_be_added_later_and_is_private(): void
    {
        $record = $this->source()[0]; $record['technician_signature'] = [];
        $bytes = "%PDF-1.4\n% test fixture\n%%EOF";
        $name = 'original.pdf'; $record['report_pdf'] = [['name' => $name, 'path' => 'files/report_pdf/'.$record['ninox_id'].'/'.$name, 'sha256' => hash('sha256', $bytes)]];
        $this->runImport([$record]); $document = ServiceDocument::firstOrFail();
        $url = route('services.document', [$document->service_id, $document->id]);
        $this->get($url)->assertNotFound();
        $root = storage_path('framework/testing/ninox-'.uniqid()); $file = $root.'/'.$record['report_pdf'][0]['path'];
        mkdir(dirname($file), 0777, true); file_put_contents($file, $bytes);
        try {
            $this->assertSame(1, $this->runImport([$record], [], [$root])['ready']);
            $this->get($url)->assertOk()->assertHeader('Content-Type', 'application/pdf');
            $tech = User::factory()->create(); $tech->assignRole('Técnico'); $this->actingAs($tech);
            $this->get($url)->assertNotFound(); $this->assertGuestCannotOpen($url);
        } finally { unlink($file); }
    }
    private function assertGuestCannotOpen(string $url): void { auth()->logout(); $this->get($url)->assertRedirect(route('login')); }
    public function test_hash_mismatch_is_rejected_and_path_traversal_fails_validation(): void
    {
        $record = $this->source()[0]; $record['report_pdf'] = [];
        $record['technician_signature'][0]['sha256'] = str_repeat('0', 64);
        $this->assertSame(1, $this->runImport([$record], [], [base_path('database/imports/ninox-service-logs')])['invalid']);
        $record['technician_signature'][0]['path'] = '../outside.png';
        $temp = tempnam(sys_get_temp_dir(), 'ninox'); file_put_contents($temp, json_encode($record));
        try { $this->expectException(ValidationException::class); app(SourceRecords::class)->read($temp); }
        finally { unlink($temp); }
    }
    public function test_native_editor_displays_history_but_does_not_modify_it(): void
    {
        $record = $this->source()[0]; $this->runImport([$record]); $service = Service::firstOrFail();
        $page = Livewire::test(ServicePage::class)->call('edit', $service->id)->assertSee('Detalles')
            ->assertSee($record['report_code'])->assertSee('Pendiente de equivalencia')->assertSee('Bitácora histórica importada');
        $page->set('summary', 'No permitido')->call('autosaveRecord', $service->id, $page->get('editorSession'))->assertReturned('invalid');
        $this->assertNotSame('No permitido', $service->fresh()->summary);
        $this->get('/operations/services?record='.$service->id)->assertOk();
    }
    public function test_dry_run_does_not_create_services_or_documents(): void
    {
        $this->artisan('ninox:import-service-logs', ['--dry-run' => true])->assertSuccessful();
        $this->assertSame(0, Service::count()); $this->assertSame(0, ServiceDocument::count());
    }
    public function test_validated_map_requires_real_targets_and_source_duplicates_fail_before_writing(): void
    {
        $temp = tempnam(sys_get_temp_dir(), 'ninox-map');
        try {
            file_put_contents($temp, json_encode(['tickets' => ['2071' => 99999999]]));
            try { app(ApprovedMappings::class)->read($temp); $this->fail('Invalid target accepted'); } catch (\RuntimeException) {}
            file_put_contents($temp, json_encode(['tickets' => ['2071' => $this->workflow()['ticket']->id]]));
            $this->assertArrayHasKey('2071', app(ApprovedMappings::class)->read($temp)['tickets']);
            $record = $this->source()[0]; file_put_contents($temp, json_encode($record)."\n".json_encode($record));
            try { app(SourceRecords::class)->read($temp); $this->fail('Duplicate identity accepted'); } catch (\RuntimeException) {}
            $this->assertSame(0, NinoxServiceImport::count());
        } finally { unlink($temp); }
    }
    public function test_forged_normal_values_cannot_edit_imported_service_or_regenerate_its_report(): void
    {
        $record = $this->source()[0]; $this->runImport([$record]); $service = Service::firstOrFail(); $f = $this->workflow();
        try {
            $this->saveWorkflow('services', ['ticket_id' => $f['ticket']->id, 'service_date' => '2026-10-18',
                'status' => 'draft', 'service_type' => 'Changed'], $service->id);
            $this->fail('Historical service changed');
        } catch (ValidationException $e) { $this->assertArrayHasKey('service_id', $e->errors()); }
        $this->seed(\Database\Seeders\ReportTemplatesSeeder::class);
        $this->expectException(ValidationException::class);
        $this->saveWorkflow('technical_reports', ['service_id' => $service->id, 'title' => 'Incomplete report',
            'report_template_id' => \App\Modules\ReportTemplates\Models\ReportTemplate::firstOrFail()->id]);
    }

}
