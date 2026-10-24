<?php
namespace App\Modules\MaintenancePlans\Configuration;
class Tabs
{
    public static function definitions(): array
    {
        return ['general'=>['label'=>'General','icon'=>'calendar','sections'=>[
            ['title'=>'Plan de mantenimiento','fields'=>['name','service_type','active','notes'],'widths'=>['name'=>'full','notes'=>'full']]]],
            'equipment'=>['label'=>'Equipo y contrato','icon'=>'cpu','sections'=>[
                ['title'=>'Origen de las visitas','fields'=>['equipment_id','contract_coverage_id','contract_reference'],'columns'=>1]]],
            'schedule'=>['label'=>'Programación','icon'=>'clock','sections'=>[
                ['title'=>'Frecuencia y vigencia','fields'=>['interval_value','interval_unit','first_month','end_month','first_date','end_date','expected_visits','technician_id'],
                    'widths'=>['expected_visits'=>'full','technician_id'=>'full']]]],
            'visits'=>['label'=>'Visitas','icon'=>'clipboard','relation'=>'maintenance_visits']];
    }
}
