<?php
namespace App\Modules\DataReview\Actions;
use App\Models\User;
use Illuminate\Support\Facades\{DB,Storage};
use App\Modules\Services\Models\NinoxServiceImport;
use App\Modules\Tickets\Models\NinoxTicketImport;
use App\Modules\Catalogs\Models\NinoxCatalogImport;
class RemoveDemonstrationData
{
    private RecordReferences $references;
    private array $removed=[],$protected=[],$previewDirectories=[];
    public function execute(): array
    {
        $this->references=new RecordReferences;
        $this->removed=[]; $this->protected=[]; $this->previewDirectories=[];
        DB::transaction(fn()=> $this->clean());
        foreach($this->previewDirectories as $directory) { Storage::disk('local')->deleteDirectory($directory); }
        app(\App\Support\Cache\PageDataCache::class)->invalidate('users','clients','institutions','equipment','tickets','services','activities','technical_reports','institution-options');
        return ['removed'=>$this->removed,'protected'=>$this->protected];
    }
    private function clean(): void
    {
        $equipment=$this->candidates('equipment')->get();
        $models=$equipment->pluck('equipment_model_id')->filter()->all(); $brands=$equipment->pluck('brand_id')->filter()->all();
        $reports=$this->candidates('technical_reports')->pluck('id');
        $generations=DB::table('report_generations')->whereIn('technical_report_id',$reports)->pluck('id');
        foreach($generations as $id) { $this->previewDirectories[]='report-previews/'.$id; }
        $this->removed['report_generations']=DB::table('report_generations')->whereIn('id',$generations)->delete();
        foreach(['technical_reports','activities','services','tickets'] as $table) { $this->remove($table,$this->candidates($table)->pluck('id')->all()); }
        $this->remove('maintenance_visits',DB::table('maintenance_visits')->whereIn('maintenance_plan_id',$this->candidates('maintenance_plans')->pluck('id'))->pluck('id')->all());
        foreach(['maintenance_plans','equipment'] as $table) { $this->remove($table,$this->candidates($table)->pluck('id')->all()); }
        $contacts=$this->candidates('contacts')->pluck('id')->all();
        foreach($contacts as $id) {
            if (!DB::table('tickets')->where('requester_contact_id',$id)->exists()) { DB::table('contact_links')->where('contact_id',$id)->delete(); }
        }
        $this->remove('contacts',$contacts);
        foreach(['clients'] as $table) { $this->remove($table,$this->candidates($table)->pluck('id')->all()); }
        $this->remove('equipment_models',$models); $this->remove('brands',$brands);
        DB::table('services')->whereIn('id',NinoxServiceImport::select('service_id'))->where('service_type','No especificado en Ninox')->update(['service_type'=>'No especificado']);
        $this->users(); $this->remove('institutions',$this->candidates('institutions')->pluck('id')->all());
        foreach(DB::table('report_templates')->where('name','DEMO · Informe técnico')->get() as $template) {
            if (DB::table('technical_reports')->where('report_template_id',$template->id)->exists() || DB::table('report_generations')->where('report_template_id',$template->id)->exists()) { continue; }
            DB::table('report_templates')->where('id',$template->id)->update(['published_version_id'=>null]);
            DB::table('report_template_versions')->where('report_template_id',$template->id)->delete();
            $this->remove('report_templates',[$template->id]);
        }
    }
    private function candidates(string $table)
    {
        return DB::table($table)->where(fn($q)=>$q->where('code','like','DEMO-%')->orWhere('code','like','NAV-DEMO-%'));
    }
    private function remove(string $table,array $ids): void
    {
        foreach(array_unique($ids) as $id) {
            if ($this->imported($table,$id) || $this->references->exists($table,$id)) { $this->protected[$table][]= $id; continue; }
            $this->removed[$table]=($this->removed[$table] ?? 0)+DB::table($table)->where('id',$id)->delete();
        }
    }
    private function imported(string $table,int $id): bool
    {
        return match($table) {
            'clients','institutions'=>\App\Modules\DataManagement\Models\DataSourceRecord::where('entity',$table)->where('record_id',$id)->exists(),
            'services'=>NinoxServiceImport::where('service_id',$id)->exists(), 'tickets'=>NinoxTicketImport::where('ticket_id',$id)->exists(),
            'brands','equipment_models'=>NinoxCatalogImport::where('catalog',$table)->where('record_id',$id)->exists(), default=>false,
        };
    }
    private function users(): void
    {
        $users=User::where(fn($q)=>$q->where('email','like','%@navigation-demo.example.test')->orWhere('email','like','user.%@example.test')->orWhere('email','like','demo.%@example.test'))->get();
        foreach($users as $user) {
            if ($user->hasRole('SuperAdmin') || $this->references->exists('users',$user->id)) { $this->protected['users'][]=$user->id; continue; }
            DB::table('sessions')->where('user_id',$user->id)->delete();
            $user->delete(); $this->removed['users']=($this->removed['users'] ?? 0)+1;
        }
    }
}
