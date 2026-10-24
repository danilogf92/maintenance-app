<?php

namespace App\Modules\Institutions\Configuration;

class Fields
{
    public static function definitions(): array
    {
        return [
            'name' => ['label' => 'Nombre', 'type' => 'text', 'required' => true, 'max' => 255],
            'code' => ['label' => 'Código', 'type' => 'text', 'required' => true, 'max' => 255],
            'type' => ['label' => 'Tipo', 'type' => 'select', 'required' => true, 'options' => [
                'company' => 'Empresa', 'hospital' => 'Hospital', 'clinic' => 'Clínica',
                'university' => 'Universidad', 'other' => 'Otro']],
            'city' => ['label' => 'Ciudad', 'type' => 'text', 'required' => false, 'max' => 120],
            'address' => ['label' => 'Dirección', 'type' => 'text', 'required' => false, 'max' => 10000],
            'contact_email' => ['label' => 'Correo de contacto', 'type' => 'email', 'required' => false, 'max' => 255],
            'contact_phone' => ['label' => 'Teléfono', 'type' => 'text', 'required' => false, 'max' => 40],
        ];
    }

    public static function names(): array
    {
        return array_keys(self::definitions());
    }
}
