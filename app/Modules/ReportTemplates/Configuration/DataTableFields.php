<?php

namespace App\Modules\ReportTemplates\Configuration;

use App\Support\Forms\HexColor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;

class DataTableFields
{
    public static function make(): array
    {
        return [Section::make('Estilo de los datos')->collapsible()->collapsed()->schema([
            Toggle::make('show_table_header')->label('Mostrar títulos de las columnas')->default(true)->live(),
            Toggle::make('repeat_header')->label('Repetir títulos si la tabla continúa')->default(true)->live(),
            Toggle::make('show_labels')->label('Mostrar etiquetas de los campos')->default(true)->live(),
            Toggle::make('bordered')->label('Mostrar bordes de la tabla')->default(false)->live(),
            HexColor::make('section_background')->label('Fondo de los títulos')->required()->live(debounce: 600),
            HexColor::make('section_color')->label('Letra de los títulos')->required()->live(debounce: 600),
            HexColor::make('label_color')->label('Color de etiquetas')->required()->live(debounce: 600),
            HexColor::make('value_color')->label('Color de valores')->required()->live(debounce: 600),
        ]), Section::make('Datos en columnas')->collapsible()->collapsed()->schema([
            Repeater::make('data_sections')->label('Columnas')->minItems(1)->maxItems(3)->schema(self::section())
                ->collapsible()->collapsed()->addActionLabel('Añadir columna')->itemLabel(fn (array $state) => $state['title'] ?? 'Columna')->live(debounce: 600),
        ])];
    }

    private static function section(): array
    {
        return [TextInput::make('title')->label('Título de columna')->maxLength(80)->required()->live(debounce: 600),
            Toggle::make('show_title')->label('Mostrar este título')->default(true)->live(),
            Select::make('item_alignment')->label('Alineación de los ítems de esta columna')
                ->options(self::alignments())->default('left')->required()->live(),
            TextStyle::fields('title_style', 'Formato del encabezado de esta columna', true),
            TextStyle::fields('label_style', 'Formato de etiquetas de esta columna'),
            TextStyle::fields('value_style', 'Formato de valores de esta columna'),
            Repeater::make('fields')->label('Filas')->schema(self::row())->minItems(1)->maxItems(30)
                ->collapsible()->collapsed()->addActionLabel('Añadir dato')->itemLabel(fn (array $state) => ($state['label'] ?? '') ?: 'Dato')->live(debounce: 600)];
    }

    private static function alignments(): array
    {
        return ['left' => 'Izquierda', 'center' => 'Centro', 'right' => 'Derecha', 'legacy' => 'Diseño anterior: etiqueta y valor en celdas'];
    }

    private static function row(): array
    {
        return [Select::make('item_alignment')->label('Alineación del ítem (etiqueta + valor)')
                ->options(['inherit' => 'Heredar de la columna'] + self::alignments())->default('inherit')->required()->live(),
            TextInput::make('item_indent')->label('Sangría del ítem (mm)')->numeric()->minValue(0)->maxValue(30)->step(0.5)->default(0)->required()
                ->helperText('Desplaza este ítem desde el borde izquierdo. Se aplica con izquierda, centro o derecha.')->live(debounce: 600),
            TextInput::make('label')->label('Etiqueta (ejemplo: RUC:)')->default('')->maxLength(80)->live(debounce: 600),
            Select::make('mode')->label('Campo: texto o variable')->options(['variable' => 'Variable del sistema', 'manual' => 'Texto manual', 'signature' => 'Firma individual'])->default('variable')->required()->live(),
            Select::make('variable')->label('Dato')->options(Variables::options())->default('client.name')->searchable()
                ->required(fn (Get $get) => $get('mode') === 'variable')->visible(fn (Get $get) => $get('mode') === 'variable')->live(),
            Textarea::make('text')->label('Valor manual')->default('')->maxLength(500)->visible(fn (Get $get) => $get('mode') === 'manual')->live(debounce: 600),
            Signature::fields()->visible(fn (Get $get) => $get('mode') === 'signature'),
            TextStyle::fields('label_style', 'Formato de esta etiqueta'),
            TextStyle::fields('value_style', 'Formato de este valor')];
    }
}
