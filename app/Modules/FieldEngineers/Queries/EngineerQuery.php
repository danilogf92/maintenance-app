<?php
namespace App\Modules\FieldEngineers\Queries;
use App\Modules\FieldEngineers\Models\FieldEngineer;
use App\Modules\FieldEngineers\Configuration\Permissions;
use App\Modules\Workflow\Queries\VisibleRecords;
use App\Modules\Workflow\Configuration\Permissions as WorkflowPermissions;
class EngineerQuery
{
    public function records(string $search)
    {
        Permissions::authorize();return FieldEngineer::query()->select(['id','code','name','initials','email','phone','city','country'])
            ->when($search!=='',fn($q)=>$q->where(fn($m)=>$m->where('name','like','%'.$search.'%')->orWhere('initials','like','%'.$search.'%')->orWhere('email','like','%'.$search.'%')))
            ->orderBy('name')->orderBy('id')->get();
    }
    public function details(?int $id): array
    {
        Permissions::authorize();if (!$id) { return []; }$record=FieldEngineer::findOrFail($id);$source=$record->source_payload;
        $private=Permissions::allows('sensitive') ? array_intersect_key($source,array_flip(['id_number','cost_per_hour','bonus_percentage'])) : [];
        return ['reference'=>$record->source_id,'private'=>$private,
            'references'=>array_intersect_key($source,array_flip(['visits','tasks','certifications','contract_schedule'])),
            'tickets'=>WorkflowPermissions::allows('tickets') ? VisibleRecords::query('tickets')->whereHas('fieldEngineers',fn($q)=>$q->where('field_engineers.id',$id))->orderBy('code')->get(['tickets.id','code']) : collect(),
            'services'=>WorkflowPermissions::allows('services') ? VisibleRecords::query('services')->where('field_engineer_id',$id)->orderBy('code')->get(['id','code']) : collect()];
    }
}
