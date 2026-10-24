<?php

namespace App\Modules\Contracts\Configuration;

class Fields
{
    public static function definitions(): array
    {
        return ['number' => ['label' => 'Número de contrato', 'type' => 'text', 'required' => true, 'max' => 100],
            'name' => ['label' => 'Nombre del contrato', 'type' => 'text', 'required' => true, 'max' => 160],
            'client_id' => ['label' => 'Cliente (opcional)', 'type' => 'client'], 'active' => ['label' => 'Contrato activo', 'type' => 'checkbox'],
            'notes' => ['label' => 'Condiciones generales', 'type' => 'textarea', 'max' => 5000]];
    }

    public static function names(): array
    {
        return array_keys(self::definitions());
    }
}
