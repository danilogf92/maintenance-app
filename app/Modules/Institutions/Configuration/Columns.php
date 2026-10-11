<?php

namespace App\Modules\Institutions\Configuration;

class Columns
{
    public static function columns(): array
    {
        return [
            'id' => 'ID',
            'name' => 'Nombre',
            'code' => 'Código',
            'type' => 'Tipo',
            'city' => 'Ciudad',
            'contact_email' => 'Correo de contacto',
            'contact_phone' => 'Teléfono',
            'users' => 'Usuarios',
            'created_at' => 'Creado el',
            'updated_at' => 'Actualizado el',
        ];
    }

    public static function views(): array
    {
        return [
            ['name' => 'Todos', 'filter' => ''],
            ['name' => 'Empresas', 'filter' => 'company'],
            ['name' => 'Hospitales', 'filter' => 'hospital'],
            ['name' => 'Clínicas', 'filter' => 'clinic'],
            ['name' => 'Universidades', 'filter' => 'university'],
            ['name' => 'Otros', 'filter' => 'other'],
        ];
    }
}
