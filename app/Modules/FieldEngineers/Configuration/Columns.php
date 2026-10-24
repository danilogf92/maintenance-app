<?php
namespace App\Modules\FieldEngineers\Configuration;
class Columns
{
    public static function columns(): array { return ['name'=>'Ingeniero','code'=>'Código','initials'=>'Iniciales','email'=>'Correo','phone'=>'Teléfono','country'=>'País','city'=>'Ciudad']; }
    public static function views(): array { return [['name'=>'Todos','filter'=>'']]; }
}
