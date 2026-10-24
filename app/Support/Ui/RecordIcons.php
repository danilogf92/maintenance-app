<?php

namespace App\Support\Ui;

class RecordIcons
{
    public static function forView(string $module, string $value): string
    {
        if ($value === '') { return 'table'; }
        $icons = match ($module) {
            'contracts', 'maintenance_plans', 'users', 'clients', 'manufacturers', 'brands', 'equipment_models' => ['1' => 'check-circle', '0' => 'x-circle'],
            'institutions' => ['company' => 'briefcase', 'hospital' => 'medical', 'clinic' => 'medical', 'university' => 'book', 'other' => 'building'],
            'equipment' => ['operational' => 'check-circle', 'maintenance' => 'wrench', 'out_of_service' => 'alert', 'retired' => 'archive'],
            'activities' => ['with_result' => 'check-circle', 'pending_result' => 'clock', 'with_photos' => 'image'],
            'services' => ['draft' => 'pencil', 'in_progress' => 'wrench', 'completed' => 'check-circle', 'cancelled' => 'x-circle'],
            'tickets' => ['open' => 'ticket', 'in_progress' => 'clock', 'resolved' => 'check-circle', 'cancelled' => 'x-circle'],
            default => [],
        };
        return $icons[$value] ?? 'filter';
    }
}
