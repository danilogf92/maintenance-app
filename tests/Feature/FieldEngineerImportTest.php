<?php
namespace Tests\Feature;
use App\Models\User;
use App\Modules\FieldEngineers\Models\FieldEngineer;
use App\Modules\FieldEngineers\Import\{EngineerSource,ImportEngineer,LinkEngineerTickets,LinkEngineerServices};
use App\Modules\FieldEngineers\UI\EngineerPage;
use App\Modules\DataReview\Queries\EngineerReview;
use App\Modules\Tickets\Models\{Ticket,NinoxTicketImport};
use App\Modules\Services\Models\{Service,NinoxServiceImport};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Artisan,DB,Storage};
use Livewire\Livewire;
use Tests\Support\WorkflowFixture;
use Tests\TestCase;
class FieldEngineerImportTest extends TestCase
{
    use RefreshDatabase,WorkflowFixture;
    protected function setUp(): void { parent::setUp();$this->bootWorkflow();Storage::fake('local'); }
    private function loadReceived(): void
    {
        foreach (['data:load-services','data:load-catalogs','data:load-tickets','data:load-equipment','data:load-engineers'] as $command) {
            $this->assertSame(0,Artisan::call($command),Artisan::output());
        }
    }
    public function test_actual_engineers_link_history_without_creating_accounts_or_monthly_visits(): void
    {
        $u=User::factory()->create(['email'=>'danilo.granda@corpo-medica.com']);$u->assignRole('Cliente');$before=User::count();
        $this->loadReceived();$this->assertSame(8,FieldEngineer::count());$this->assertSame($before,User::count());$this->assertTrue($u->fresh()->hasRole('Cliente'));
        $this->assertSame(624,DB::table('ticket_field_engineer')->count());$this->assertSame(624,Ticket::whereHas('fieldEngineers')->count());
        $this->assertSame(366,Service::whereNotNull('field_engineer_id')->count());$this->assertSame(3,Service::whereNull('field_engineer_id')->count());
        $this->assertSame(363,Service::where('field_engineer_basis','ticket_assignment_and_name')->count());
        $this->assertSame(3,Service::where('field_engineer_basis','registered_name_phone')->count());
        $this->assertSame(625,Ticket::whereNull('technician_id')->count());$this->assertSame(369,Service::whereNull('technician_id')->count());
        $this->assertSame(0,DB::table('maintenance_visits')->count());$this->assertSame(0,DB::table('maintenance_plans')->count());
        $this->assertNull(Service::whereHas('ninoxImport',fn($q)=>$q->where('ninox_id','2021'))->firstOrFail()->field_engineer_id);
        $engineer=FieldEngineer::where('source_id','7')->firstOrFail();$this->assertSame('Danilo Granda',$engineer->name);
        $this->assertStringNotContainsString('id_number',$engineer->getRawOriginal('source_payload'));
        $this->assertArrayNotHasKey('source_payload',$engineer->toArray());
    }
    public function test_repeated_complete_load_preserves_person_values_links_versions_and_source_payloads(): void
    {
        $this->loadReceived();$before=[];
        foreach (['field_engineers','ticket_field_engineer','tickets','services','ninox_ticket_imports','ninox_service_imports'] as $table) {
            $before[$table]=DB::table($table)->orderBy('id')->get()->toJson();
        }
        $this->assertSame(0,Artisan::call('data:load-engineers'));
        foreach ($before as $table=>$value) { $this->assertSame($value,DB::table($table)->orderBy('id')->get()->toJson(),$table); }
        $row=app(EngineerSource::class)->read(base_path('database/imports/field-engineers/export/records.jsonl'))[0];
        $this->assertSame('conflicts',app(ImportEngineer::class)->execute([...$row,'name'=>'Changed']));
        $this->assertSame('Christian Larrea',FieldEngineer::where('source_id','1')->firstOrFail()->name);
    }
    public function test_missing_or_ambiguous_assignment_and_existing_service_are_never_reassigned(): void
    {
        $this->loadReceived();$ticket=NinoxTicketImport::whereHas('ticket.fieldEngineers')->firstOrFail();
        $payload=$ticket->payload;$assignment=$payload['field_service_engineer'][0];$old=DB::table('ticket_field_engineer')->where('ticket_id',$ticket->ticket_id)->first();
        $other=FieldEngineer::whereKeyNot($old->field_engineer_id)->firstOrFail();$source=$other->source_payload;$source['visits'][]=$assignment;$other->update(['source_payload'=>$source]);
        $this->assertSame(1,app(LinkEngineerTickets::class)->execute()['ticket_link_conflicts']);
        $this->assertSame($old->field_engineer_id,DB::table('ticket_field_engineer')->where('id',$old->id)->value('field_engineer_id'));
        $service=Service::whereNotNull('field_engineer_id')->firstOrFail();$original=$service->field_engineer_id;
        $wrong=FieldEngineer::whereKeyNot($original)->firstOrFail();$service->update(['field_engineer_id'=>$wrong->id]);
        $this->assertSame(1,app(LinkEngineerServices::class)->execute()['service_link_conflicts']);$this->assertSame($wrong->id,$service->fresh()->field_engineer_id);
    }
    public function test_native_tabs_and_sensitive_fields_are_authorized_in_html_and_livewire(): void
    {
        $this->loadReceived();$engineer=FieldEngineer::where('source_id','7')->firstOrFail();
        Livewire::test(EngineerPage::class)->call('edit',$engineer->id)->assertSee('Contacto')->assertSee('Detalles')->assertSee('Relaciones')->assertSee('1104839483');
        $tech=User::factory()->create();$tech->assignRole('Técnico');$this->actingAs($tech);
        $this->get('/operations/field-engineers?record='.$engineer->id)->assertOk()->assertDontSee('1104839483')->assertDontSee('Costo por hora registrado');
        Livewire::test(EngineerPage::class)->call('edit',$engineer->id)->assertDontSee('1104839483')->assertSee('Sin registros visibles.')->call('create')->assertForbidden();
        $guest=User::factory()->create();$this->actingAs($guest);$this->get('/operations/field-engineers')->assertForbidden();
    }
    public function test_review_and_technician_labels_use_people_without_changing_record_visibility(): void
    {
        $this->loadReceived();$review=app(EngineerReview::class)->execute();
        $this->assertSame(624,$review['ticketsLinked']);$this->assertSame(366,$review['servicesLinked']);$this->assertCount(3,$review['pendingServices']);
        $this->assertSame(589,$review['otherAssignments']);$this->assertSame(2,$review['withoutEmail']);$this->assertSame(1,$review['withoutAssignment']);
        $labels=app(\App\Modules\Tickets\Queries\TicketQuery::class)->records('','');$this->assertSame(624,$labels->filter(fn($t)=>$t->technician_label!=='')->count());
        $services=app(\App\Modules\Services\Queries\ServiceQuery::class)->records('','');$this->assertSame(366,$services->filter(fn($s)=>$s->technician_label!=='')->count());
        $this->get('/operations/data-review')->assertOk()->assertSee('Ingenieros de servicio')->assertSee('589');
        $tech=User::factory()->create();$tech->assignRole('Técnico');$this->actingAs($tech);
        $this->assertCount(0,app(\App\Modules\Tickets\Queries\TicketQuery::class)->records('',''));
        $this->assertCount(0,app(\App\Modules\Services\Queries\ServiceQuery::class)->records('',''));
    }
    public function test_dry_run_and_invalid_source_do_not_create_partial_people(): void
    {
        $this->assertSame(0,Artisan::call('data:load-engineers',['--dry-run'=>true]));$this->assertSame(0,FieldEngineer::count());
        $row=app(EngineerSource::class)->read(base_path('database/imports/field-engineers/export/records.jsonl'))[0];
        $path=storage_path('framework/invalid-engineer.jsonl');file_put_contents($path,json_encode($row)."\n".json_encode([...$row,'ninox_id'=>'99','email'=>'invalid']));
        try { $this->assertSame(1,Artisan::call('data:load-engineers',['--source'=>$path]));$this->assertSame(0,FieldEngineer::count()); }
        finally { unlink($path); }
    }
}
