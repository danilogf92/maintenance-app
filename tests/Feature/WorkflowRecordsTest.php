<?php

namespace Tests\Feature;

use App\Modules\Equipment\Models\Equipment;
use App\Modules\Workflow\Actions\SaveRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Support\WorkflowFixture;
use Tests\TestCase;

class WorkflowRecordsTest extends TestCase
{
    use RefreshDatabase, WorkflowFixture;

    protected function setUp(): void { parent::setUp(); $this->bootWorkflow(); }

    public function test_operational_chain_links_real_entities_and_allows_repeated_serials(): void
    {
        $f = $this->workflow();
        $this->assertSame($f['client']->id, $f['equipment']->client_id);
        $this->assertSame($f['equipment']->id, $f['ticket']->equipment_id);
        $this->assertSame($f['ticket']->id, $f['service']->ticket_id);
        $this->assertSame($this->admin->id, $f['service']->technician_id);
        $this->assertSame($f['service']->id, $f['activity']->service_id);
        $copy = $f['equipment']->only(['client_id', 'institution_id', 'name', 'serial', 'status']);
        $second = $this->saveWorkflow('equipment', $copy);
        $this->assertNotSame($f['equipment']->code, $second->code);
        $this->assertSame(2, Equipment::where('serial', 'SERIE-IGUAL')->count());
    }

    public function test_stale_edits_are_rejected_without_overwriting_current_values(): void
    {
        $client = $this->workflow()['client'];
        $data = ['lock_version' => $client->lock_version, 'name' => 'Nombre nuevo', 'active' => true];
        app(SaveRecord::class)->execute('clients', $data, $client->id);
        try { app(SaveRecord::class)->execute('clients', [...$data, 'name' => 'Edición obsoleta'], $client->id); $this->fail(); }
        catch (ValidationException $error) { $this->assertArrayHasKey('lock_version', $error->errors()); }
        $this->assertSame('Nombre nuevo', $client->fresh()->name);
    }

    public function test_existing_service_prevents_ticket_equipment_reassignment(): void
    {
        $f = $this->workflow(); $second = $this->saveWorkflow('equipment', $f['equipment']->only(['client_id', 'institution_id', 'name', 'serial', 'status']));
        $data = $f['ticket']->only(['lock_version', 'equipment_id', 'technician_id', 'subject', 'priority', 'status', 'opened_at']);
        $this->expectException(ValidationException::class);
        app(SaveRecord::class)->execute('tickets', [...$data, 'equipment_id' => $second->id], $f['ticket']->id);
    }

    public function test_referenced_institutions_cannot_be_deleted(): void
    {
        $f = $this->workflow();
        $this->expectException(ValidationException::class);
        app(\App\Modules\Institutions\Actions\DeleteInstitution::class)->execute($this->admin, $f['institution']->id);
    }
}
