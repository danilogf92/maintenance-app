<?php

namespace App\Modules\Clients\Configuration;

class Tabs
{
    public static function definitions(): array
    {
        return ['general' => ['label' => 'General', 'icon' => 'briefcase', 'sections' => [
            ['title' => 'Datos del cliente', 'fields' => ['name', 'tax_id', 'active'], 'widths' => ['name' => 'full']]]],
            'contact' => ['label' => 'Comunicación', 'icon' => 'phone', 'sections' => [
                ['title' => 'Información de contacto', 'fields' => ['email', 'phone', 'address', 'city'], 'widths' => ['address' => 'full']]]],
            'contacts' => ['label' => 'Contactos', 'icon' => 'users', 'relation' => 'contacts'],
            'equipment' => ['label' => 'Equipos relacionados', 'icon' => 'cpu', 'relation' => 'equipment']];
    }
}
