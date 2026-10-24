<?php
namespace Tests\Feature;
use App\Models\User;
use App\Modules\Activities\Models\Activity;
use App\Modules\MaintenanceProtocols\Actions\ApplyProtocol;
use App\Modules\MaintenanceProtocols\Import\ProtocolSource;
use App\Modules\MaintenanceProtocols\Models\{MaintenanceProtocol, ProtocolStep, ProtocolFile, ProtocolApplication};
use App\Modules\MaintenanceProtocols\UI\{ProtocolPage, ServiceProtocols};
use App\Modules\Services\UI\ServicePage;
use App\Modules\TechnicalReports\Queries\ReportData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\Support\WorkflowFixture;
use Tests\TestCase;
class MaintenanceProtocolTest extends TestCase
{
    use RefreshDatabase, WorkflowFixture;
    protected function setUp(): void { parent::setUp(); $this->bootWorkflow(); Storage::fake('local'); }
    private function protocol(): MaintenanceProtocol
    {
        $p = MaintenanceProtocol::create(['source_id' => '99901', 'code' => 'PR-TEST', 'name' => 'Protocolo de prueba aislada',
            'version' => 'V1', 'is_obsolete' => false, 'source_hash' => str_repeat('a', 64), 'source_payload' => []]);
        foreach ([true, false] as $index => $include) {
            $p->steps()->create(['source_id' => '9990'.($index + 2), 'parent_source_id' => $p->source_id,
                'position' => $index + 1, 'step_number' => $index + 1, 'instruction' => 'Instrucción original '.$index,
                'report_text' => 'Texto del informe '.$index, 'range' => '10–20 V', 'type' => 'MEDICION',
                'include_in_report' => $include, 'source_hash' => str_repeat('b', 64), 'source_payload' => []]);
        }
        return $p;
    }
    public function test_received_dataset_is_complete_idempotent_and_queryable(): void
    {
        $this->artisan('data:load-protocols', ['--dry-run' => true])->assertSuccessful();
        $this->assertSame(0, MaintenanceProtocol::count()); $this->artisan('data:load-protocols')->assertSuccessful();
        $this->assertSame(55, MaintenanceProtocol::count()); $this->assertSame(675, ProtocolStep::count());
        $this->assertSame(23, ProtocolStep::whereNull('maintenance_protocol_id')->count());
        $this->assertSame(652, ProtocolStep::whereNotNull('maintenance_protocol_id')->count());
        $this->assertSame(189, ProtocolFile::count()); $this->assertSame(1, ProtocolFile::where('mime', 'application/pdf')->count());
        $this->artisan('data:load-protocols')->assertSuccessful();
        $this->assertSame(55, MaintenanceProtocol::count()); $this->assertSame(675, ProtocolStep::count()); $this->assertSame(189, ProtocolFile::count());
        $p = MaintenanceProtocol::where('source_id', '100')->firstOrFail();
        $this->assertSame(['1602', '1603', '1604', '1605', '1606', '1607'], $p->steps->pluck('source_id')->all());
        $this->get('/operations/maintenance-protocols?record='.$p->id)->assertOk()->assertSee('Pasos e instrucciones')->assertSee('SMARTMAN-PREVENTIVO');
        Livewire::test(ProtocolPage::class)->set('showPending', true)->assertSee('Pasos pendientes de relación')->assertSee('113')->assertSee('114');
        $this->get(route('operations.protocol-file', ProtocolFile::first()))->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertSame(0, ProtocolApplication::count());
        $f = $this->workflow();
        $this->assertSame(6, app(ApplyProtocol::class)->execute($f['service']->id, $p->id)->activities()->count());
    }
    public function test_apply_is_idempotent_preserves_snapshot_and_excludes_internal_steps_from_report(): void
    {
        $f = $this->workflow(); $p = $this->protocol();
        $application = app(ApplyProtocol::class)->execute($f['service']->id, $p->id);
        $again = app(ApplyProtocol::class)->execute($f['service']->id, $p->id);
        $this->assertSame($application->id, $again->id); $this->assertSame(1, ProtocolApplication::count());
        $this->assertSame(3, $f['service']->activities()->count()); $activities = $application->activities()->orderBy('id')->get();
        $this->assertNull($activities[0]->result); $this->assertSame([], $activities[0]->photos);
        $this->assertSame('10–20 V', $activities[0]->protocol_snapshot['range']);
        $p->steps()->first()->update(['instruction' => 'Cambio posterior']); $p->update(['version' => 'V2']);
        $this->assertSame('V1', $application->fresh()->snapshot['version']);
        $this->assertSame('Instrucción original 0', $activities[0]->fresh()->protocol_snapshot['instruction']);
        $report = app(ReportData::class)->execute($f['service'], 'TEST', 'Prueba'); $this->assertCount(2, $report['activities']);
        $this->assertNotContains('Texto del informe 1', array_column($report['activities'], 'description'));
        $this->assertSame('in_progress', $f['service']->fresh()->status);
    }
    public function test_service_tab_loads_component_and_application_links_to_activities(): void
    {
        $f = $this->workflow(); $p = $this->protocol();
        Livewire::test(ServicePage::class)->call('edit', $f['service']->id)->call('loadEditorRelation', 'maintenance_protocols')
            ->assertSee('Protocolos del servicio')->assertHasNoErrors();
        Livewire::test(ServiceProtocols::class, ['serviceId' => $f['service']->id])->set('protocolId', (string)$p->id)
            ->call('apply')->assertHasNoErrors()->assertSee('2 actividades vinculadas')->assertSee('Resultado pendiente');
        $a = Activity::whereNotNull('protocol_application_id')->firstOrFail();
        $this->get('/operations/activities?record='.$a->id)->assertOk()->assertSee('Instrucción original 0')->assertSee('10–20 V');
    }
    public function test_missing_instructions_closed_service_and_unverified_protocol_are_rejected_without_partial_activities(): void
    {
        $f = $this->workflow(); $p = $this->protocol(); $p->steps()->first()->update(['instruction' => null, 'report_text' => null]);
        $this->assertThrows(fn () => app(ApplyProtocol::class)->execute($f['service']->id, $p->id), ValidationException::class);
        $this->assertSame(0, ProtocolApplication::count()); $this->assertSame(1, $f['service']->activities()->count());
        $p->steps()->first()->update(['instruction' => 'Restaurado']);
        foreach ([true, null] as $obsolete) {
            $p->update(['is_obsolete' => $obsolete]);
            $this->assertThrows(fn () => app(ApplyProtocol::class)->execute($f['service']->id, $p->id), ValidationException::class);
        }
        $p->update(['is_obsolete' => false]); $f['service']->update(['status' => 'completed']);
        $this->assertThrows(fn () => app(ApplyProtocol::class)->execute($f['service']->id, $p->id), ValidationException::class);
    }
    public function test_technician_scope_and_apply_permission_are_enforced(): void
    {
        $f = $this->workflow(); $p = $this->protocol(); $tech = User::factory()->create(); $tech->assignRole('Técnico');
        $own = $this->workflow($tech); $this->actingAs($tech);
        $this->assertThrows(fn () => Livewire::test(ServiceProtocols::class, ['serviceId' => $f['service']->id]), \Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $tech->removeRole('Técnico'); $tech->givePermissionTo(['workflow.access', 'services.view', 'services.manage', 'activities.view', 'activities.manage', 'maintenance_protocols.view']);
        Livewire::test(ServiceProtocols::class, ['serviceId' => $own['service']->id])->set('protocolId', (string)$p->id)->call('apply')->assertForbidden();
        $this->assertSame(0, ProtocolApplication::count());
    }
    public function test_protocol_activity_cannot_be_reassigned_to_another_service(): void
    {
        $f = $this->workflow(); $other = $this->workflow(); $p = $this->protocol();
        $a = app(ApplyProtocol::class)->execute($f['service']->id, $p->id)->activities()->first();
        $this->assertThrows(fn () => $this->saveWorkflow('activities', ['service_id' => $other['service']->id,
            'description' => $a->description, 'result' => '', 'lock_version' => $a->lock_version], $a->id), ValidationException::class);
        $this->assertSame($f['service']->id, $a->fresh()->service_id);
    }
    public function test_private_files_and_catalog_require_permissions(): void
    {
        $this->get('/operations/maintenance-protocols')->assertOk();
        $user = User::factory()->create(); $user->givePermissionTo('workflow.access'); $this->actingAs($user);
        $this->get('/operations/maintenance-protocols')->assertForbidden();
        $p = $this->protocol(); $step = $p->steps()->first();
        $file = $step->files()->create(['sha256' => str_repeat('a', 64), 'name' => 'Referencia.png', 'path' => 'maintenance-protocols/images/'.str_repeat('a', 64).'.png', 'mime' => 'image/png']);
        $this->get(route('operations.protocol-file', $file))->assertForbidden();
    }
    public function test_source_rejects_path_traversal_before_import(): void
    {
        $directory = Storage::disk('local')->path('source'); mkdir($directory, 0777, true);
        foreach (['protocols.jsonl', 'steps.jsonl'] as $file) { copy(base_path('database/imports/maintenance-protocols/'.$file), $directory.'/'.$file); }
        $files = json_decode(file_get_contents(base_path('database/imports/maintenance-protocols/images.json')), true);
        $files[0]['path'] = '../outside.png'; file_put_contents($directory.'/images.json', json_encode($files));
        $this->assertThrows(fn () => app(ProtocolSource::class)->read($directory), ValidationException::class);
        $this->assertSame(0, MaintenanceProtocol::count());
    }
    public function test_historical_service_is_not_modified_and_long_instructions_are_retained_in_full(): void
    {
        $f = $this->workflow(); $p = $this->protocol();
        $f['service']->ninoxImport()->create(['ninox_table' => 'K', 'ninox_id' => '999991', 'source_hash' => str_repeat('c', 64), 'payload' => []]);
        $this->assertThrows(fn () => app(ApplyProtocol::class)->execute($f['service']->id, $p->id), ValidationException::class);
        $this->assertSame(0, ProtocolApplication::count());
        $other = $this->workflow(); $text = str_repeat('Instrucciones completas ', 400);
        $p->steps()->first()->update(['instruction' => $text, 'report_text' => null]);
        $activity = app(ApplyProtocol::class)->execute($other['service']->id, $p->id)->activities()->first();
        $this->assertSame($text, $activity->protocol_snapshot['instruction']);
        $this->assertLessThanOrEqual(5000, mb_strlen($activity->description));
        $this->get('/operations/activities?record='.$activity->id)->assertOk()->assertSee($text);
    }

}
