<?php
namespace App\Modules\MaintenancePlans\Configuration;
class Fields
{
    public static function definitions(): array
    {
        return ['name'=>['label'=>'Nombre del plan','type'=>'text','required'=>true,'max'=>160],
            'equipment_id'=>['label'=>'Equipo','type'=>'equipment','required'=>true],
            'contract_coverage_id'=>['label'=>'Cobertura contractual (opcional)','type'=>'coverage'],
            'service_type'=>['label'=>'Tipo de mantenimiento','type'=>'text','required'=>true,'max'=>120],
            'active'=>['label'=>'Plan activo','type'=>'checkbox'],
            'interval_unit'=>['label'=>'Unidad de frecuencia','type'=>'unit','required'=>true],
            'interval_value'=>['label'=>'Repetir cada','type'=>'number','required'=>true],
            'first_month'=>['label'=>'Mes inicial','type'=>'month'], 'end_month'=>['label'=>'Mes final (opcional)','type'=>'month'],
            'first_date'=>['label'=>'Fecha inicial','type'=>'date'], 'end_date'=>['label'=>'Fecha final (opcional)','type'=>'date'],
            'expected_visits'=>['label'=>'Cantidad comprometida total (opcional)','type'=>'number'],
            'technician_id'=>['label'=>'Técnico previsto (opcional)','type'=>'technician'],
            'contract_reference'=>['label'=>'Referencia histórica / externa (opcional)','type'=>'text','max'=>160],
            'notes'=>['label'=>'Indicaciones','type'=>'textarea','max'=>5000]];
    }
    public static function names(): array { return array_keys(self::definitions()); }
}
