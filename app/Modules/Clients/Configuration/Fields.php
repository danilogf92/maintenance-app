<?php

namespace App\Modules\Clients\Configuration;

use App\Modules\Workflow\Configuration\FormFields;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class Fields
{
    public static function definitions(): array
    {
        return ['name' => ['label' => 'Nombre / razón social', 'type' => 'text', 'required' => true, 'max' => 160],
            'tax_id' => ['label' => 'RUC / identificación', 'type' => 'text', 'max' => 40],
            'active' => ['label' => 'Cliente activo', 'type' => 'checkbox'],
            'email' => ['label' => 'Correo', 'type' => 'email', 'max' => 160],
            'phone' => ['label' => 'Teléfono', 'type' => 'tel', 'max' => 40],
            'address' => ['label' => 'Dirección', 'type' => 'text', 'max' => 240],
            'city' => ['label' => 'Ciudad', 'type' => 'text', 'max' => 100]];
    }

    public static function names(): array { return array_keys(self::definitions()); }

    public static function make(): array
    {
        return [TextInput::make('name')->label('Nombre / razón social')->required()->maxLength(160),
            TextInput::make('tax_id')->label('RUC / identificación')->maxLength(40),
            TextInput::make('email')->label('Correo')->email()->maxLength(160),
            TextInput::make('phone')->label('Teléfono')->maxLength(40),
            TextInput::make('address')->label('Dirección')->maxLength(240),
            TextInput::make('city')->label('Ciudad')->maxLength(100),
            Toggle::make('active')->label('Activo')->default(true)];
    }
}
