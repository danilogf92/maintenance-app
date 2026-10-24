<?php
namespace App\Modules\EquipmentModels\Configuration;
class Fields
{
    public static function definitions(): array { return ['name' => ['label' => 'Nombre', 'type' => 'text', 'max' => 100, 'required' => true],
'brand_id' => ['label' => 'Marca', 'type' => 'brands'],
'description' => ['label' => 'Descripción', 'type' => 'textarea', 'max' => 5000], 'visits_recommended_per_year' => ['label' => 'Visitas recomendadas por año', 'type' => 'number', 'max' => 365],
'active' => ['label' => 'Activo', 'type' => 'checkbox'], 'notes' => ['label' => 'Notas', 'type' => 'textarea', 'max' => 5000]]; }
    public static function names(): array { return array_keys(self::definitions()); }
}
