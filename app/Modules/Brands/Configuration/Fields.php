<?php
namespace App\Modules\Brands\Configuration;
class Fields
{
    public static function definitions(): array { return ['name' => ['label' => 'Nombre', 'type' => 'text', 'max' => 100, 'required' => true],
'manufacturer_id' => ['label' => 'Fabricante', 'type' => 'manufacturers'],
'distributor' => ['label' => 'Casa comercial', 'type' => 'text', 'max' => 255], 'origin' => ['label' => 'Procedencia', 'type' => 'text', 'max' => 255],
'active' => ['label' => 'Activo', 'type' => 'checkbox'], 'notes' => ['label' => 'Notas', 'type' => 'textarea', 'max' => 5000]]; }
    public static function names(): array { return array_keys(self::definitions()); }
}
