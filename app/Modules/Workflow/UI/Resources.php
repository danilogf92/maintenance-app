<?php

namespace App\Modules\Workflow\UI;

class Resources
{
    public static function all(): array
    {
        return ['clients' => \App\Modules\Clients\UI\ClientResource::class,
            'equipment' => \App\Modules\Equipment\UI\EquipmentResource::class,
            'tickets' => \App\Modules\Tickets\UI\TicketResource::class,
            'services' => \App\Modules\Services\UI\ServiceResource::class,
            'activities' => \App\Modules\Activities\UI\ActivityResource::class,
            'technical_reports' => \App\Modules\TechnicalReports\UI\TechnicalReportResource::class];
    }

    public static function get(string $module): string { return self::all()[$module]; }
}
