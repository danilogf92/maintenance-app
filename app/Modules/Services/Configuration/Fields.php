<?php

namespace App\Modules\Services\Configuration;

use App\Modules\Workflow\Configuration\FormFields;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;

class Fields
{
    public static function definitions(): array
    {
        return ['ticket_id' => ['label' => 'Ticket', 'type' => 'tickets', 'required' => true],
            'service_date' => ['label' => 'Fecha del servicio', 'type' => 'date', 'required' => true],
            'status' => ['label' => 'Estado', 'type' => 'status', 'required' => true],
            'service_type' => ['label' => 'Tipo de servicio', 'type' => 'text', 'required' => true, 'max' => 100],
            'summary' => ['label' => 'Resumen de la intervención', 'type' => 'textarea', 'max' => 5000],
            'recommendations' => ['label' => 'Recomendaciones', 'type' => 'textarea', 'max' => 5000]];
    }

    public static function names(): array { return array_keys(self::definitions()); }

    public static function make(): array
    {
        return [FormFields::relation('ticket_id', 'Ticket', 'tickets'),
            DatePicker::make('service_date')->label('Fecha de servicio')->default(today())->required(),
            TextInput::make('service_type')->label('Tipo de servicio')->default('Mantenimiento preventivo')->required()->maxLength(100),
            Select::make('status')->label('Estado')->options(fn ($record) => $record?->ninoxImport()->exists() ? self::statuses() : array_diff_key(self::statuses(), ['historical' => true]))->default('draft')->required(),
            Textarea::make('summary')->label('Resumen de la intervención')->maxLength(5000),
            Textarea::make('recommendations')->label('Recomendaciones (una por línea)')->maxLength(5000)];
    }

    public static function statuses(): array
    {
        return ['draft' => 'Borrador', 'in_progress' => 'En ejecución', 'completed' => 'Completado', 'cancelled' => 'Cancelado', 'historical' => 'Histórico importado'];
    }
}
