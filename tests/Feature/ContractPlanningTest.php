<?php
namespace Tests\Feature;
use App\Models\User;
use App\Modules\Contracts\Actions\{SaveContract,SaveCoverage};
use App\Modules\Contracts\Queries\{Compliance,ContractQuery};
use App\Modules\Contracts\UI\{ContractPage,Coverages};
use App\Modules\MaintenancePlans\Actions\{SavePlan,GenerateVisits,GenerateVisitTicket,SaveVisit};
use App\Modules\MaintenancePlans\Configuration\{CalendarStatus,Schedule};
use App\Modules\MaintenancePlans\Queries\CalendarQuery;
use App\Modules\MaintenancePlans\UI\CalendarPage;
use App\Modules\Workflow\Actions\SaveRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\Support\WorkflowFixture;
use Tests\TestCase;
class ContractPlanningTest extends TestCase
{
    use RefreshDatabase,WorkflowFixture;
    protected function setUp(): void { parent::setUp(); $this->bootWorkflow(); }
    private function context(): array
    {
        $f=$this->workflow();
        $contract=app(SaveContract::class)->execute(['number'=>'CTR-REAL-001','name'=>'Cobertura técnica','client_id'=>$f['client']->id,'active'=>true,'lock_version'=>0]);
        $coverage=app(SaveCoverage::class)->execute($contract->id,['equipment_id'=>$f['equipment']->id,'first_month'=>'2027-01','end_month'=>'2027-12','lock_version'=>0]);
        return [$f,$contract,$coverage];
    }
    private function plan($equipment,$coverage=null,array $extra=[])
    {
        return app(SavePlan::class)->execute(['name'=>'Preventivo','service_type'=>'Preventivo','equipment_id'=>$equipment->id,
            'contract_coverage_id'=>$coverage?->id,'first_month'=>$coverage?->first_month ?? '2027-01','end_month'=>$coverage?->end_month ?? '2027-12',
            'interval_value'=>3,'interval_unit'=>'months','technician_id'=>$this->admin->id,'active'=>true,'lock_version'=>0,...$extra]);
    }
    public function test_each_equipment_has_its_own_coverage_frequency_and_commitment(): void
    {
        [$f,$contract,$coverage]=$this->context();
        $other=$this->saveWorkflow('equipment',['name'=>'Segundo equipo','client_id'=>$f['client']->id,'institution_id'=>$f['institution']->id,'serial'=>'EQ-2','status'=>'operational']);
        $long=app(SaveCoverage::class)->execute($contract->id,['equipment_id'=>$other->id,'first_month'=>'2027-01','end_month'=>'2029-12','requirements'=>'Calibración anual','lock_version'=>0]);
        $quarterly=$this->plan($f['equipment'],$coverage); $annual=$this->plan($other,$long,['interval_value'=>1,'interval_unit'=>'years']);
        app(GenerateVisits::class)->execute($quarterly->id,12,0); app(GenerateVisits::class)->execute($annual->id,12,0);
        $rows=app(Compliance::class)->forContract($contract->id)->keyBy('id');
        $this->assertSame(4,$rows[$coverage->id]->committed); $this->assertSame(3,$rows[$long->id]->committed);
        $this->assertSame(['2027-01','2028-01','2029-01'],$annual->visits()->pluck('scheduled_month')->all());
        $this->assertSame(5,app(CalendarQuery::class)->execute(2027,'','contract',$contract->id)['totalVisits']);
        $this->assertSame(0,app(CalendarQuery::class)->execute(2027,'','equipment')['totalVisits']);
        Livewire::test(ContractPage::class)->call('edit',$contract->id)->call('loadEditorRelation','contract_coverages')->assertSee('Segundo equipo')->assertSee('Calibración anual');
        $this->get(route('operations.maintenance_plans',['new_for_coverage'=>$long->id]))->assertOk()->assertSee('Cobertura contractual');
    }
    public function test_completion_comes_from_service_closure_once_and_reopening_recalculates(): void
    {
        [$f,$contract,$coverage]=$this->context(); $plan=$this->plan($f['equipment'],$coverage); app(GenerateVisits::class)->execute($plan->id,1,0);
        $visit=$plan->visits()->first(); $ticket=app(GenerateVisitTicket::class)->execute($plan->id,$visit->id,$visit->lock_version);
        $ticket->update(['status'=>'resolved']);
        $this->assertSame(0,app(Compliance::class)->forContract($contract->id)->first()->completed);
        $service=$this->saveWorkflow('services',['ticket_id'=>$ticket->id,'service_date'=>'2027-01-15','service_type'=>'Preventivo','status'=>'completed']);
        $second=$this->saveWorkflow('services',['ticket_id'=>$ticket->id,'service_date'=>'2027-01-16','service_type'=>'Preventivo','status'=>'completed']);
        $this->assertSame(1,app(Compliance::class)->forContract($contract->id)->first()->completed);
        $loaded=$visit->fresh()->loadExists('completedServices'); $this->assertSame('completed',CalendarStatus::forVisit($loaded,$ticket)[0]);
        app(SaveRecord::class)->execute('services',[...$service->getAttributes(),'status'=>'in_progress'],$service->id);
        $this->assertSame(1,app(Compliance::class)->forContract($contract->id)->first()->completed);
        app(SaveRecord::class)->execute('services',[...$second->getAttributes(),'status'=>'cancelled'],$second->id);
        $this->assertSame(0,app(Compliance::class)->forContract($contract->id)->first()->completed);
    }
    public function test_days_and_weeks_support_multiple_visits_in_a_month_without_changing_monthly_precision(): void
    {
        [$f]=$this->context();
        $daily=$this->plan($f['equipment'],null,['interval_value'=>10,'interval_unit'=>'days','first_date'=>'2027-01-02','end_date'=>'2027-02-05']);
        app(GenerateVisits::class)->execute($daily->id,8,0);
        $this->assertSame(['2027-01-02','2027-01-12','2027-01-22','2027-02-01'],$daily->visits()->get()->map(fn($v)=>$v->scheduled_for->format('Y-m-d'))->all());
        $weekly=$this->plan($f['equipment'],null,['interval_value'=>2,'interval_unit'=>'weeks','first_date'=>'2027-01-01','end_date'=>'2027-02-05']);
        app(GenerateVisits::class)->execute($weekly->id,8,0);
        $this->assertSame(['2027-01-01','2027-01-15','2027-01-29'],$weekly->visits()->get()->map(fn($v)=>$v->scheduled_for->format('Y-m-d'))->all());
        $this->assertSame(1,app(CalendarQuery::class)->execute(2027,'','equipment',null,['2027-01-12','2027-01-12'])['totalVisits']);
        $this->assertSame(1,app(CalendarQuery::class)->execute(2027,'','equipment',null,['2027-02-01','2027-02-01'])['totalVisits']);
        Livewire::test(CalendarPage::class)->set('viewMode','day')->set('anchor','2027-01-12')->assertSee('12/01/2027')->assertDontSee('22/01/2027');
        $month=$this->plan($f['equipment']); app(GenerateVisits::class)->execute($month->id,1,0);
        Livewire::test(CalendarPage::class)->set('viewMode','week')->set('anchor','2027-01-12')->assertSee('Sin día definido');
        $this->assertSame('month',$month->visits()->first()->schedule_precision);
    }
    public function test_contract_scope_and_direct_write_permission_are_enforced(): void
    {
        [$f,$contract,$coverage]=$this->context(); $user=User::factory()->create(['is_active'=>true]); $user->assignRole('Técnico');
        $this->actingAs($user); $this->assertSame(0,ContractQuery::scoped()->count());
        $this->get(route('operations.contracts'))->assertOk();
        Livewire::test(ContractPage::class)->call('create')->assertForbidden();
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        app(CalendarQuery::class)->execute(2027,'','contract',$contract->id);
    }
    public function test_wrong_equipment_or_out_of_coverage_plan_is_rejected(): void
    {
        [$f,$contract,$coverage]=$this->context();
        try { $this->plan($f['equipment'],$coverage,['end_month'=>'2028-01']); $this->fail(); } catch (ValidationException $e) { $this->assertArrayHasKey('end_month',$e->errors()); }
        $other=$this->saveWorkflow('equipment',['name'=>'Ajeno','client_id'=>$f['client']->id,'institution_id'=>$f['institution']->id,'status'=>'operational']);
        try { $this->plan($other,$coverage); $this->fail(); } catch (ValidationException $e) { $this->assertArrayHasKey('contract_coverage_id',$e->errors()); }
        $plan=$this->plan($f['equipment'],$coverage); app(GenerateVisits::class)->execute($plan->id,1,0);
        $this->expectException(ValidationException::class);
        app(SaveCoverage::class)->execute($contract->id,[...$coverage->getAttributes(),'end_month'=>'2028-12'],$coverage->id);
    }
    public function test_contract_inactivation_stops_generation_without_deleting_history(): void
    {
        [$f,$contract,$coverage]=$this->context(); $plan=$this->plan($f['equipment'],$coverage);
        app(GenerateVisits::class)->execute($plan->id,1,0); $contract->update(['active'=>false]);
        $this->assertSame(1,$plan->visits()->count());
        $this->expectException(ValidationException::class); app(GenerateVisits::class)->execute($plan->id,1,1);
    }
    public function test_explicit_quota_does_not_silently_exceed_commitment(): void
    {
        [$f,$contract,$coverage]=$this->context(); $plan=$this->plan($f['equipment'],$coverage,['expected_visits'=>2]);
        $this->assertSame(2,app(GenerateVisits::class)->execute($plan->id,12,0));
        $this->assertSame(2,app(Compliance::class)->forContract($contract->id)->first()->committed);
        $this->expectException(ValidationException::class); app(GenerateVisits::class)->execute($plan->id,1,1);
    }
    public function test_native_creation_saves_contract_and_individual_coverage_without_generating_visits(): void
    {
        $f=$this->workflow(); $page=Livewire::test(ContractPage::class)->call('create'); $session=$page->get('editorSession');
        $page->set('number','CTR-NATIVE')->call('autosaveDraft',$session)->assertReturned('invalid');
        $page->set('name','Contrato nativo')->call('autosaveDraft',$session)->assertReturned('saved');
        $id=$page->get('editingId');
        Livewire::test(Coverages::class,['contractId'=>$id])->call('create')->set('equipment_id',(string)$f['equipment']->id)
            ->set('first_month','2027-01')->set('end_month','2029-12')->set('requirements','Cobertura propia')
            ->call('save')->assertHasNoErrors()->assertSee('Cobertura propia');
        $this->assertSame(0,\App\Modules\MaintenancePlans\Models\MaintenanceVisit::count());
    }
    public function test_exact_date_edit_drag_and_duplicate_validation_preserve_the_plan(): void
    {
        [$f]=$this->context(); $plan=$this->plan($f['equipment'],null,['interval_unit'=>'days','interval_value'=>1,
            'first_date'=>'2027-01-31','end_date'=>'2027-03-31']);
        app(GenerateVisits::class)->execute($plan->id,2,0); $visit=$plan->visits()->first();
        Livewire::test(CalendarPage::class)->set('year',2027)->call('selectVisit',$visit->id)->assertSet('dailyVisit',true)
            ->set('scheduled_for','2027-02-01')->call('saveVisit')->assertHasErrors('scheduled_for');
        Livewire::test(CalendarPage::class)->call('moveVisit',$visit->id,'2027-02',$visit->lock_version)->assertHasNoErrors();
        $this->assertSame('2027-02-28',$visit->fresh()->scheduled_for->format('Y-m-d'));
        $this->assertSame('2027-01-31',$plan->fresh()->first_date->format('Y-m-d'));
        Livewire::test(CalendarPage::class)->call('selectVisit',$visit->id)->set('scheduled_for','2027-04-01')
            ->call('saveVisit')->assertHasErrors('scheduled_for');
    }

    public function test_monthly_edit_keeps_historical_dates_and_recurrence_uses_months(): void
    {
        [$f]=$this->context(); $plan=$this->plan($f['equipment']);
        $plan->update(['first_date'=>'2027-01-31','end_date'=>'2027-12-01']);
        $saved=app(SavePlan::class)->execute([...$plan->getAttributes(),'name'=>'Conservar fechas'],$plan->id);
        $this->assertSame('2027-01-31',$saved->first_date->format('Y-m-d'));
        $this->assertSame('2027-12-01',$saved->end_date->format('Y-m-d'));
        app(GenerateVisits::class)->execute($plan->id,4,0);
        $this->assertSame(['2027-01','2027-04','2027-07','2027-10'],$plan->visits()->pluck('scheduled_month')->all());
    }

    public function test_missing_client_can_be_completed_without_replacing_a_known_client(): void
    {
        [$f,$contract]=$this->context(); $contract->update(['client_id'=>null]);
        $saved=app(SaveContract::class)->execute([...$contract->getAttributes(),'client_id'=>$f['client']->id],$contract->id);
        $this->assertSame($f['client']->id,$saved->client_id);
        $other=$this->saveWorkflow('clients',['name'=>'Otro cliente','active'=>true]);
        $this->expectException(ValidationException::class);
        app(SaveContract::class)->execute([...$saved->getAttributes(),'client_id'=>$other->id],$contract->id);
    }

}
