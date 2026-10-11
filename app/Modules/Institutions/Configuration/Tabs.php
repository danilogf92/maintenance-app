<?php

namespace App\Modules\Institutions\Configuration;

class Tabs
{
    public static function definitions(): array
    {
        return [
            'general' => ['label' => 'General', 'sections' => [
                ['title' => 'Datos de la institución', 'fields' => ['name', 'code', 'type', 'city']],
            ]],
            'contact' => ['label' => 'Contacto', 'sections' => [
                ['title' => 'Información de contacto', 'fields' => ['contact_email', 'contact_phone']],
            ]],
            'users' => ['label' => 'Usuarios', 'relation' => 'users'],
        ];
    }
}
