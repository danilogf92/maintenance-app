<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Tickets\Models\Ticket;
use App\Modules\Tickets\UI\{TicketPage, RelatedServices};
use App\Modules\Tickets\Queries\{TicketOptions, TicketQuery};
use App\Modules\Workflow\Configuration\Modules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Support\WorkflowFixture;
use Tests\TestCase;

class TicketInterfaceTest extends TestCase
{
    use RefreshDatabase, WorkflowFixture;
    protected function setUp(): void { parent::setUp(); $this->bootWorkflow(); }

    public function test_route_uses_tabbed_component_and_datetime_input_format(): void
    {
        $f = $this->workflow(); $registry = app(\Livewire\Mechanisms\ComponentRegistry::class);
        $this->assertSame('tickets-page', $registry->getName(TicketPage::class));
        $this->assertSame(TicketPage::class, $registry->getClass('tickets-page'));
        $this->get('/operations/tickets?record='.$f['ticket']->id)->assertOk()->assertSee('Información del ticket')->assertSee('Equipo y responsable');
        $page = Livewire::test(TicketPage::class)->call('edit', $f['ticket']->id);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $page->get('opened_at'));
        $this->get('/operations/tickets?record=invalid')->assertNotFound();
    }

    public function test_create_draft_requires_equipment_then_saves_once_and_edits_repeatedly(): void
    {
        $f = $this->workflow(); $page = Livewire::test(TicketPage::class)->call('create')->set('subject', 'Ticket nuevo');
        $session = $page->get('editorSession');
        $page->call('autosaveDraft', $session)->assertReturned('invalid')->assertHasErrors('equipment_id');
        $page->set('equipment_id', (string) $f['equipment']->id)->call('autosaveDraft', $session)->assertReturned('saved')->assertHasNoErrors();
        $id = $page->get('editingId');
        $page->call('autosaveDraft', $session)->assertReturned('saved')->set('description', 'Hallazgo registrado')
            ->call('autosaveRecord', $id, $session)->assertReturned('saved')->set('priority', 'high')
            ->call('autosaveRecord', $id, $session)->assertReturned('saved');
        $this->assertSame(2, Ticket::count()); $this->assertSame(3, Ticket::findOrFail($id)->lock_version);
        $this->assertSame('Hallazgo registrado', Ticket::findOrFail($id)->description);
    }

    public function test_technician_cannot_view_another_ticket_or_reassign_work_to_another_technician(): void
    {
        $tech = User::factory()->create(); $tech->assignRole('Técnico'); $own = $this->workflow($tech); $other = $this->workflow();
        $new = $this->saveWorkflow('tickets', ['equipment_id' => $own['equipment']->id, 'technician_id' => $tech->id,
            'subject' => 'Sin servicios', 'priority' => 'normal', 'status' => 'open', 'opened_at' => now()->toDateTimeString()]);
        $this->actingAs($tech);
        $this->get('/operations/tickets?record='.$other['ticket']->id)->assertNotFound();
        $this->assertThrows(fn () => Livewire::test(TicketPage::class)->call('edit', $other['ticket']->id), \Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $page = Livewire::test(TicketPage::class)->call('edit', $new->id)->set('technician_id', (string) $this->admin->id);
        $page->call('autosaveRecord', $new->id, $page->get('editorSession'))->assertReturned('invalid')->assertHasErrors('technician_id');
        $this->assertSame($tech->id, $new->fresh()->technician_id);
    }

    public function test_service_history_freezes_assignment_but_allows_general_changes(): void
    {
        $f = $this->workflow(); $other = $this->workflow();
        $page = Livewire::test(TicketPage::class)->call('edit', $f['ticket']->id)->assertSet('relationsFrozen', true);
        $session = $page->get('editorSession');
        $page->set('equipment_id', (string) $other['equipment']->id)->call('autosaveRecord', $f['ticket']->id, $session)
            ->assertReturned('invalid')->assertHasErrors('equipment_id');
        $page->set('equipment_id', (string) $f['equipment']->id)->set('description', 'Actualización permitida')
            ->call('autosaveRecord', $f['ticket']->id, $session)->assertReturned('saved');
        $this->assertSame('Actualización permitida', $f['ticket']->fresh()->description);
    }

    public function test_inactive_historical_technician_is_preserved_but_new_assignment_is_rejected(): void
    {
        $tech = User::factory()->create(); $tech->assignRole('Técnico'); $f = $this->workflow($tech); $tech->update(['is_active' => false]);
        $this->assertArrayHasKey($tech->id, TicketOptions::technicians($f['ticket']->id));
        $page = Livewire::test(TicketPage::class)->call('edit', $f['ticket']->id)->set('description', 'Conservar historial');
        $page->call('autosaveRecord', $f['ticket']->id, $page->get('editorSession'))->assertReturned('saved');
        $new = Livewire::test(TicketPage::class)->call('create')->set('subject', 'Intento inactivo')->set('equipment_id', (string) $f['equipment']->id)
            ->set('technician_id', (string) $tech->id);
        $new->call('autosaveDraft', $new->get('editorSession'))->assertReturned('invalid')->assertHasErrors('technician_id');
    }

    public function test_related_services_are_lazy_and_scope_both_parent_and_technician(): void
    {
        $tech = User::factory()->create(); $tech->assignRole('Técnico'); $f = $this->workflow($tech); $other = $this->workflow();
        $hidden = $f['service']->replicate(); $hidden->code = 'HIDDEN-SERVICE'; $hidden->technician_id = $this->admin->id; $hidden->save();
        $this->actingAs($tech);
        $page = Livewire::test(TicketPage::class)->call('edit', $f['ticket']->id)->assertSet('relationLoaded', false)
            ->call('loadEditorRelation', 'services')->assertSet('relationLoaded', true);
        Livewire::test(RelatedServices::class, ['ticketId' => $f['ticket']->id])->assertSee($f['service']->code)
            ->assertDontSee('HIDDEN-SERVICE')->assertDontSee($other['service']->code);
        $row = app(TicketQuery::class)->records('', '')->firstWhere('id', $f['ticket']->id);
        $this->assertSame(1, $row->visible_services_count);
        $this->get('/operations/services?record='.$hidden->id)->assertNotFound();
    }

    public function test_ceo_is_read_only_and_service_permission_controls_counter_and_tab(): void
    {
        $f = $this->workflow(); $ceo = User::factory()->create(); $ceo->assignRole('CEO'); $this->actingAs($ceo);
        Livewire::test(TicketPage::class)->call('create')->assertForbidden();
        $page = Livewire::test(TicketPage::class)->call('edit', $f['ticket']->id)->set('subject', 'Prohibido');
        $page->call('autosaveRecord', $f['ticket']->id, $page->get('editorSession'))->assertForbidden();
        $user = User::factory()->create(); $user->givePermissionTo(['workflow.access', 'workflow.view_all', 'tickets.view']); $this->actingAs($user);
        $page = Livewire::test(TicketPage::class)->call('edit', $f['ticket']->id);
        $this->assertNotContains('services', $page->get('visibleColumns'));
        $page->call('loadEditorRelation', 'services')->assertForbidden();
    }

    public function test_equipment_shortcut_opens_a_draft_without_inserting_a_ticket(): void
    {
        $f = $this->workflow(); $count = Ticket::count();
        $this->get('/operations/tickets?new_for_equipment='.$f['equipment']->id)->assertOk()->assertSee('Nuevo registro');
        $this->assertSame($count, Ticket::count());
        $this->get('/operations/tickets?new_for_equipment=999999')->assertNotFound();
        $this->get('/operations/tickets?new_for_equipment=-1')->assertNotFound();
    }

    public function test_lost_updates_are_rejected_and_service_creation_refreshes_the_counter(): void
    {
        $f = $this->workflow(); $query = app(TicketQuery::class);
        $this->assertSame(1, $query->records('', '')->first()->visible_services_count);
        $this->saveWorkflow('services', ['ticket_id' => $f['ticket']->id, 'service_date' => today()->toDateString(), 'service_type' => 'Otra visita', 'status' => 'draft']);
        $this->assertSame(2, $query->records('', '')->first()->visible_services_count);
        $page = Livewire::test(TicketPage::class)->call('edit', $f['ticket']->id);
        $this->saveWorkflow('tickets', [...$f['ticket']->only(['equipment_id', 'technician_id', 'subject', 'description', 'priority', 'status', 'opened_at', 'lock_version']), 'subject' => 'Cambio externo'], $f['ticket']->id);
        $page->set('subject', 'Cambio viejo')->call('autosaveRecord', $f['ticket']->id, $page->get('editorSession'))
            ->assertReturned('invalid')->assertHasErrors('lock_version');
        $this->assertSame('Cambio externo', $f['ticket']->fresh()->subject);
    }

    public function test_sidebar_icons_are_distinct_and_tabs_keep_their_accessible_names(): void
    {
        $response = $this->get('/operations/tickets')->assertOk();
        $dom = new \DOMDocument(); @$dom->loadHTML($response->getContent()); $xpath = new \DOMXPath($dom); $icons = [];
        foreach (Modules::all() as $definition) {
            $node = $xpath->query('//a[@title="'.$definition['label'].'"]//*[name()="svg"]')->item(0);
            $this->assertNotNull($node); $this->assertSame($definition['ui_icon'], $node->getAttribute('data-icon'));
            $this->assertSame('true', $node->getAttribute('aria-hidden')); $icons[] = $node->getAttribute('data-icon');
        }
        $this->assertCount(6, array_unique($icons));
        $response->assertSee('record-tab-icon', false)->assertSee('Asignación')->assertSee('Servicios');
    }
}
