<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Clients\Models\Client;
use App\Modules\Clients\UI\{ClientPage, RelatedEquipment};
use App\Modules\Clients\Queries\ClientQuery;
use App\Modules\Workflow\Actions\SaveRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Support\WorkflowFixture;
use Tests\TestCase;

class ClientInterfaceTest extends TestCase
{
    use RefreshDatabase, WorkflowFixture;

    protected function setUp(): void { parent::setUp(); $this->bootWorkflow(); }

    public function test_actual_route_resolves_the_tabbed_client_component(): void
    {
        $registry = app(\Livewire\Mechanisms\ComponentRegistry::class);
        $this->assertSame('clients-page', $registry->getName(ClientPage::class));
        $this->assertSame(ClientPage::class, $registry->getClass('clients-page'));
        $this->get('/operations/clients')->assertOk()->assertSee('Información del cliente')->assertSee('Equipos relacionados');
        $this->get('/operations/clients?record=invalid')->assertNotFound();
    }

    public function test_autosave_creates_once_and_repeated_edits_keep_the_current_version(): void
    {
        $page = Livewire::test(ClientPage::class)->call('create')->set('name', 'Cliente nuevo');
        $session = $page->get('editorSession');
        $page->call('autosaveDraft', $session)->assertReturned('saved')->assertHasNoErrors();
        $id = $page->get('editingId');
        $page->call('autosaveDraft', $session)->assertReturned('saved');
        $page->set('city', 'Quito')->call('autosaveRecord', $id, $session)->assertReturned('saved');
        $page->set('phone', '000-000')->call('autosaveRecord', $id, $session)->assertReturned('saved');
        $this->assertSame(1, Client::count());
        $this->assertSame(3, Client::findOrFail($id)->lock_version);
        $this->assertSame('Quito', Client::findOrFail($id)->city);
    }

    public function test_retried_draft_recovers_version_when_the_first_response_was_lost(): void
    {
        $client = $this->saveWorkflow('clients', ['name' => 'Ya creado', 'active' => true]);
        $page = Livewire::test(ClientPage::class)->call('create')->set('name', 'Ya creado');
        $session = $page->get('editorSession');
        \Illuminate\Support\Facades\DB::table('operation_record_creations')->insert([
            'user_id' => $this->admin->id, 'module' => 'clients', 'token' => $session, 'record_id' => $client->id,
            'created_at' => now(), 'updated_at' => now()]);
        $page->call('autosaveDraft', $session)->assertReturned('saved')->assertSet('lockVersion', 1)
            ->assertSet('code', $client->code)->set('city', 'Quito')
            ->call('autosaveRecord', $client->id, $session)->assertReturned('saved');
        $this->assertSame(1, Client::count());
        $this->assertSame('Quito', $client->fresh()->city);
    }

    public function test_read_only_technician_cannot_create_or_save_even_by_calling_actions(): void
    {
        $f = $this->workflow(); $tech = User::factory()->create(); $tech->assignRole('Técnico'); $this->actingAs($tech);
        $this->get('/operations/clients')->assertOk()->assertDontSee('aria-label="Nuevo cliente"', false);
        Livewire::test(ClientPage::class)->call('create')->assertForbidden();
        $page = Livewire::test(ClientPage::class)->call('edit', $f['client']->id)->set('name', 'Intento');
        $page->call('autosaveRecord', $f['client']->id, $page->get('editorSession'))->assertForbidden();
        $this->assertSame($f['client']->name, $f['client']->fresh()->name);
    }

    public function test_related_equipment_is_lazy_and_never_includes_another_clients_equipment(): void
    {
        $own = $this->workflow(); $other = $this->workflow();
        $other['equipment']->update(['name' => 'Equipo ajeno secreto']);
        $page = Livewire::test(ClientPage::class)->call('edit', $own['client']->id)->assertSet('relationLoaded', false);
        $page->set('phone', '000-001')->call('loadEditorRelation', 'equipment')->assertSet('relationLoaded', true);
        $this->assertSame('000-001', $own['client']->fresh()->phone);
        Livewire::test(RelatedEquipment::class, ['clientId' => $own['client']->id])
            ->assertSee($own['equipment']->code)->assertDontSee('Equipo ajeno secreto');
        $page->call('edit', $other['client']->id)->assertSet('relationLoaded', false);
    }

    public function test_lost_update_is_rejected_and_the_editor_can_discard_its_stale_draft(): void
    {
        $f = $this->workflow();
        $page = Livewire::test(ClientPage::class)->call('edit', $f['client']->id);
        $session = $page->get('editorSession');
        app(SaveRecord::class)->execute('clients', [...$f['client']->only(['name', 'tax_id', 'email', 'phone', 'address', 'city', 'active', 'lock_version']), 'name' => 'Cambio externo'], $f['client']->id);
        $page->set('name', 'Versión vieja')->call('autosaveRecord', $f['client']->id, $session)
            ->assertReturned('invalid')->assertHasErrors('lock_version')->call('cancel')->assertSet('formOpen', false);
        $this->assertSame('Cambio externo', $f['client']->fresh()->name);
    }

    public function test_personal_view_filters_grouping_and_cache_refresh_after_save(): void
    {
        $this->saveWorkflow('clients', ['name' => 'Activo', 'active' => true, 'city' => 'Quito']);
        $inactive = $this->saveWorkflow('clients', ['name' => 'Inactivo', 'active' => false, 'city' => 'Cuenca']);
        $page = Livewire::test(ClientPage::class)->call('groupColumn', 'city')->assertSee('ops-group-toggle', false);
        $activeView = \App\Models\OperationView::where('module', 'clients')->where('filter_value', '1')->firstOrFail();
        $page->call('selectView', $activeView->id)->assertSee('Activo')->assertDontSee('>Inactivo<', false);
        $query = app(ClientQuery::class); $this->assertCount(2, $query->records('', ''));
        $inactive->update(['name' => 'Nombre actualizado']);
        $this->assertTrue($query->records('', '')->contains('name', 'Nombre actualizado'));
        $page->call('resetColumns')->assertSet('visibleColumns', ['name', 'code', 'tax_id', 'status', 'city']);
    }

    public function test_equipment_permission_is_required_for_relation_and_counter(): void
    {
        $f = $this->workflow(); $user = User::factory()->create(); $user->givePermissionTo(['workflow.access', 'clients.view']); $this->actingAs($user);
        $page = Livewire::test(ClientPage::class)->call('edit', $f['client']->id);
        $this->assertNotContains('equipment', $page->get('visibleColumns'));
        $page->call('loadEditorRelation', 'equipment')->assertForbidden();
        Livewire::test(RelatedEquipment::class, ['clientId' => $f['client']->id])->assertForbidden();
    }

    public function test_equipment_deep_link_opens_the_correct_record_and_rejects_unknown_ids(): void
    {
        $f = $this->workflow();
        $this->get('/operations/equipment?record='.$f['equipment']->id)->assertOk()->assertSee($f['equipment']->name);
        $this->get('/operations/equipment?record=999999')->assertNotFound();
        $this->get('/operations/equipment?record=-1')->assertNotFound();
    }
}
