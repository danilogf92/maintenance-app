<?php

namespace App\Modules\Tickets\Configuration;

class Columns
{
    public static function columns(): array
    {
        return ['id' => 'ID', 'subject' => 'Asunto', 'code' => 'Código', 'status' => 'Estado', 'priority' => 'Prioridad',
            'ninox_status' => 'Estado registrado', 'origin' => 'Origen', 'equipment' => 'Equipo', 'technician' => 'Técnico', 'opened_at' => 'Apertura', 'services' => 'Servicios visibles',
            'created_at' => 'Creado el', 'updated_at' => 'Actualizado el'];
    }

    public static function views(): array
    {
        $views = [['name' => 'Todos', 'filter' => '']];
        foreach (Fields::statuses() as $value => $label) { $views[] = ['name' => $label, 'filter' => $value]; }
        return $views;
    }
}
