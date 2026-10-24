<?php

namespace App\Modules\MaintenancePlans\Configuration;

class Columns
{
    public static function columns(): array
    {
        return ['name' => 'Plan', 'code' => 'Código', 'equipment' => 'Equipo', 'first_month' => 'Mes inicial', 'technician' => 'Técnico previsto', 'status' => 'Estado', 'visits' => 'Visitas', 'origin'=>'Origen', 'contract_reference' => 'Referencia histórica / externa'];
    }

    public static function views(): array
    {
        return [['name' => 'Todos', 'filter' => ''], ['name' => 'Activos', 'filter' => '1'], ['name' => 'Inactivos', 'filter' => '0']];
    }
}
