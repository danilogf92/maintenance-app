<?php
namespace Tests\Feature;
use App\Models\User;
use App\Modules\Tickets\Import\{TicketSource,ImportTicket,LinkNinoxServices};
use App\Modules\Tickets\Models\{Ticket,NinoxTicketImport};
use App\Modules\Tickets\UI\TicketPage;
use App\Modules\Services\Models\{Service,NinoxServiceImport};
use App\Modules\Workflow\Actions\SaveRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Artisan,Storage};
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\Support\WorkflowFixture;
use Tests\TestCase;
class NinoxTicketImportTest extends TestCase
{
    use RefreshDatabase,WorkflowFixture;
    protected function setUp(): void { parent::setUp(); $this->bootWorkflow(); Storage::fake('local'); }
    private function source(): array { return app(TicketSource::class)->read(base_path('database/imports/ninox-tickets/export/records.jsonl')); }
    private function import(): void { $this->assertSame(0,Artisan::call('ninox:import-tickets'),Artisan::output()); }
    public function test_all_tickets_preserve_unknown_assignment_original_status_and_full_subject(): void
    {
        $users=User::count();$this->import();$this->assertSame(625,Ticket::count());$this->assertSame(625,NinoxTicketImport::count());
        $this->assertSame(625,Ticket::whereNull('equipment_id')->whereNull('technician_id')->count());$this->assertSame($users,User::count());
        $this->assertSame(502,Ticket::where('ninox_status','Control de Calidad')->count());
        $this->assertSame(625,Ticket::where('origin','legacy')->where('priority','unknown')->where('status','historical')->count());
        $long=collect($this->source())->sortByDesc(fn($r)=>strlen($r['report_client'] ?? ''))->first();
        $this->assertSame($long['report_client'],Ticket::where('code',$long['ticket_code'])->firstOrFail()->subject);
        $this->assertStringNotContainsString('ticket_code',NinoxTicketImport::firstOrFail()->getRawOriginal('payload'));
    }
    public function test_services_link_both_ways_and_repeated_import_preserves_versions(): void
    {
        $this->assertSame(0,Artisan::call('ninox:import-service-logs'));$this->import();
        $this->assertSame(366,Service::whereNotNull('ticket_id')->count());$this->assertSame(3,Service::whereNull('ticket_id')->count());
        $versions=Service::pluck('lock_version','id')->all();$payloads=NinoxServiceImport::pluck('source_hash','id')->all();
        $this->import();$this->assertSame(625,Ticket::count());$this->assertSame($versions,Service::pluck('lock_version','id')->all());
        $this->assertSame($payloads,NinoxServiceImport::pluck('source_hash','id')->all());
        $this->assertSame(0,Artisan::call('ninox:import-service-logs'));$this->assertSame(366,Service::whereNotNull('ticket_id')->count());
        $service=Service::whereNotNull('ticket_id')->firstOrFail();
        $this->get('/operations/services?record='.$service->id)->assertOk()->assertSee('Abrir ticket '.$service->ticket->code);
    }
    public function test_linking_does_not_override_an_existing_verified_ticket(): void
    {
        $this->assertSame(0,Artisan::call('ninox:import-service-logs'));$f=$this->workflow();
        $service=NinoxServiceImport::firstOrFail()->service;$service->update(['ticket_id'=>$f['ticket']->id]);
        $this->assertSame(1,Artisan::call('ninox:import-tickets'));
        $this->assertSame($f['ticket']->id,$service->fresh()->ticket_id);
    }
    public function test_nonreciprocal_reference_is_not_linked(): void
    {
        $this->assertSame(0,Artisan::call('ninox:import-service-logs'));$this->import();
        $service=Service::whereNotNull('ticket_id')->firstOrFail();$import=$service->ticket->ninoxImport;
        $payload=$import->payload;$payload['value_15']=[];$import->update(['payload'=>$payload]);$service->update(['ticket_id'=>null]);
        $this->assertSame(1,app(LinkNinoxServices::class)->execute()['link_conflicts']);$this->assertNull($service->fresh()->ticket_id);
    }
    public function test_historical_ticket_is_visible_to_admin_and_readonly_in_server(): void
    {
        $this->import();$ticket=Ticket::firstOrFail();
        Livewire::test(TicketPage::class)->call('edit',$ticket->id)->assertSee('Detalles')->assertSee('Estado original')
            ->assertSee($ticket->ninox_status)->assertSee('sin hora ni prioridad');
        $f=$this->workflow();
        try {
            $this->saveWorkflow('tickets',['equipment_id'=>$f['equipment']->id,'technician_id'=>$this->admin->id,
                'subject'=>'Changed','priority'=>'normal','status'=>'open','opened_at'=>now()->toDateTimeString()],$ticket->id);
            $this->fail('Historical ticket modified');
        } catch (ValidationException $e) { $this->assertArrayHasKey('ticket_id',$e->errors()); }
        $this->expectException(ValidationException::class);
        $this->saveWorkflow('services',['ticket_id'=>$ticket->id,'service_date'=>'2026-10-18','service_type'=>'Nuevo','status'=>'draft']);
    }
    public function test_unassigned_imports_do_not_leak_to_ordinary_technicians(): void
    {
        $this->import();$ticket=Ticket::firstOrFail();$tech=User::factory()->create();$tech->assignRole('Técnico');$this->actingAs($tech);
        $this->get('/operations/tickets?record='.$ticket->id)->assertNotFound();
        $this->get('/operations/tickets')->assertOk()->assertDontSee($ticket->code);
    }
    public function test_source_changes_and_code_collisions_are_conflicts_and_dry_run_writes_nothing(): void
    {
        $this->artisan('ninox:import-tickets',['--dry-run'=>true])->assertSuccessful();$this->assertSame(0,Ticket::count());
        $row=$this->source()[0];$action=app(ImportTicket::class);$this->assertSame('created',$action->execute($row));
        $this->assertSame('conflicts',$action->execute([...$row,'status'=>'Modified']));
        $this->assertSame('conflicts',$action->execute([...$row,'ninox_id'=>'999999']));$this->assertSame(1,Ticket::count());
    }
}
