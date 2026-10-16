<?php

namespace App\Modules\ReportTemplates\Configuration;

use App\Support\Forms\HexColor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Illuminate\Validation\Rule;

class Signature
{
    public static function defaults(): array
    {
        return ['source' => 'blank', 'alignment' => 'left', 'width' => 60, 'height' => 18, 'show_line' => true,
            'line_color' => '#00858b', 'caption' => 'Responsable técnico', 'show_name' => true,
            'name_variable' => 'technician.name', 'name' => '', 'style' => []];
    }

    public static function sources(): array
    {
        return ['blank' => 'Espacio para firmar en papel', 'technician' => 'Firma del técnico de este informe',
            'recipient' => 'Firma del receptor de este informe'];
    }

    public static function fields(): Section
    {
        return Section::make('Firma')->statePath('signature')->collapsible()->collapsed()->schema([
            Select::make('source')->label('Origen de la firma')->options(self::sources())->required()->live(),
            Select::make('alignment')->label('Alineación de firma individual')->options(['left' => 'Izquierda', 'center' => 'Centro', 'right' => 'Derecha'])->required()->live(),
            TextInput::make('width')->label('Ancho (mm)')->numeric()->minValue(15)->maxValue(160)->required()->live(debounce: 600),
            TextInput::make('height')->label('Alto (mm)')->numeric()->minValue(5)->maxValue(60)->required()->live(debounce: 600),
            Toggle::make('show_line')->label('Mostrar línea para firmar')->live(),
            HexColor::make('line_color')->label('Color de línea')->required()->live(debounce: 600),
            TextInput::make('caption')->label('Texto bajo la firma')->maxLength(120)->live(debounce: 600),
            Toggle::make('show_name')->label('Mostrar nombre')->live(),
            Select::make('name_variable')->label('Nombre automático')->options(['' => 'Usar nombre manual'] + Variables::options())->live(),
            TextInput::make('name')->label('Nombre manual')->maxLength(120)->live(debounce: 600),
            TextStyle::fields('style', 'Formato de firma')]);
    }

    public static function rules(string $path): array
    {
        return array_merge([$path => 'sometimes|array:source,alignment,width,height,show_line,line_color,caption,show_name,name_variable,name,style',
            $path.'.source' => ['required_with:'.$path, Rule::in(array_keys(self::sources()))],
            $path.'.alignment' => ['sometimes', Rule::in(['left', 'center', 'right'])],
            $path.'.width' => 'required_with:'.$path.'|numeric|between:15,160',
            $path.'.height' => 'required_with:'.$path.'|numeric|between:5,60',
            $path.'.show_line' => 'required_with:'.$path.'|boolean', $path.'.show_name' => 'required_with:'.$path.'|boolean',
            $path.'.line_color' => ['required_with:'.$path, 'regex:/^#[a-fA-F0-9]{6}$/'],
            $path.'.caption' => 'sometimes|nullable|string|max:120', $path.'.name' => 'sometimes|nullable|string|max:120',
            $path.'.name_variable' => ['sometimes', 'nullable', Rule::in(array_keys(Variables::options()))]], TextStyle::rules($path.'.style'));
    }
}
