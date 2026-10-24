<?php
namespace App\Modules\FieldEngineers\Import;
use App\Modules\Services\Models\NinoxServiceImport;
use Illuminate\Support\Facades\DB;
class LinkEngineerServices
{
    public function execute(): array
    {
        $evidence=new ServiceEngineerEvidence;
        $counts=['service_links_created'=>0,'service_links_existing'=>0,'service_links_pending'=>0,'service_link_conflicts'=>0];
        foreach (NinoxServiceImport::with('service.ticket.fieldEngineers')->get() as $source) {
            $match=$evidence->resolve($source);if (!$match) { $counts['service_links_pending']++;continue; }
            $result=DB::transaction(function () use ($source,$match) {
                $service=$source->service()->lockForUpdate()->firstOrFail();
                if ($service->field_engineer_id) { return (int)$service->field_engineer_id===(int)$match['id'] ? 'service_links_existing' : 'service_link_conflicts'; }
                $service->field_engineer_id=$match['id'];$service->field_engineer_basis=$match['basis'];$service->lock_version++;$service->save();
                return 'service_links_created';
            });$counts[$result]++;
        }
        return $counts;
    }
}
