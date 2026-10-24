<?php

namespace App\Modules\Activities\Configuration;

class Columns
{
    public static function columns(): array
    {
        return ['id' => 'ID', 'code' => 'Código', 'description' => 'Actividad / hallazgo', 'result' => 'Resultado',
            'service' => 'Servicio', 'equipment' => 'Equipo', 'technician' => 'Técnico', 'photos' => 'Fotografías',
            'created_at' => 'Creado el', 'updated_at' => 'Actualizado el'];
    }

    public static function views(): array
    {
        return [['name' => 'Todas', 'filter' => ''], ['name' => 'Con resultado', 'filter' => 'with_result'],
            ['name' => 'Resultado pendiente', 'filter' => 'pending_result'], ['name' => 'Con fotografías', 'filter' => 'with_photos']];
    }
}
