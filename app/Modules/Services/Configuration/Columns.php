<?php

namespace App\Modules\Services\Configuration;

class Columns
{
    public static function columns(): array
    {
        return ['id' => 'ID', 'code' => 'Código', 'service_type' => 'Tipo de servicio', 'status' => 'Estado',
            'service_date' => 'Fecha', 'ticket' => 'Ticket', 'equipment' => 'Equipo', 'technician' => 'Técnico',
            'activities' => 'Actividades', 'reports' => 'Informes', 'created_at' => 'Creado el', 'updated_at' => 'Actualizado el'];
    }

    public static function views(): array
    {
        $views = [['name' => 'Todos', 'filter' => '']];
        foreach (Fields::statuses() as $value => $label) { $views[] = ['name' => $label, 'filter' => $value]; }
        return $views;
    }
}
