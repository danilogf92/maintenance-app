<?php

namespace App\Modules\ReportTemplates\Configuration;

use App\Support\Forms\HexColor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Illuminate\Validation\Rule;

class TextStyle
{
    public static function fonts(): array
    {
        return ['sans' => 'DejaVu Sans', 'serif' => 'DejaVu Serif', 'mono' => 'DejaVu Sans Mono'];
    }

    public static function sizes(): array
    {
        return [8, 9, 10, 10.5, 11, 12, 14, 16, 18, 20, 22, 24, 26, 28, 36, 48, 72];
    }

    public static function size(string $name): TextInput
    {
        return TextInput::make($name)->label('Tamaño de letra (pt, como Word)')->numeric()->step(0.5)
            ->minValue(6)->maxValue(72)->datalist(self::sizes())->placeholder('Heredar')
            ->helperText('1 pt = 1/72 de pulgada. Admite 10.5, 11, 12, 14…')->live(debounce: 600);
    }

    public static function fields(string $name, string $label, bool $background = false): Section
    {
        $fields = [self::size('size'),
            Select::make('font')->label('Tipo de letra')->options(self::fonts())->placeholder('Heredar')->live(),
            HexColor::make('color')->label('Color de letra (HEX)')->live(debounce: 600),
            Select::make('alignment')->label('Alineación')->options(['left' => 'Izquierda', 'center' => 'Centro', 'right' => 'Derecha'])->placeholder('Heredar')->live(),
            Select::make('bold')->label('Negrita')->options([1 => 'Sí', 0 => 'No'])->placeholder('Heredar')->live(),
            Select::make('italic')->label('Cursiva')->options([1 => 'Sí', 0 => 'No'])->placeholder('Heredar')->live()];
        if ($background) { $fields[] = HexColor::make('background')->label('Fondo (HEX)')->live(debounce: 600); }

        return Section::make($label)->statePath($name)->schema($fields)->collapsible()->collapsed()
            ->description('Vacío = heredar el formato de la tabla o columna.');
    }

    public static function rules(string $path): array
    {
        return [$path => 'sometimes|nullable|array:size,font,color,alignment,bold,italic,background',
            $path.'.size' => 'sometimes|nullable|numeric|between:6,72',
            $path.'.font' => ['sometimes', 'nullable', Rule::in(array_keys(self::fonts()))],
            $path.'.alignment' => ['sometimes', 'nullable', Rule::in(['left', 'center', 'right'])],
            $path.'.bold' => 'sometimes|nullable|boolean', $path.'.italic' => 'sometimes|nullable|boolean',
            $path.'.color' => ['sometimes', 'nullable', 'regex:/^#[a-fA-F0-9]{6}$/'],
            $path.'.background' => ['sometimes', 'nullable', 'regex:/^#[a-fA-F0-9]{6}$/']];
    }

    public static function css(array ...$layers): string
    {
        $style = [];
        foreach ($layers as $layer) {
            $style = array_replace($style, array_filter($layer, fn ($value) => $value !== null && $value !== ''));
        }
        $css = [];
        foreach ($style as $key => $value) {
            $css[] = match ($key) {
                'size' => 'font-size:'.$value.'pt', 'font' => 'font-family:'.(self::fonts()[$value] ?? 'DejaVu Sans'),
                'alignment' => 'text-align:'.$value, 'bold' => 'font-weight:'.($value ? 'bold' : 'normal'),
                'italic' => 'font-style:'.($value ? 'italic' : 'normal'),
                'color', 'background' => $key.':'.$value, default => '',
            };
        }

        return implode(';', array_filter($css));
    }
}
