<?php
namespace App\Modules\MaintenancePlans\Configuration;
use Carbon\CarbonImmutable;
class Periods
{
    public static function label(?string $month): string
    {
        return $month ? ucfirst(CarbonImmutable::createFromFormat('!Y-m', $month)->locale('es')->translatedFormat('F Y')) : 'Sin período';
    }
    public static function visitLabel($visit): string
    {
        return $visit->schedule_precision === 'day' ? $visit->scheduled_for->format('d/m/Y') : self::label($visit->scheduled_month);
    }
    public static function overdue(?string $month): bool { return $month && $month < today()->format('Y-m'); }
}
