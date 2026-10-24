<?php
namespace App\Modules\Manufacturers\Configuration;
class Fields
{
    public static function definitions(): array { return ['name' => ['label' => 'Nombre', 'type' => 'text', 'max' => 160, 'required' => true],
'country' => ['label' => 'País', 'type' => 'text', 'max' => 100], 'website' => ['label' => 'Sitio web', 'type' => 'url', 'max' => 255],
'active' => ['label' => 'Activo', 'type' => 'checkbox'], 'notes' => ['label' => 'Notas', 'type' => 'textarea', 'max' => 5000]]; }
    public static function names(): array { return array_keys(self::definitions()); }
}
