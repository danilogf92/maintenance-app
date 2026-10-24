<?php
namespace App\Modules\TechnicalReports\Configuration;

class Columns
{
    public static function columns(): array
    {
        return ['id' => 'ID', 'code' => 'Código', 'title' => 'Título', 'service' => 'Servicio',
            'template' => 'Plantilla', 'revision' => 'Revisión', 'created_at' => 'Creado el', 'updated_at' => 'Actualizado el'];
    }
    public static function views(): array { return [['name' => 'Todos', 'filter' => '']]; }
}
