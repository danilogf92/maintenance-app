<?php
namespace App\Modules\EquipmentModels\Configuration;
class Tabs
{
    public static function definitions(): array
    {
        return ['general' => ['label' => 'General', 'icon' => 'info', 'sections' => [['title' => 'Identificación', 'fields' => ['name', 'brand_id', 'active', 'notes'], 'columns' => 1]]],
            
            'details' => ['label' => 'Datos del modelo', 'icon' => 'info', 'sections' => [['title' => 'Descripción y recomendaciones', 'fields' => ['description', 'visits_recommended_per_year'], 'columns' => 1]]],
            'ninox' => ['label' => 'Detalles', 'icon' => 'file-text', 'sections' => [], 'view' => 'catalogs::ninox'],
            'relations' => ['label' => 'Relaciones', 'icon' => 'external-link', 'sections' => [], 'view' => 'catalogs::relations']];
    }
}
