<?php

namespace App\Modules\Institutions\Configuration;

class Tabs
{
    public static function definitions(): array
    {
        return [
            'general' => ['label' => 'General', 'icon' => 'info', 'sections' => [
                ['title' => 'Datos de la institución', 'fields' => ['name', 'code', 'type', 'city', 'address']],
            ]],
            'contact' => ['label' => 'Comunicación', 'icon' => 'phone', 'sections' => [
                ['title' => 'Información de contacto', 'fields' => ['contact_email', 'contact_phone']],
            ]],
            'contacts' => ['label' => 'Contactos', 'icon' => 'users', 'relation' => 'contacts'],
            'users' => ['label' => 'Usuarios', 'icon' => 'users', 'relation' => 'users'],
        ];
    }
}
