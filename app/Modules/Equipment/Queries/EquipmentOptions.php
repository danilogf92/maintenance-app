<?php

namespace App\Modules\Equipment\Queries;

use App\Models\Institution;
use App\Modules\Workflow\Configuration\Permissions;
use App\Modules\Workflow\Queries\VisibleRecords;

class EquipmentOptions
{
    public static function clients(string $search, string $selected): array
    {
        if (! Permissions::allows('clients')) { return []; }
        $query = VisibleRecords::query('clients');
        $items = (clone $query)->where(fn ($q) => $q->where('name', 'like', '%'.$search.'%')->orWhere('code', 'like', '%'.$search.'%'))
            ->orderBy('name')->limit(30)->get();
        if ($selected && ($record = $query->find($selected)) && ! $items->contains('id', $record->id)) { $items->prepend($record); }
        return $items->mapWithKeys(fn ($record) => [$record->id => $record->code.' · '.$record->name.($record->active ? '' : ' (inactivo)')])->all();
    }

    public static function institutions(string $search, string $selected): array
    {
        $items = Institution::where('name', 'like', '%'.$search.'%')->orderBy('name')->limit(30)->get();
        if ($selected && ($record = Institution::find($selected)) && ! $items->contains('id', $record->id)) { $items->prepend($record); }
        return $items->mapWithKeys(fn ($record) => [$record->id => $record->name])->all();
    }
}
