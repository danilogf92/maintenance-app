<?php

namespace App\Modules\Workflow\UI;

use App\Modules\TechnicalReports\Actions\QueueTechnicalPdf;
use App\Modules\Workflow\Configuration\Permissions;
use App\Modules\Workflow\Configuration\Modules;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class RecordActions
{
    public static function linked(string $module): array
    {
        $links = match ($module) {
            'clients' => [['equipment', 'Registrar equipo', 'client_id']],
            'equipment' => [['tickets', 'Abrir ticket', 'equipment_id']],
            'tickets' => [['services', 'Registrar servicio', 'ticket_id']],
            'services' => [['activities', 'Añadir actividad', 'service_id'], ['technical_reports', 'Crear informe', 'service_id']],
            default => [],
        };
        $actions = [];
        foreach ($links as [$target, $label, $field]) {
            $actions[] = Action::make('next_'.$target)->label($label)->icon('heroicon-o-plus')
                ->visible(fn () => Permissions::allows($target, 'manage'))
                ->url(fn ($record) => Resources::get($target)::getUrl('create', [$field => $record->id]));
        }
        foreach ($links as [$target, $label, $field]) {
            $actions[] = Action::make('related_'.$target)->label('Ver '.Modules::get($target)['label'])
                ->visible(fn () => Permissions::allows($target))
                ->url(fn ($record) => Resources::get($target)::getUrl('index', [$field => $record->id]));
        }
        if ($module === 'activities') {
            foreach (range(0, 3) as $index) {
                $actions[] = Action::make('photo_'.$index)->label('Foto '.($index + 1))->icon('heroicon-o-photo')
                    ->visible(fn ($record) => isset(($record->photos ?? [])[$index]))
                    ->url(fn ($record) => route('workflow.photo', ['activity' => $record->id, 'photo' => $index]))->openUrlInNewTab();
            }
        }
        if ($module === 'technical_reports') { array_push($actions, ...self::pdf()); }

        return $actions;
    }

    private static function pdf(): array
    {
        return [Action::make('generate_pdf')->label('Generar PDF')->icon('heroicon-o-document-arrow-down')
            ->action(function ($record) {
                app(QueueTechnicalPdf::class)->execute($record->id);
                Notification::make()->success()->title('PDF en cola. Consulta su estado en la lista de informes.')->send();
            }),
            Action::make('open_pdf')->label('Abrir PDF')->icon('heroicon-o-arrow-top-right-on-square')
                ->visible(fn ($record) => $record->lastGeneration?->status === 'ready' && $record->lastGeneration->expires_at->isFuture()
                    && (int) $record->lastGeneration->report_revision === (int) $record->revision)
                ->url(fn ($record) => route('workflow.pdf', ['report' => $record->id, 'generation' => $record->lastGeneration->id]))->openUrlInNewTab()];
    }
}
