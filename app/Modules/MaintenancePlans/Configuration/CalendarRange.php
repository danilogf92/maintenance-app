<?php

namespace App\Modules\MaintenancePlans\Configuration;

use Carbon\CarbonImmutable;

class CalendarRange
{
    public static function make(int $year, string $mode, string $anchor): array
    {
        $date = CarbonImmutable::parse($anchor);
        [$from,$to] = match ($mode) {
            'day' => [$date->startOfDay(), $date->endOfDay()],
            'week' => [$date->startOfWeek(), $date->endOfWeek()],
            'month' => [$date->startOfMonth(), $date->endOfMonth()],
            default => [CarbonImmutable::create($year, 1, 1), CarbonImmutable::create($year, 12, 31)],
        };

        return [$from->format('Y-m-d'), $to->format('Y-m-d')];
    }
}
