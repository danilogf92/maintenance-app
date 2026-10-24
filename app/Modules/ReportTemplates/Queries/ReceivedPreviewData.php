<?php
namespace App\Modules\ReportTemplates\Queries;
use App\Modules\Workflow\Configuration\Permissions;
use App\Modules\Workflow\Queries\VisibleRecords;
use Illuminate\Support\Facades\Gate;
class ReceivedPreviewData
{
    public function execute(): array
    {
        $source=[];$ticket='';$equipment=null;
        if (Permissions::allows('services')) {
            $service=VisibleRecords::query('services')->whereHas('ninoxImport')->orderBy('id')->first();
            if ($service) {
                $source=$service->ninoxImport->payload;
                if (Permissions::allows('tickets') && $service->ticket_id) {
                    $record=VisibleRecords::query('tickets')->find($service->ticket_id); $ticket=$record?->code ?? '';
                    if ($record && Permissions::allows('equipment')) { $equipment=$record->equipment; }
                }
            }
        }
        if (!$source && Gate::allows('workflow.view_all')) {
            $path=base_path('database/imports/ninox-service-logs/export/records.jsonl');
            if (is_file($path)) { $file=new \SplFileObject($path);$source=json_decode($file->fgets(),true,512,JSON_THROW_ON_ERROR); }
        }
        return ['is_sample'=>true,'report'=>['number'=>$source['report_code'] ?? '', 'date'=>$source['service_date'] ?? ''],
            'ticket'=>['number'=>$ticket],'service'=>['type'=>$source['service_type_realized'] ?? $source['service_type'] ?? ''],
            'institution'=>['name'=>$source['institution_name'] ?? '', 'city'=>$source['city'] ?? '', 'contact_email'=>'','contact_phone'=>''],
            'client'=>['name'=>$source['client_name'] ?? '', 'tax_id'=>''],
            'equipment'=>['name'=>$equipment?->name ?? '','brand'=>$equipment?->brand ?? '','model'=>$equipment?->model ?? '','serial'=>$equipment?->serial ?? '','location'=>$source['equipment_location'] ?? '','status'=>$source['final_system_status'] ?? ''],
            'technician'=>['name'=>$source['engineer_assigned'] ?? ''], 'activities'=>[], 'components'=>[], 'parts'=>[], 'recommendations'=>[]];
    }
}
