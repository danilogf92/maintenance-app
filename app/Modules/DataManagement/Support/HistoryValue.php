<?php
namespace App\Modules\DataManagement\Support;
class HistoryValue
{
    public static function references(mixed $value): ?array
    {
        if ($value === null || $value === '') { return []; }
        $values = is_array($value) ? $value : [$value]; $ids = [];
        if (!array_is_list($values)) { return null; }
        foreach ($values as $id) {
            if ((!is_int($id) && !is_string($id)) || !preg_match('/^[1-9][0-9]{0,17}$/D', (string)$id)) { return null; }
            $ids[] = (string)$id;
        }
        return array_values(array_unique($ids));
    }
    public static function single(mixed $value): ?string
    {
        $ids = self::references($value); return $ids !== null && count($ids) === 1 ? $ids[0] : null;
    }
    public static function date(mixed $value): array
    {
        if ($value === null || $value === '') { return ['value' => null, 'date_only' => true, 'issue' => 'date_missing']; }
        if (!is_string($value)) { return ['value' => null, 'date_only' => true, 'issue' => 'date_invalid']; }
        $only = preg_match('/^\d{4}-\d{2}-\d{2}$/D', $value) === 1;
        if (!$only && !preg_match('/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}(?:\.\d{1,6})?(?:Z|[+-]\d{2}:\d{2})?$/D', $value)) {
            return ['value' => null, 'date_only' => false, 'issue' => 'date_invalid'];
        }
        try {
            $date = new \DateTimeImmutable($value, new \DateTimeZone('America/Guayaquil')); $errors = \DateTimeImmutable::getLastErrors();
            if ($errors && ($errors['warning_count'] || $errors['error_count'])) { throw new \InvalidArgumentException; }
            // Date-only values remain calendar dates; timestamp values are normalized to Ecuador's local time.
            $date = $date->setTimezone(new \DateTimeZone('America/Guayaquil'));
            return ['value' => $date->format('Y-m-d H:i:s'), 'date_only' => $only, 'issue' => null];
        } catch (\Exception) { return ['value' => null, 'date_only' => $only, 'issue' => 'date_invalid']; }
    }
}
