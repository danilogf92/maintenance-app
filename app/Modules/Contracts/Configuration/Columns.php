<?php

namespace App\Modules\Contracts\Configuration;

class Columns
{
    public static function columns(): array
    {
        return ['name' => 'Contrato', 'number' => 'Número', 'client' => 'Cliente', 'status' => 'Estado', 'equipment' => 'Equipos'];
    }

    public static function views(): array
    {
        return [['name' => 'Todos', 'filter' => ''], ['name' => 'Activos', 'filter' => '1'], ['name' => 'Inactivos', 'filter' => '0']];
    }
}
