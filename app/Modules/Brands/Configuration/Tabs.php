<?php
namespace App\Modules\Brands\Configuration;
class Tabs
{
    public static function definitions(): array
    {
        return ['general' => ['label' => 'General', 'icon' => 'info', 'sections' => [['title' => 'Identificación', 'fields' => ['name', 'manufacturer_id', 'active', 'notes'], 'columns' => 1]]],
            
            'commercial' => ['label' => 'Datos comerciales', 'icon' => 'info', 'sections' => [['title' => 'Información de origen', 'fields' => ['distributor', 'origin'], 'columns' => 1]]],
            'ninox' => ['label' => 'Detalles', 'icon' => 'file-text', 'sections' => [], 'view' => 'catalogs::ninox'],
            'relations' => ['label' => 'Relaciones', 'icon' => 'external-link', 'sections' => [], 'view' => 'catalogs::relations']];
    }
}
