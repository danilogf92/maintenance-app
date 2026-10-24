<?php

namespace App\Modules\Users\Configuration;

class Tabs
{
    public static function definitions(): array
    {
        return [
            'general' => ['label' => 'General', 'icon' => 'user', 'sections' => [
                ['title' => 'Datos del usuario', 'fields' => ['name', 'email', 'institution_id', 'job_title']],
            ]],
            'contact' => ['label' => 'Contacto', 'icon' => 'phone', 'sections' => [
                ['title' => 'Información de contacto', 'fields' => ['identification', 'phone']],
            ]],
            'access' => ['label' => 'Acceso', 'icon' => 'shield', 'sections' => [
                ['title' => 'Seguridad', 'fields' => ['password'], 'widths' => ['password' => 'full']],
                ['title' => 'Roles y estado', 'fields' => ['role_ids', 'is_active', 'can_access_admin'], 'columns' => 1],
            ]],
        ];
    }
}
