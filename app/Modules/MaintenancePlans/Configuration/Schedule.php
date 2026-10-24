<?php

namespace App\Modules\MaintenancePlans\Configuration;

use Carbon\CarbonImmutable;

class Schedule
{
    public static function units(): array
    {
        return ['days' => 'Días', 'weeks' => 'Semanas', 'months' => 'Meses', 'years' => 'Años'];
    }

    public static function date($plan, int $sequence): CarbonImmutable
    {
        $anchor = CarbonImmutable::parse($plan->schedule_precision === 'month' ? $plan->first_month.'-01' : $plan->first_date);
        $offset = ($sequence - 1) * $plan->interval_value;

        return match ($plan->interval_unit) {
            'days' => $anchor->addDays($offset), 'weeks' => $anchor->addWeeks($offset),
            'years' => $anchor->addYearsNoOverflow($offset), default => $anchor->addMonthsNoOverflow($offset),
        };
    }

    public static function capacity($plan): ?int
    {
        if (! $plan->end_date) {
            return null;
        }
        $start = CarbonImmutable::parse($plan->first_date);
        $end = CarbonImmutable::parse($plan->end_date);
        $count = match ($plan->interval_unit) {
            'days' => (int) floor($start->diffInDays($end) / $plan->interval_value) + 1,
            'weeks' => (int) floor($start->diffInDays($end) / ($plan->interval_value * 7)) + 1,
            default => (int) floor((($end->year - $start->year) * 12 + $end->month - $start->month) /
                ($plan->interval_value * ($plan->interval_unit === 'years' ? 12 : 1))) + 1,
        };
        while ($count > 0 && self::date($plan, $count)->gt($end)) {
            $count--;
        }

        return $count;
    }

    public static function label($plan): string
    {
        return 'Cada '.$plan->interval_value.' '.mb_strtolower(self::units()[$plan->interval_unit] ?? $plan->interval_unit);
    }
}
