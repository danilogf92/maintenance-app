<?php

namespace App\Modules\TechnicalReports\Configuration;

use App\Modules\Workflow\Configuration\FormFields;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

class Fields
{
    public static function definitions(): array
    {
        return ['service_id' => ['label' => 'Servicio', 'type' => 'services', 'required' => true],
            'title' => ['label' => 'Título del informe', 'type' => 'text', 'required' => true],
            'report_template_id' => ['label' => 'Plantilla PDF', 'type' => 'templates', 'required' => true]];
    }

    public static function names(): array { return array_keys(self::definitions()); }

    public static function make(): array
    {
        return [FormFields::relation('service_id', 'Servicio', 'services'),
            Select::make('report_template_id')->label('Plantilla PDF')->options(fn () => \App\Modules\ReportTemplates\Models\ReportTemplate::orderBy('name')->pluck('name', 'id')->all())->required()->searchable()
                ->helperText('Usa la versión publicada o el borrador actual si todavía no se ha publicado. El informe resultante siempre es borrador.'),
            TextInput::make('title')->label('Título')->default('Informe técnico')->required()->maxLength(160)];
    }
}
