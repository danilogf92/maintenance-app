<?php

namespace App\Modules\ReportTemplates\Configuration;

use Filament\Forms\Components\FileUpload;
use App\Support\Forms\HexColor;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;

class Fields
{
    public static function block(string $type): array
    {
        $common = [TextInput::make('title')->label($type === 'header' ? 'Nombre del bloque en el editor' : 'Título')->required()->maxLength(120)->live(debounce: 600),
            Toggle::make('enabled')->label('Mostrar bloque')->live(),
            Toggle::make('hide_empty')->label('Ocultar si está vacío')->live(),
            Toggle::make('page_break')->label('Empezar en otra página')->live(),
            TextInput::make('spacing')->label('Separación inferior (mm)')->numeric()->minValue(0)->maxValue(20)->live(debounce: 600)];
        if (! in_array($type, ['header', 'separator'])) {
            $common[] = Toggle::make('show_title')->label('Mostrar título del bloque')->default(true)->live();
        }
        $fields = [Section::make('Información y distribución del bloque')->schema($common)->collapsible()->collapsed()];
        if (in_array($type, ['institution', 'equipment'])) {
            $fields[] = Section::make('Variables y columnas')->collapsible()->collapsed()->schema([
                Select::make('fields')->label('Variables')->options(Variables::options())->multiple()->searchable()->live(),
                Select::make('columns')->label('Columnas de datos')->options([1 => 'Una', 2 => 'Dos', 3 => 'Tres'])->live()]);
        }
        if ($type === 'header') {
            array_push($fields, ...HeaderFields::make());
            $logos = [];
            foreach (['company_logo' => 'Logo empresa', 'factory_logo' => 'Logo fabricante'] as $name => $label) {
                $logos[] = FileUpload::make($name)->label($label)->disk('public')->directory('report-assets')
                    ->helperText('Opcional: se carga una vez y se guarda en la plantilla. Selecciona su origen en la columna.')->image()->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])->maxSize(2048);
            }
            $fields[] = Section::make('Logos guardados en la plantilla')->schema($logos)->collapsible()->collapsed();
        }
        if (in_array($type, ['activities', 'components'])) {
            $fields[] = Section::make('Tabla y fotografías')->schema(self::photos())->collapsible()->collapsed();
        }
        if (in_array($type, ['data_table', 'activities', 'components', 'parts'])) {
            $fields[] = TextStyle::fields('table_header_style', 'Formato de encabezados de la tabla', true);
        }
        if ($type === 'signature') { $fields[] = Signature::fields(); }
        if ($type === 'data_table') { array_push($fields, ...DataTableFields::make()); }
        if ($type === 'separator') {
            $fields[] = Section::make('Franja del separador')->collapsible()->collapsed()->schema([
                HexColor::make('separator_background')->label('Fondo del separador')->required()->live(debounce: 600),
                HexColor::make('separator_color')->label('Color de letra')->required()->live(debounce: 600),
                Select::make('separator_alignment')->label('Alineación')->options(['left' => 'Izquierda', 'center' => 'Centro', 'right' => 'Derecha'])->required()->live()]);
        }
        if ($type === 'parts') { $fields[] = Toggle::make('show_table_header')->label('Mostrar encabezados de columnas')->default(true)->live(); }
        if ($type === 'text') {
            $fields[] = Section::make('Contenido de texto')->collapsible()->collapsed()->schema([
                RichEditor::make('text')->label('Contenido')->fileAttachments(false)
                    ->toolbarButtons(['bold', 'italic', 'underline', 'bulletList', 'orderedList', 'undo', 'redo'])->live(debounce: 800)]);
        }

        return $fields;
    }

    private static function photos(): array
    {
        return [Toggle::make('show_table_header')->label('Mostrar encabezados de columnas')->default(true)->live(),
            Select::make('table_columns')->label('Columnas de la tabla')->options(Blocks::tableOptions())->multiple()->required()->live(),
            Select::make('photo_columns')->label('Fotografías por fila')->options([1 => 'Una', 2 => 'Dos', 3 => 'Tres'])->live(),
            Select::make('photo_height')->label('Altura máxima de foto')->options([25 => 'Pequeña (25 mm)', 35 => 'Mediana (35 mm)', 50 => 'Grande (50 mm)'])->live(),
            Toggle::make('repeat_header')->label('Repetir encabezados en cada hoja')->live()];
    }
}
