<?php

namespace App\Modules\Contacts\Configuration;

class Columns
{
    public static function columns(): array
    {
        return ['name' => 'Contacto', 'code' => 'Código', 'position' => 'Cargo', 'department' => 'Área', 'status' => 'Estado', 'email' => 'Correo', 'phone' => 'Teléfono', 'extension' => 'Extensión', 'created_at' => 'Creado el', 'updated_at' => 'Actualizado el'];
    }

    public static function views(): array
    {
        return [['name' => 'Todos', 'filter' => ''], ['name' => 'Activos', 'filter' => '1'], ['name' => 'Inactivos', 'filter' => '0']];
    }
}
