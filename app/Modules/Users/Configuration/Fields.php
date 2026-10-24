<?php

namespace App\Modules\Users\Configuration;

class Fields
{
    public static function definitions(): array
    {
        return [
            'name' => ['label' => 'Nombre', 'type' => 'text', 'required' => true],
            'email' => ['label' => 'Correo electrónico', 'type' => 'email', 'required' => true],
            'institution_id' => ['label' => 'Institución', 'type' => 'institution', 'required' => true],
            'job_title' => ['label' => 'Cargo', 'type' => 'text'],
            'identification' => ['label' => 'Identificación', 'type' => 'text'],
            'phone' => ['label' => 'Teléfono', 'type' => 'tel'],
            'password' => ['label' => 'Contraseña', 'type' => 'password'],
            'role_ids' => ['label' => 'Roles', 'type' => 'roles'],
            'is_active' => ['label' => 'Cuenta activa', 'type' => 'checkbox'],
            'can_access_admin' => ['label' => 'Permitir acceso administrativo', 'type' => 'checkbox', 'super_admin_only' => true],
        ];
    }
}
