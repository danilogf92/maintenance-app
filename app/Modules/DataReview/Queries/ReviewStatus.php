<?php
namespace App\Modules\DataReview\Queries;
use App\Modules\Tickets\Models\NinoxTicketImport;
use App\Modules\Services\Models\NinoxServiceImport;
use App\Modules\Catalogs\Models\NinoxCatalogImport;
use Illuminate\Support\Facades\{DB,Storage};
class ReviewStatus
{
    public function execute(): array
    {
        $missingEquipment=0;$withoutEquipmentReference=0;
        foreach(NinoxTicketImport::with('ticket')->get() as $ticketSource) {
            if (empty($ticketSource->payload['value_16'])) { $withoutEquipmentReference++; }
            elseif (!$ticketSource->ticket->equipment_id) { $missingEquipment++; }
        }
        $models=DB::table('equipment_models')->whereIn('id',NinoxCatalogImport::where('catalog','equipment_models')->select('record_id'))->whereNull('brand_id')->get(['id','name']);
        $sources=NinoxServiceImport::with('service')->get(); $pending=0; $absent=0;
        foreach($sources as $source) {
            if (empty($source->payload['tickets_associated'])) { $absent++; }
            elseif (!$source->service->ticket_id) { $pending++; }
        }
        $cleanup=Storage::disk('local')->exists('data-review/cleanup.json') ? json_decode(Storage::disk('local')->get('data-review/cleanup.json'),true) : [];
        $additional=[];
        foreach (['equipment'=>'received_equipment','tickets'=>'ninox_ticket_imports','services'=>'ninox_service_imports','brands'=>'ninox_catalog_imports','equipment_models'=>'ninox_catalog_imports'] as $table=>$mapping) {
            $q=DB::table($mapping);
            if (in_array($table,['brands','equipment_models'])) { $q->where('catalog',$table); }
            $field=match($table) { 'equipment'=>'equipment_id','tickets'=>'ticket_id','services'=>'service_id',default=>'record_id' };
            $additional[$table]=DB::table($table)->whereNotIn('id',$q->select($field))->count();
        }
        return ['protocolReview'=>app(\App\Modules\MaintenanceProtocols\Queries\ProtocolReview::class)->execute(), 'engineerReview'=>app(EngineerReview::class)->execute(), 'equipmentReview'=>app(EquipmentReview::class)->execute(), 'additional'=>$additional, 'received'=>['Marcas'=>NinoxCatalogImport::where('catalog','brands')->count(),'Modelos'=>NinoxCatalogImport::where('catalog','equipment_models')->count(),
            'Ingenieros de servicio'=>\App\Modules\FieldEngineers\Models\FieldEngineer::count(),
            'Clientes'=>\App\Modules\DataManagement\Models\DataSourceRecord::where('entity','clients')->count(),
            'Centros médicos'=>\App\Modules\DataManagement\Models\DataSourceRecord::where('entity','institutions')->count(),
            'Historiales de clientes'=>DB::table('equipment_client_histories')->count(), 'Historiales de centros'=>DB::table('equipment_institution_histories')->count(),
            'Equipos'=>\App\Modules\Equipment\Models\ReceivedEquipment::count(),'Tickets'=>NinoxTicketImport::count(),'Bitácoras de servicio'=>$sources->count()], 'missingEquipment'=>$missingEquipment, 'withoutEquipmentReference'=>$withoutEquipmentReference,
            'models'=>$models,'pendingServices'=>$pending,'withoutSourceTicket'=>$absent,
            'missingDocuments'=>DB::table('service_documents')->where('status','missing')->count(),
            'invalidDocuments'=>DB::table('service_documents')->where('status','invalid')->count(),
            'protected'=>array_sum(array_map('count',$cleanup['protected'] ?? []))];
    }
    public static function allowed(): bool
    {
        return \App\Modules\Workflow\Configuration\Permissions::allows('equipment') && \App\Modules\Workflow\Configuration\Permissions::allows('tickets') && \App\Modules\Workflow\Configuration\Permissions::allows('services')
            && \App\Modules\Workflow\Configuration\Permissions::allows('brands') && \App\Modules\Workflow\Configuration\Permissions::allows('equipment_models')
            && auth()->user()->can('workflow.view_all');
    }
}
