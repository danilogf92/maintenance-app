<?php
namespace App\Modules\FieldEngineers\Configuration;
class Tabs
{
    public static function definitions(): array
    {
        return ['general'=>['label'=>'General','icon'=>'user','sections'=>[['title'=>'Ingeniero de servicio','fields'=>['name','initials','country','city'],'widths'=>['name'=>'full']]]],
            'contact'=>['label'=>'Contacto','icon'=>'phone','sections'=>[['title'=>'Datos de contacto recibidos','fields'=>['email','phone']]]],
            'details'=>['label'=>'Detalles','icon'=>'file-text','sections'=>[],'view'=>'field-engineers::details'],
            'relations'=>['label'=>'Relaciones','icon'=>'ticket','sections'=>[],'view'=>'field-engineers::relations']];
    }
}
