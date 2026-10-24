<?php

namespace App\Modules\Tickets\Configuration;

use App\Modules\Workflow\Configuration\FormFields;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;

class Fields
{
    public static function definitions(): array
    {
        return ['origin' => ['label' => 'Origen del ticket', 'type' => 'origin', 'required' => true],
            'requester_contact_id' => ['label' => 'Solicitante vinculado (opcional)', 'type' => 'requester'],
            'reported_by' => ['label' => 'Reportado por (texto opcional)', 'type' => 'text', 'max' => 160],
            'subject' => ['label' => 'Asunto', 'type' => 'text', 'required' => true, 'max' => 160],
            'description' => ['label' => 'Descripción', 'type' => 'textarea', 'max' => 5000],
            'priority' => ['label' => 'Prioridad', 'type' => 'priority', 'required' => true],
            'status' => ['label' => 'Estado', 'type' => 'status', 'required' => true],
            'opened_at' => ['label' => 'Fecha de apertura', 'type' => 'datetime-local', 'required' => true],
            'equipment_id' => ['label' => 'Equipo', 'type' => 'equipment', 'required' => true],
            'technician_id' => ['label' => 'Técnico asignado', 'type' => 'technicians', 'required' => true]];
    }

    public static function names(): array { return array_keys(self::definitions()); }
    public static function priorities(): array { return ['low' => 'Baja', 'normal' => 'Normal', 'high' => 'Alta', 'urgent' => 'Urgente', 'unknown' => 'No informada']; }

    public static function make(): array
    {
        return [FormFields::relation('equipment_id', 'Equipo', 'equipment'),
            Select::make('technician_id')->label('Técnico asignado')->options(fn () => FormFields::technicians())->default(fn () => auth()->id())->searchable()->required(),
            TextInput::make('subject')->label('Asunto')->required()->maxLength(160),
            Textarea::make('description')->label('Descripción')->maxLength(5000),
            Select::make('priority')->label('Prioridad')->options(['low' => 'Baja', 'normal' => 'Normal', 'high' => 'Alta', 'urgent' => 'Urgente'])->default('normal')->required(),
            Select::make('status')->label('Estado')->options(fn ($record) => $record?->ninoxImport()->exists() ? self::statuses() : array_diff_key(self::statuses(), ['historical'=>true]))->default('open')->required(),
            DateTimePicker::make('opened_at')->label('Fecha de apertura')->default(now())->required()];
    }

    public static function statuses(): array
    {
        return ['open' => 'Abierto', 'in_progress' => 'En atención', 'resolved' => 'Resuelto', 'cancelled' => 'Cancelado', 'historical' => 'Histórico importado'];
    }
}
