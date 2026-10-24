<?php

namespace App\Modules\MaintenancePlans\Configuration;

class CalendarStatus
{
    public static function forVisit($visit, $ticket = null): array
    {
        if ($visit->status === 'cancelled') {
            return ['cancelled', 'Cancelada'];
        }
        if ($visit->completed_services_exists) {
            return ['completed', 'Cumplida · Servicio completado'];
        }
        if ($ticket) {
            return match ($ticket->status) {
                'resolved' => ['resolved', 'Ticket resuelto · Servicio pendiente'], 'cancelled' => ['cancelled', 'Ticket cancelado'],
                'in_progress' => ['progress', 'En atención'], default => ['ticket', 'Con ticket'],
            };
        }
        if ($visit->ticket_exists) {
            return ['ticket', 'Con ticket'];
        }
        $overdue = $visit->schedule_precision === 'day' ? $visit->scheduled_for->lt(today()) : Periods::overdue($visit->scheduled_month);

        return $overdue ? ['overdue', 'Pendiente vencida'] : ['planned', 'Programada'];
    }
}
