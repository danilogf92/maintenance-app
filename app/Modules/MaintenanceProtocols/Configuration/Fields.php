<?php
namespace App\Modules\MaintenanceProtocols\Configuration;
class Fields
{
    public static function definitions(): array
    {
        return ['name' => ['label' => 'Nombre del protocolo'], 'code' => ['label' => 'Código recibido'],
            'version' => ['label' => 'Versión'], 'type' => ['label' => 'Tipo'], 'model_info' => ['label' => 'Marca y modelo (informativo)'],
            'status_label' => ['label' => 'Estado recibido'], 'description' => ['label' => 'Descripción']];
    }
    public static function names(): array { return array_keys(self::definitions()); }
}
