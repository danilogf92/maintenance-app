<?php

namespace App\Modules\Workflow\Configuration;

use Filament\Tables\Columns\TextColumn;

class TableColumns
{
    public static function make(string $module): array
    {
        $columns = [TextColumn::make('code')->label('Código')->searchable()->sortable()];
        $names = match ($module) {
            'clients' => ['name' => 'Cliente', 'tax_id' => 'RUC / identificación', 'city' => 'Ciudad'],
            'equipment' => ['name' => 'Equipo', 'serial' => 'Serie', 'client.name' => 'Cliente', 'institution.name' => 'Institución'],
            'tickets' => ['subject' => 'Asunto', 'equipment.code' => 'Equipo', 'technician.name' => 'Técnico'],
            'services' => ['ticket.code' => 'Ticket', 'service_type' => 'Servicio', 'technician.name' => 'Técnico'],
            'activities' => ['service.code' => 'Servicio', 'description' => 'Actividad', 'result' => 'Resultado'],
            'technical_reports' => ['title' => 'Informe borrador', 'service.code' => 'Servicio'],
        };
        foreach ($names as $name => $label) { $columns[] = TextColumn::make($name)->label($label)->searchable()->limit(55)->wrap(); }
        $fields = Modules::configuration($module, 'Fields');
        if (method_exists($fields, 'statuses')) {
            $columns[] = TextColumn::make('status')->label('Estado')->badge()->formatStateUsing(fn ($state) => $fields::statuses()[$state] ?? $state);
        }
        if ($module === 'clients') { $columns[] = TextColumn::make('active')->label('Activo')->formatStateUsing(fn ($state) => $state ? 'Sí' : 'No'); }
        if ($module === 'technical_reports') {
            $columns[] = TextColumn::make('revision')->label('Revisión');
            $columns[] = TextColumn::make('lastGeneration.status')->label('PDF')->placeholder('Sin generar')
                ->formatStateUsing(function ($state, $record) {
                    if ($record->lastGeneration?->expires_at->isPast()) { return 'Expirado'; }
                    if ((int) $record->lastGeneration?->report_revision !== (int) $record->revision) { return 'Requiere generar'; }
                    return ['queued' => 'En cola', 'processing' => 'Procesando', 'ready' => 'Listo', 'failed' => 'Falló'][$state] ?? $state;
                });
        }
        $columns[] = TextColumn::make('updated_at')->label('Actualizado')->dateTime('d/m/Y H:i')->sortable()->toggleable();

        return $columns;
    }
}
