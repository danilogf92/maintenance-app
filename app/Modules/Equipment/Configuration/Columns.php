<?php

namespace App\Modules\Equipment\Configuration;

class Columns
{
    public static function columns(): array
    {
        return ['id' => 'ID', 'name' => 'Equipo', 'code' => 'Código', 'serial' => 'Serie', 'brand' => 'Marca', 'model' => 'Modelo',
            'status' => 'Estado', 'client' => 'Cliente', 'institution' => 'Institución', 'location' => 'Área / ubicación',
            'tickets' => 'Tickets visibles', 'created_at' => 'Creado el', 'updated_at' => 'Actualizado el'];
    }

    public static function views(): array
    {
        $views = [['name' => 'Todos', 'filter' => '']];
        foreach (Fields::statuses() as $value => $label) { $views[] = ['name' => $label, 'filter' => $value]; }
        return $views;
    }
}
