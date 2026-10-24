<?php

namespace App\Modules\Workflow\Configuration;

class Modules
{
    public static function all(): array
    {
        return [
            'clients' => ['ui_icon' => 'briefcase', 'label' => 'Clientes', 'model' => \App\Modules\Clients\Models\Client::class, 'prefix' => 'CLI', 'icon' => 'heroicon-o-building-office'],
            'equipment' => ['ui_icon' => 'cpu', 'label' => 'Equipos', 'model' => \App\Modules\Equipment\Models\Equipment::class, 'prefix' => 'EQ', 'icon' => 'heroicon-o-cpu-chip'],
            'tickets' => ['ui_icon' => 'ticket', 'label' => 'Tickets', 'model' => \App\Modules\Tickets\Models\Ticket::class, 'prefix' => 'TK', 'icon' => 'heroicon-o-ticket'],
            'services' => ['ui_icon' => 'wrench', 'label' => 'Servicios', 'model' => \App\Modules\Services\Models\Service::class, 'prefix' => 'SV', 'icon' => 'heroicon-o-wrench-screwdriver'],
            'activities' => ['ui_icon' => 'activity', 'label' => 'Actividades', 'model' => \App\Modules\Activities\Models\Activity::class, 'prefix' => 'AC', 'icon' => 'heroicon-o-clipboard-document-list'],
            'technical_reports' => ['ui_icon' => 'file-text', 'label' => 'Informes técnicos', 'model' => \App\Modules\TechnicalReports\Models\TechnicalReport::class, 'prefix' => 'IT', 'icon' => 'heroicon-o-document-text'],
        ];
    }

    public static function configuration(string $module, string $class): string
    {
        $model = self::get($module)['model'];

        return substr($model, 0, strrpos($model, '\\Models\\')).'\\Configuration\\'.$class;
    }

    public static function get(string $module): array
    {
        abort_unless(isset(self::all()[$module]), 404);

        return self::all()[$module];
    }
}
