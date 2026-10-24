<?php
namespace App\Modules\Contacts\Configuration;

class Fields
{
    public static function definitions(): array
    {
        return ['name' => ['label' => 'Nombre completo', 'type' => 'text', 'required' => true, 'max' => 160],
            'position' => ['label' => 'Cargo', 'type' => 'text', 'max' => 120],
            'department' => ['label' => 'Área / departamento', 'type' => 'text', 'max' => 120],
            'active' => ['label' => 'Contacto activo', 'type' => 'checkbox'],
            'email' => ['label' => 'Correo electrónico', 'type' => 'email', 'max' => 160],
            'phone' => ['label' => 'Teléfono', 'type' => 'tel', 'max' => 60],
            'extension' => ['label' => 'Extensión', 'type' => 'text', 'max' => 20],
            'notes' => ['label' => 'Notas', 'type' => 'textarea', 'max' => 5000]];
    }
    public static function names(): array { return array_keys(self::definitions()); }
    public static function categories(): array
    {
        return ['technical' => 'Técnico', 'administrative' => 'Administrativo', 'reception' => 'Recepción', 'other' => 'Otro / sin clasificar'];
    }
}
