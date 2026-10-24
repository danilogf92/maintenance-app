<?php
namespace Tests\Feature;
use App\Models\User;
use App\Modules\MaintenancePlans\Actions\{SavePlan, GenerateVisits, GenerateVisitTicket};
use App\Modules\MaintenancePlans\Models\MaintenancePlan;
use App\Modules\MaintenancePlans\UI\CalendarPage;
use App\Modules\MaintenancePlans\Queries\CalendarQuery;
use App\Modules\Tickets\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Support\WorkflowFixture;
use Tests\TestCase;
class MaintenanceCalendarTest extends TestCase
{
    use RefreshDatabase, WorkflowFixture;
    protected function setUp(): void { parent::setUp(); $this->bootWorkflow(); }
    private function planned(): array
    {
        $f = $this->workflow();
        $plan = app(SavePlan::class)->execute(['name'=>'Trimestral', 'equipment_id'=>$f['equipment']->id,
            'technician_id'=>$this->admin->id, 'service_type'=>'Preventivo', 'first_month'=>'2027-01', 'end_month'=>'2028-12',
            'interval_months'=>3, 'active'=>true, 'lock_version'=>0]);
        app(GenerateVisits::class)->execute($plan->id, 5, 0);
        $visit = $plan->visits()->orderBy('sequence')->first(); $visit->update(['notes'=>'Conservar indicaciones']);
        return [$f,$plan,$visit];
    }
    public function test_annual_matrix_contains_only_year_visits_and_keeps_equipment_without_a_plan(): void
    {
        [$f,$plan] = $this->planned();
        $other = $this->saveWorkflow('equipment',['name'=>'Equipo sin plan','serial'=>'OTRO','client_id'=>$f['client']->id,
            'institution_id'=>$f['institution']->id,'status'=>'operational']);
        $data = app(CalendarQuery::class)->execute(2027,'');
        $this->assertSame(4,$data['totalVisits']); $this->assertCount(2,$data['equipment']);
        $this->assertSame(['2027-01','2027-04','2027-07','2027-10'],$data['plansByEquipment'][$f['equipment']->id][0]->visits->pluck('scheduled_month')->all());
        Livewire::test(CalendarPage::class)->set('year',2027)->assertSee('Equipo sin plan')->assertSee('Enero')->assertSee('Diciembre')
            ->set('search','OTRO')->assertSee('Equipo sin plan')->assertDontSee('Preventivo');
        $this->assertSame(1,app(CalendarQuery::class)->execute(2028,'')['totalVisits']);
    }
    public function test_move_and_edit_preserve_assignment_notes_and_do_not_generate_tickets(): void
    {
        [$f,$plan,$visit] = $this->planned(); $tickets = Ticket::count();
        Livewire::test(CalendarPage::class)->set('year',2027)->call('moveVisit',$visit->id,'2027-02',$visit->lock_version)
            ->assertHasNoErrors()->assertSee('Febrero 2027');
        $this->assertSame('2027-02',$visit->fresh()->scheduled_month);
        $this->assertSame($this->admin->id,$visit->fresh()->technician_id);
        $this->assertSame('Conservar indicaciones',$visit->fresh()->notes);
        Livewire::test(CalendarPage::class)->call('selectVisit',$visit->id)->assertSet('scheduled_month','2027-02')
            ->set('scheduled_month','2027-03')->call('saveVisit')->assertHasNoErrors();
        $this->assertSame('2027-03',$visit->fresh()->scheduled_month); $this->assertSame($tickets,Ticket::count());
    }
    public function test_duplicate_out_of_bounds_stale_cancelled_and_inactive_moves_fail(): void
    {
        [$f,$plan,$visit] = $this->planned();
        foreach (['2027-04','2026-12','2029-01','2027-01-10'] as $month) {
            Livewire::test(CalendarPage::class)->call('moveVisit',$visit->id,$month,$visit->lock_version)->assertHasErrors('scheduled_month')->assertSee('No se pudo actualizar la planificación');
        }
        Livewire::test(CalendarPage::class)->call('moveVisit',$visit->id,'2027-02',0)->assertHasErrors('planning');
        $visit->update(['status'=>'cancelled']);
        Livewire::test(CalendarPage::class)->call('moveVisit',$visit->id,'2027-02',$visit->lock_version)->assertHasErrors('planning');
        $visit->update(['status'=>'planned']); $plan->update(['active'=>false]);
        Livewire::test(CalendarPage::class)->call('moveVisit',$visit->id,'2027-02',$visit->lock_version)->assertHasErrors('planning');
        $this->assertSame('2027-01',$visit->fresh()->scheduled_month);
    }
    public function test_ticket_history_is_locked_and_status_uses_actual_ticket(): void
    {
        [$f,$plan,$visit] = $this->planned();
        $ticket = app(GenerateVisitTicket::class)->execute($plan->id,$visit->id,$visit->lock_version);
        Livewire::test(CalendarPage::class)->call('selectVisit',$visit->id)->assertSet('editVisitId',null)->assertSee($ticket->code)
            ->call('moveVisit',$visit->id,'2027-02',$visit->fresh()->lock_version)->assertHasErrors('planning');
        $ticket->update(['status'=>'resolved']);
        Livewire::test(CalendarPage::class)->set('year',2027)->assertSee('Ticket resuelto');
    }
    public function test_reader_scope_direct_mutations_and_hidden_ticket_are_protected(): void
    {
        [$f,$plan,$visit] = $this->planned();
        app(GenerateVisitTicket::class)->execute($plan->id,$visit->id,$visit->lock_version);
        $user = User::factory()->create(['is_active'=>true]); $user->assignRole('Técnico'); $this->actingAs($user);
        Livewire::test(CalendarPage::class)->set('year',2027)->assertDontSee('Monitor técnico');
        try { Livewire::test(CalendarPage::class)->call('selectVisit',$visit->id); $this->fail('Foreign plan was exposed'); }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {}
        Livewire::test(CalendarPage::class)->call('moveVisit',$visit->id,'2027-02',$visit->fresh()->lock_version)->assertForbidden();
        $plan->update(['technician_id'=>$user->id]);
        $this->assertCount(0,app(CalendarQuery::class)->execute(2027,'')['tickets']);
        Livewire::test(CalendarPage::class)->set('year',2027)->assertSee('Monitor técnico')->call('selectVisit',$visit->id)
            ->assertSet('editVisitId',null)->assertSee('Esta visita ya tiene un ticket asociado.');
    }
    public function test_empty_calendar_does_not_create_business_data_and_year_navigation_is_bounded(): void
    {
        Livewire::test(CalendarPage::class)->assertSee('No hay equipos para mostrar')->set('year',2027)->call('changeYear',1)
            ->assertSet('year',2028)->set('year',2200)->call('changeYear',1)->assertSet('year',2200)->call('currentYear')->assertSet('year',(int)now()->year);
        $this->assertSame(0,MaintenancePlan::count()); $this->assertSame(0,Ticket::count());
        $this->get(route('operations.maintenance_calendar'))->assertOk();
        $this->get(route('operations.maintenance_plans',['new'=>1]))->assertOk()->assertSee('Nombre del plan');
        auth()->logout(); $this->get(route('operations.maintenance_calendar'))->assertRedirect(route('login'));
    }
}
