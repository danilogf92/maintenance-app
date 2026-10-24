<?php
namespace Tests\Feature;
use App\Models\User;
use App\Modules\MaintenancePlans\Actions\{SavePlan, GenerateVisits, SaveVisit, GenerateVisitTicket};
use App\Modules\MaintenancePlans\Models\MaintenancePlan;
use App\Modules\MaintenancePlans\UI\{PlanPage, PlanVisits};
use App\Modules\MaintenancePlans\Queries\PlanQuery;
use App\Modules\Contacts\Actions\{SaveContact, SaveContactLink};
use App\Modules\Tickets\UI\TicketPage;
use App\Modules\Tickets\Models\Ticket;
use App\Modules\Workflow\Actions\SaveRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\Support\WorkflowFixture;
use Tests\TestCase;
class MaintenancePlanningTest extends TestCase
{
    use RefreshDatabase, WorkflowFixture;
    protected function setUp(): void { parent::setUp(); $this->bootWorkflow(); }
    private function plan(array $f, array $extra = []): MaintenancePlan
    {
        return app(SavePlan::class)->execute(['name' => 'Preventivo trimestral', 'equipment_id' => $f['equipment']->id,
            'technician_id' => '', 'service_type' => 'Preventivo', 'first_month' => '2027-01', 'end_month' => '',
            'interval_months' => 1, 'active' => true, 'lock_version' => 0, ...$extra]);
    }
    private function newTicket(array $f, array $extra = [])
    {
        return app(SaveRecord::class)->execute('tickets', ['equipment_id' => $f['equipment']->id, 'technician_id' => $this->admin->id,
            'subject' => 'Evento reportado', 'description' => '', 'priority' => 'normal', 'status' => 'open', 'opened_at' => now(), 'lock_version' => 0, ...$extra]);
    }
    public function test_plan_generates_months_without_requester_accounts_or_tickets(): void
    {
        $f = $this->workflow(); $users = User::count(); $tickets = Ticket::count(); $plan = $this->plan($f);
        $this->assertSame(3, app(GenerateVisits::class)->execute($plan->id, 3, 0));
        $this->assertSame(['2027-01','2027-02','2027-03'], $plan->visits()->get()->map(fn ($v) => $v->scheduled_month)->all());
        $this->assertSame($tickets, Ticket::count()); $this->assertSame($users, User::count());
        $this->assertNull($plan->visits()->first()->technician_id);
        $this->expectException(ValidationException::class); app(GenerateVisits::class)->execute($plan->id, 3, 0);
    }
    public function test_ticket_generation_requires_assignment_then_is_idempotent_and_linked(): void
    {
        $f = $this->workflow(); $plan = $this->plan($f); app(GenerateVisits::class)->execute($plan->id, 1, 0); $visit = $plan->visits()->first();
        try { app(GenerateVisitTicket::class)->execute($plan->id, $visit->id, 1); $this->fail(); } catch (ValidationException) {}
        $visit = app(SaveVisit::class)->execute($plan->id, $visit->id, ['scheduled_month' => '2027-02', 'technician_id' => $this->admin->id, 'status' => 'planned'], 1);
        $ticket = app(GenerateVisitTicket::class)->execute($plan->id, $visit->id, $visit->lock_version);
        $again = app(GenerateVisitTicket::class)->execute($plan->id, $visit->id, $visit->lock_version);
        $this->assertSame($ticket->id, $again->id); $this->assertSame('scheduled', $ticket->origin);
        $this->assertNull($ticket->requester_contact_id); $this->assertSame($visit->id, $ticket->maintenance_visit_id);
        $this->assertSame($f['equipment']->id, $ticket->equipment_id);
        $this->expectException(ValidationException::class); app(SaveVisit::class)->execute($plan->id, $visit->id, ['scheduled_month' => '2027-03', 'status' => 'cancelled'], $visit->fresh()->lock_version);
    }
    public function test_client_and_commercial_events_allow_no_saved_requester(): void
    {
        $f = $this->workflow();
        foreach (['client','commercial','internal'] as $origin) {
            $ticket = $this->newTicket($f, ['origin' => $origin, 'reported_by' => 'Persona que reporta']);
            $this->assertSame($origin, $ticket->origin); $this->assertNull($ticket->requester_contact_id);
        }
        $this->expectException(ValidationException::class); $this->newTicket($f, ['origin' => 'scheduled']);
    }
    public function test_requester_must_belong_to_equipment_organization_and_history_survives_inactivation(): void
    {
        $f = $this->workflow(); $contact = app(SaveContact::class)->execute(['name' => 'Solicitante', 'active' => true, 'lock_version' => 0]);
        try { $this->newTicket($f, ['requester_contact_id' => $contact->id]); $this->fail(); } catch (ValidationException) {}
        app(SaveContactLink::class)->execute($contact->id, 'client', $f['client']->id, ['category' => 'technical', 'is_primary' => false]);
        $ticket = $this->newTicket($f, ['origin' => 'client', 'requester_contact_id' => $contact->id]);
        app(SaveContact::class)->execute(['name' => $contact->name, 'active' => false, 'lock_version' => $contact->lock_version], $contact->id);
        $updated = app(SaveRecord::class)->execute('tickets', [...$ticket->getAttributes(), 'subject' => 'Actualizado'], $ticket->id);
        $this->assertSame($contact->id, $updated->requester_contact_id);
        Livewire::test(TicketPage::class)->call('edit', $ticket->id)->assertSee('Solicitante (inactivo)');
    }
    public function test_plan_history_and_revision_are_protected(): void
    {
        $f = $this->workflow(); $plan = $this->plan($f); app(GenerateVisits::class)->execute($plan->id, 1, 0);
        try { app(SavePlan::class)->execute([...$plan->getAttributes(), 'first_month' => '2027-02', 'end_month' => ''], $plan->id); $this->fail(); } catch (ValidationException) {}
        $saved = app(SavePlan::class)->execute([...$plan->getAttributes(), 'first_month' => '2027-01', 'end_month' => '', 'name' => 'Plan actualizado'], $plan->id);
        $this->assertSame('Plan actualizado', $saved->name);
        $this->expectException(ValidationException::class);
        app(SavePlan::class)->execute([...$plan->getAttributes(), 'first_month' => '2027-01', 'end_month' => ''], $plan->id);
    }
    public function test_cancelled_visits_and_inactive_plans_cannot_generate_tickets(): void
    {
        $f = $this->workflow(); $plan = $this->plan($f, ['technician_id' => $this->admin->id]); app(GenerateVisits::class)->execute($plan->id, 1, 0);
        $visit = $plan->visits()->first(); app(SaveVisit::class)->execute($plan->id, $visit->id, ['scheduled_month' => '2027-02', 'technician_id' => $this->admin->id, 'status' => 'cancelled'], 1);
        try { app(GenerateVisitTicket::class)->execute($plan->id, $visit->id, 2); $this->fail(); } catch (ValidationException) {}
        $plan->update(['active' => false]); $this->expectException(ValidationException::class); app(GenerateVisits::class)->execute($plan->id, 1, 1);
    }
    public function test_native_plan_autosave_and_visit_panel(): void
    {
        $f = $this->workflow(); $page = Livewire::test(PlanPage::class)->call('create'); $session = $page->get('editorSession');
        $page->set('name', 'Plan nuevo')->call('autosaveDraft', $session)->assertReturned('invalid');
        $page->set('equipment_id', (string)$f['equipment']->id)->call('autosaveDraft', $session)->assertReturned('saved')->call('autosaveDraft', $session);
        $this->assertSame(1, MaintenancePlan::count());
        $page->call('loadEditorRelation', 'maintenance_visits')->assertSee('Visitas programadas');
        Livewire::test(PlanVisits::class, ['planId' => $page->get('editingId')])->set('generationCount', '4')->call('programVisits')->assertSee('4 visitas programadas');
        $this->get(route('operations.maintenance_plans'))->assertOk()->assertSee('Plan nuevo');
    }
    public function test_plan_visibility_is_scoped_and_readers_cannot_write(): void
    {
        $f = $this->workflow(); $plan = $this->plan($f);
        $user = User::factory()->create(['is_active' => true]); $user->assignRole('Técnico'); $this->actingAs($user);
        $this->assertSame(0, PlanQuery::scoped()->count());
        Livewire::test(PlanPage::class)->call('create')->assertForbidden();
        $plan->update(['technician_id' => $user->id]); $this->assertSame(1, PlanQuery::scoped()->count());
        Livewire::test(PlanVisits::class, ['planId' => $plan->id])->call('programVisits')->assertForbidden();
    }
    public function test_end_date_stops_schedule_and_demo_is_safe_to_repeat(): void
    {
        $f = $this->workflow(); $plan = $this->plan($f, ['end_month' => '2027-02']);
        $this->assertSame(2, app(GenerateVisits::class)->execute($plan->id, 12, 0));
        $this->seed(\Database\Seeders\WorkflowDemoSeeder::class); $users = User::count();
        $this->seed(\Database\Seeders\PlanningDemoSeeder::class); $demo = MaintenancePlan::where('code', 'DEMO-PM-1')->firstOrFail(); $demo->update(['name' => 'Editado']);
        $this->seed(\Database\Seeders\PlanningDemoSeeder::class);
        $this->assertSame(4, $demo->visits()->count()); $this->assertSame('Editado', $demo->fresh()->name); $this->assertSame($users, User::count());
    }
    public function test_foreign_visit_cannot_be_modified_and_scheduled_origin_cannot_be_changed(): void
    {
        $f = $this->workflow(); $a = $this->plan($f); $b = $this->plan($f, ['name' => 'Segundo plan', 'technician_id' => $this->admin->id]);
        app(GenerateVisits::class)->execute($b->id, 1, 0); $visit = $b->visits()->first();
        try { app(SaveVisit::class)->execute($a->id, $visit->id, ['scheduled_month' => '2027-02', 'status' => 'planned'], 1); $this->fail(); }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {}
        $ticket = app(GenerateVisitTicket::class)->execute($b->id, $visit->id, 1);
        try { app(SaveRecord::class)->execute('tickets', [...$ticket->getAttributes(), 'origin' => 'client'], $ticket->id); $this->fail(); }
        catch (ValidationException) {}
        $service = $this->saveWorkflow('services', ['ticket_id' => $ticket->id, 'service_date' => '2027-01-31', 'service_type' => 'Preventivo', 'status' => 'draft']);
        $this->assertSame($ticket->technician_id, $service->technician_id); $this->assertSame($visit->id, $ticket->fresh()->maintenance_visit_id);
    }

    public function test_quarterly_plan_counts_months_and_crosses_year_without_daily_due_dates(): void
    {
        $f = $this->workflow(); $plan = $this->plan($f, ['interval_months' => 3]);
        app(GenerateVisits::class)->execute($plan->id, 5, 0);
        $this->assertSame(['2027-01','2027-04','2027-07','2027-10','2028-01'], $plan->visits()->orderBy('sequence')->pluck('scheduled_month')->all());
        $this->travelTo(\Carbon\Carbon::parse('2027-01-31'));
        $this->assertFalse(\App\Modules\MaintenancePlans\Configuration\Periods::overdue('2027-01'));
        $this->assertTrue(\App\Modules\MaintenancePlans\Configuration\Periods::overdue('2026-12'));
        $this->assertSame('Enero 2027', \App\Modules\MaintenancePlans\Configuration\Periods::label('2027-01'));
        $this->travelBack();
    }
    public function test_visit_can_change_month_but_cannot_duplicate_another_month(): void
    {
        $f = $this->workflow(); $plan = $this->plan($f, ['interval_months' => 3]); app(GenerateVisits::class)->execute($plan->id, 2, 0);
        $visit = $plan->visits()->orderBy('sequence')->first();
        $saved = app(SaveVisit::class)->execute($plan->id, $visit->id, ['scheduled_month' => '2027-02', 'status' => 'planned'], 1);
        $this->assertSame('2027-02', $saved->scheduled_month);
        $this->expectException(ValidationException::class);
        app(SaveVisit::class)->execute($plan->id, $visit->id, ['scheduled_month' => '2027-04', 'status' => 'planned'], $saved->lock_version);
    }

}
