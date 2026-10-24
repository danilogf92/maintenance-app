<?php
namespace App\Modules\Contacts\Queries;

use Illuminate\Support\Facades\Gate;
use App\Modules\Workflow\Configuration\Permissions;

class ContactParents
{
    public static function allowed(string $type): bool
    {
        return match ($type) { 'client' => Permissions::allows('clients'), 'institution' => Gate::allows('institutions.manage'), default => false };
    }
    public static function find(string $type, int $id, bool $lock = false)
    {
        abort_unless(in_array($type, ['client', 'institution'], true), 422); abort_unless(self::allowed($type), 403);
        $class = $type === 'client' ? \App\Modules\Clients\Models\Client::class : \App\Models\Institution::class;
        return $class::query()->when($lock, fn ($q) => $q->lockForUpdate())->findOrFail($id);
    }
    public static function options(string $type, string $search, string $selected): array
    {
        if (!self::allowed($type)) { return []; }
        $class = $type === 'client' ? \App\Modules\Clients\Models\Client::class : \App\Models\Institution::class;
        $items = $class::where('name', 'like', '%'.$search.'%')->orderBy('name')->limit(30)->get();
        if ($selected && ($item = $class::find($selected)) && !$items->contains('id', $item->id)) { $items->prepend($item); }
        return $items->mapWithKeys(fn ($item) => [$item->id => $item->code.' · '.$item->name])->all();
    }
    public static function visibleLinks($query)
    {
        return $query->where(function ($q) {
            $q->whereRaw('1 = 0');
            if (self::allowed('client')) { $q->orWhereNotNull('client_id'); }
            if (self::allowed('institution')) { $q->orWhereNotNull('institution_id'); }
        });
    }
}
