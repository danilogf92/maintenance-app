<?php

namespace App\Modules\User;

class UserDefinition
{
    public static function columns(): array
    {
        return [
            'id' => 'ID',
            'name' => 'Usuario',
            'email' => 'Correo',
            'institution' => 'Institución',
            'institution_id' => 'ID institución',
            'job_title' => 'Cargo',
            'roles' => 'Roles',
            'status' => 'Estado',
            'identification' => 'Identificación',
            'phone' => 'Teléfono',
            'can_access_admin' => 'Acceso administrativo',
            'email_verified_at' => 'Correo verificado',
            'theme_preference' => 'Tema',
            'created_at' => 'Creado el',
            'updated_at' => 'Actualizado el',
        ];
    }

    public static function views(): array
    {
        return [
            ['name' => 'Todos', 'filter' => ''],
            ['name' => 'Activos', 'filter' => '1'],
            ['name' => 'Inactivos', 'filter' => '0'],
        ];
    }
}
