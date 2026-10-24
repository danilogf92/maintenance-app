<?php

namespace App\Modules\Workflow\Configuration;

use App\Models\User;
use App\Modules\Workflow\Queries\VisibleRecords;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Gate;

class FormFields
{
    public static function relation(string $name, string $label, string $module): Select
    {
        return Select::make($name)->label($label)->required()->searchable()
            ->getSearchResultsUsing(fn (string $search) => self::options($module, $search))
            ->getOptionLabelUsing(function ($value) use ($module) {
                $record = $value ? VisibleRecords::query($module)->find($value) : null;
                return $record ? $record->code.' · '.($record->name ?? $record->subject ?? $record->title ?? '') : null;
            });
    }

    private static function options(string $module, string $search): array
    {
        $column = in_array($module, ['clients', 'equipment']) ? 'name' : ($module === 'tickets' ? 'subject' : 'code');

        return VisibleRecords::query($module)->where(fn ($q) => $q->where($column, 'like', '%'.$search.'%')->orWhere('code', 'like', '%'.$search.'%'))
            ->limit(30)->get()->mapWithKeys(fn ($r) => [$r->id => $r->code.' · '.($r->name ?? $r->subject ?? '')])->all();
    }

    public static function technicians(): array
    {
        if (! Gate::allows('workflow.view_all')) { return [auth()->id() => auth()->user()->name]; }

        return User::where('is_active', true)->whereHas('roles', fn ($q) => $q->whereIn('name', ['SuperAdmin', 'Administrador', 'Admin', 'Técnico', 'Engineer']))
            ->orderBy('name')->limit(500)->pluck('name', 'id')->all();
    }
}
