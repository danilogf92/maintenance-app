<?php

namespace App\Modules\Clients\Configuration;

class Columns
{
    public static function columns(): array
    {
        return ['id' => 'ID', 'name' => 'Cliente', 'code' => 'Código', 'tax_id' => 'RUC / identificación',
            'status' => 'Estado', 'city' => 'Ciudad', 'email' => 'Correo', 'phone' => 'Teléfono',
            'address' => 'Dirección', 'equipment' => 'Equipos', 'created_at' => 'Creado el', 'updated_at' => 'Actualizado el'];
    }

    public static function views(): array
    {
        return [['name' => 'Todos', 'filter' => ''], ['name' => 'Activos', 'filter' => '1'], ['name' => 'Inactivos', 'filter' => '0']];
    }
}
