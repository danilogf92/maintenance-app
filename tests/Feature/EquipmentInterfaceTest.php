<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Equipment\UI\{EquipmentPage, RelatedTickets};
use App\Modules\Equipment\Models\Equipment;
use App\Modules\Equipment\Queries\{EquipmentQuery, EquipmentOptions};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Support\WorkflowFixture;
use Tests\TestCase;

class EquipmentInterfaceTest extends TestCase
{
    use RefreshDatabase, WorkflowFixture;
    protected function setUp(): void { parent::setUp(); $this->bootWorkflow(); }

    public function test_actual_route_resolves_tabbed_equipment_and_its_deep_link(): void
    {
        $f = $this->workflow(); $registry = app(\Livewire\Mechanisms\ComponentRegistry::class);
        $this->assertSame('equipment-page', $registry->getName(EquipmentPage::class));
        $this->assertSame(EquipmentPage::class, $registry->getClass('equipment-page'));
        $this->get('/operations/equipment?record='.$f['equipment']->id)->assertOk()->assertSee('Información del equipo')->assertSee('Propiedad y ubicación');
        $this->get('/operations/equipment?record=invalid')->assertNotFound();
    }

    public function test_new_draft_requires_location_then_autosaves_once_and_accepts_duplicate_serials(): void
    {
        $f = $this->workflow();
        $page = Livewire::test(EquipmentPage::class)->call('create')->set('name', 'Nuevo monitor')->set('serial', $f['equipment']->serial);
        $session = $page->get('editorSession');
        $page->call('autosaveDraft', $session)->assertReturned('invalid')->assertHasErrors(['client_id', 'institution_id'])->assertSet('editingId', null);
        $page->set('client_id', (string) $f['client']->id)->set('institution_id', (string) $f['institution']->id)
            ->call('autosaveDraft', $session)->assertReturned('saved')->assertHasNoErrors();
        $id = $page->get('editingId');
        $page->call('autosaveDraft', $session)->assertReturned('saved')->set('location', 'UCI')
            ->call('autosaveRecord', $id, $session)->assertReturned('saved');
        $this->assertSame(2, Equipment::where('serial', $f['equipment']->serial)->count());
        $this->assertSame('UCI', Equipment::findOrFail($id)->location);
    }

    public function test_history_freezes_parent_relations_in_ui_and_server_but_allows_area_changes(): void
    {
        $f = $this->workflow(); $other = $this->workflow();
        $page = Livewire::test(EquipmentPage::class)->call('edit', $f['equipment']->id)->assertSet('relationsFrozen', true);
        $session = $page->get('editorSession');
        $page->set('client_id', (string) $other['client']->id)->call('autosaveRecord', $f['equipment']->id, $session)
            ->assertReturned('invalid')->assertHasErrors('client_id');
        $this->assertSame($f['client']->id, $f['equipment']->fresh()->client_id);
        $page->set('client_id', (string) $f['client']->id)->set('location', 'Nueva área')
            ->call('autosaveRecord', $f['equipment']->id, $session)->assertReturned('saved');
        $this->assertSame('Nueva área', $f['equipment']->fresh()->location);
    }

    public function test_technician_is_read_only_and_relation_and_counter_exclude_other_technicians(): void
    {
        $tech = User::factory()->create(); $tech->assignRole('Técnico'); $f = $this->workflow($tech);
        $foreign = $this->saveWorkflow('tickets', ['equipment_id' => $f['equipment']->id, 'technician_id' => $this->admin->id,
            'subject' => 'Asunto ajeno secreto', 'priority' => 'normal', 'status' => 'open', 'opened_at' => now()->toDateTimeString()]);
        $this->actingAs($tech);
        Livewire::test(EquipmentPage::class)->call('create')->assertForbidden();
        $page = Livewire::test(EquipmentPage::class)->call('edit', $f['equipment']->id)->set('serial', 'Intento');
        $page->call('autosaveRecord', $f['equipment']->id, $page->get('editorSession'))->assertForbidden();
        Livewire::test(RelatedTickets::class, ['equipmentId' => $f['equipment']->id])->assertSee($f['ticket']->code)->assertDontSee('Asunto ajeno secreto');
        $row = app(EquipmentQuery::class)->records('', '')->firstWhere('id', $f['equipment']->id);
        $this->assertSame(1, $row->visible_tickets_count);
        $this->get('/operations/tickets?record='.$foreign->id)->assertNotFound();
    }

    public function test_relation_is_lazy_filtered_by_equipment_and_resets_on_record_change(): void
    {
        $own = $this->workflow(); $other = $this->workflow();
        $page = Livewire::test(EquipmentPage::class)->call('edit', $own['equipment']->id)->assertSet('relationLoaded', false)
            ->call('loadEditorRelation', 'tickets')->assertSet('relationLoaded', true);
        Livewire::test(RelatedTickets::class, ['equipmentId' => $own['equipment']->id])->assertSee($own['ticket']->code)->assertDontSee($other['ticket']->code);
        $page->call('edit', $other['equipment']->id)->assertSet('relationLoaded', false);
    }

    public function test_status_filters_and_group_labels_use_equipment_states(): void
    {
        $f = $this->workflow(); $f['equipment']->update(['status' => 'maintenance']);
        $page = Livewire::test(EquipmentPage::class)->call('groupColumn', 'status')->assertSee('En mantenimiento');
        $view = \App\Models\OperationView::where('module', 'equipment')->where('filter_value', 'operational')->firstOrFail();
        $page->call('selectView', $view->id)->assertDontSee($f['equipment']->code);
        $this->assertSame('En mantenimiento', app(\App\Modules\Shared\RecordTable::class)->label('status', 'maintenance', \App\Modules\Equipment\Configuration\Fields::statuses()));
    }

    public function test_selected_options_remain_available_outside_search_results(): void
    {
        $f = $this->workflow();
        $this->assertArrayHasKey($f['client']->id, EquipmentOptions::clients('no-results-xyz', (string) $f['client']->id));
        $this->assertArrayHasKey($f['institution']->id, EquipmentOptions::institutions('no-results-xyz', (string) $f['institution']->id));
    }

    public function test_lost_updates_are_rejected_without_overwriting_the_new_version(): void
    {
        $f = $this->workflow(); $page = Livewire::test(EquipmentPage::class)->call('edit', $f['equipment']->id);
        $this->saveWorkflow('equipment', [...$f['equipment']->only(['name', 'client_id', 'institution_id', 'brand', 'model', 'serial', 'location', 'status', 'lock_version']), 'location' => 'Cambio externo'], $f['equipment']->id);
        $page->set('location', 'Cambio viejo')->call('autosaveRecord', $f['equipment']->id, $page->get('editorSession'))
            ->assertReturned('invalid')->assertHasErrors('lock_version');
        $this->assertSame('Cambio externo', $f['equipment']->fresh()->location);
    }

    public function test_no_ticket_permission_means_no_relation_and_no_counter(): void
    {
        $f = $this->workflow(); $user = User::factory()->create(); $user->givePermissionTo(['workflow.access', 'equipment.view']); $this->actingAs($user);
        $page = Livewire::test(EquipmentPage::class)->call('edit', $f['equipment']->id);
        $this->assertNotContains('tickets', $page->get('visibleColumns'));
        $page->call('loadEditorRelation', 'tickets')->assertForbidden();
        Livewire::test(RelatedTickets::class, ['equipmentId' => $f['equipment']->id])->assertForbidden();
    }
}
