<?php

namespace App\Modules\ReportTemplates\Configuration;

use App\Support\Forms\HexColor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;

class HeaderFields
{
    public static function make(): array
    {
        $repeat = [Toggle::make('header_repeat')->label('Repetir este encabezado completo en todas las hojas')->live(),
            TextInput::make('header_height')->label('Espacio del encabezado repetido (mm)')->numeric()->minValue(20)->maxValue(90)
                ->helperText('Aumenta este espacio si agregas muchas líneas.')->visible(fn (Get $get) => $get('header_repeat'))->live(debounce: 600)];
        $fields = [Section::make('Repetición del encabezado')->schema($repeat)->collapsible()->collapsed()];
        foreach (['left' => 'Izquierda', 'center' => 'Centro', 'right' => 'Derecha'] as $key => $label) {
            $fields[] = Section::make('Encabezado · '.$label)->statePath('header_columns.'.$key)
                ->description('Logo automático y líneas con formato propio.')->collapsible()->collapsed()
                ->schema(self::column());
        }

        return $fields;
    }

    private static function column(): array
    {
        return [TextInput::make('width')->label('Ancho relativo de columna')->numeric()->required()->minValue(10)->maxValue(80)->live(debounce: 600),
            Select::make('alignment')->label('Alineación')->options(['left' => 'Izquierda', 'center' => 'Centro', 'right' => 'Derecha'])->required()->live(),
            Select::make('logo_source')->label('Origen del logo')->options([
                'none' => 'Sin logo (solo texto)', 'company' => 'Empresa: configuración del sistema',
                'factory' => 'Fabricante: datos del equipo', 'company_custom' => 'Logo empresa guardado en esta plantilla',
                'factory_custom' => 'Logo fabricante guardado en esta plantilla'])->required()->live()
                ->helperText('Se obtiene automáticamente al generar. Si el fabricante no tiene logo, se muestra su nombre.'),
            TextInput::make('logo_height')->label('Altura máxima del logo (mm)')->numeric()->required()->minValue(8)->maxValue(40)->live(debounce: 600),
            Toggle::make('border')->label('Mostrar borde')->live(),
            HexColor::make('border_color')->label('Color del borde')->required()->live(debounce: 600),
            Repeater::make('lines')->label('Líneas de texto')->schema(self::line())->default([])->maxItems(21)
                ->addActionLabel('Añadir texto o variable')->collapsible()->collapsed()
                ->itemLabel(fn (array $state): string => ($state['text'] ?? '') ?: 'Texto / variable')->live(debounce: 600)];
    }

    private static function line(): array
    {
        return [Select::make('mode')->label('Contenido')->options(['manual' => 'Texto manual', 'variable' => 'Texto + dato del registro'])
                ->default('manual')->required()->live(),
            TextInput::make('text')->label('Texto manual / texto antes del dato')->maxLength(160)->default('')->live(debounce: 600),
            Select::make('variable')->label('Dato automático')->options(Variables::options())->default('report.number')->required()->searchable()
                ->visible(fn (Get $get) => $get('mode') === 'variable')->live(),
            HexColor::make('color')->label('Color de letra')->default('#00858b')->required()->live(debounce: 600),
            TextStyle::size('size')->default(11)->required(),
            Select::make('font')->label('Tipo de letra')->options(TextStyle::fonts())->placeholder('Heredar')->live(),
            Toggle::make('italic')->label('Cursiva')->default(false)->live(),
            Toggle::make('bold')->label('Negrita')->default(true)->live()];
    }
}
