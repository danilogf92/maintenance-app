<?php

namespace App\Support\Forms;

use Filament\Forms\Components\ColorPicker;

class HexColor
{
    public static function make(string $name): ColorPicker
    {
        return ColorPicker::make($name)->hex()->rules(['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'])
            ->helperText('HEX: #RRGGBB. Ejemplo: #00858B.');
    }
}
