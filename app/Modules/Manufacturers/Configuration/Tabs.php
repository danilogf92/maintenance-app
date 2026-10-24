<?php
namespace App\Modules\Manufacturers\Configuration;
class Tabs
{
    public static function definitions(): array
    {
        return ['general' => ['label' => 'General', 'icon' => 'info', 'sections' => [['title' => 'Identificación', 'fields' => ['name', 'country', 'website', 'active', 'notes'], 'columns' => 1]]],
            'logo' => ['label' => 'Logo', 'icon' => 'image', 'sections' => [], 'view' => 'catalogs::logo'],
            'relations' => ['label' => 'Relaciones', 'icon' => 'external-link', 'sections' => [], 'view' => 'catalogs::relations']];
    }
}
