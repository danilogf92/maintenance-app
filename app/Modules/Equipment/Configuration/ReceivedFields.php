<?php
namespace App\Modules\Equipment\Configuration;
class ReceivedFields
{
    public static function groups(): array
    {
        return ['Identificación'=>['main_component'=>'Componente principal','serial_number'=>'Serie','manufactured_at'=>'Fecha de fabricación',
            'owner'=>'Propietario informativo','garrantee_or_corpomedica'=>'En garantía o asumido por Corpomédica','gtin'=>'GTIN','corpomedica_asset_id'=>'Activo fijo'],
            'Mantenimiento'=>['date_last_maintenance'=>'Último mantenimiento registrado','annual_visits'=>'Visitas anuales registradas',
                'start_date_schedule'=>'Inicio de cronograma registrado','value_25'=>'Frecuencia manual (días, original)',
                'value_33'=>'Intervalo informativo (valor original, sin convertir)','report_type'=>'Tipo de reporte','documentation_ok'=>'Documentación conforme'],
            'Valores registrados'=>['pvp_ecu'=>'PVP ECU','ec_cost'=>'Costo ECU','discount_applied'=>'Descuento aplicado',
                'percentage_inspection'=>'Inspección (original)','percentage_corrective'=>'Correctivo (original)','preventive_percentage'=>'Preventivo (original)',
                'overhaul_percentage'=>'Overhaul (original)','installation_percentage'=>'Instalación (original)','bayer_status'=>'Estado Bayer registrado'],
            'Referencias pendientes'=>['equipment_medical_centers_history'=>'Historial de centros médicos','customer_history'=>'Historial de clientes para cobro',
                'equipment_components'=>'Componentes','contract_associated'=>'Contratos','contract_schedule'=>'Cronogramas de contrato',
                'conctacts_users'=>'Contactos relacionados','sales_advisor'=>'Asesor comercial','equipment_destination_loan'=>'Destino de préstamo',
                'equipment_list_to_transfer'=>'Equipos a trasladar','company_id'=>'Parámetros de empresa']];
    }
    public static function display(mixed $value): string
    {
        if ($value===null || $value==='' || $value===[]) { return '—'; }
        if (is_bool($value)) { return $value ? 'Sí' : 'No'; }
        return is_array($value) ? implode(', ',$value) : (string)$value;
    }
}
