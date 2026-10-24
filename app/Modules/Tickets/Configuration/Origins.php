<?php
namespace App\Modules\Tickets\Configuration;
class Origins
{
    public static function all(): array
    {
        return ['internal' => 'Detección interna', 'client' => 'Reportado por el cliente', 'commercial' => 'Reportado por comercial',
            'scheduled' => 'Mantenimiento programado', 'legacy' => 'Origen no registrado (histórico)'];
    }
}
