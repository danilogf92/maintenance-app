<?php
namespace App\Modules\MaintenanceProtocols\Configuration;
class Columns
{
    public static function columns(): array
    {
        return ['name' => 'Protocolo', 'code' => 'Código', 'version' => 'Versión', 'type' => 'Tipo',
            'model_info' => 'Marca y modelo', 'status_label' => 'Estado', 'steps_count' => 'Pasos'];
    }
    public static function views(): array { return [['name' => 'Todos', 'filter' => '']]; }
}
