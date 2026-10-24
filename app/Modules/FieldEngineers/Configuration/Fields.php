<?php
namespace App\Modules\FieldEngineers\Configuration;
class Fields
{
    public static function definitions(): array
    {
        return ['name'=>['label'=>'Nombre completo'],'initials'=>['label'=>'Iniciales'],'country'=>['label'=>'País'],
            'city'=>['label'=>'Ciudad'],'email'=>['label'=>'Correo'],'phone'=>['label'=>'Teléfono']];
    }
    public static function names(): array { return array_keys(self::definitions()); }
}
