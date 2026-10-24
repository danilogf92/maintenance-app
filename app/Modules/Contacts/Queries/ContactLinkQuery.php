<?php
namespace App\Modules\Contacts\Queries;

use App\Modules\Contacts\Models\{Contact, ContactLink};

class ContactLinkQuery
{
    public static function scoped(?int $contactId, ?string $type, ?int $parentId)
    {
        $query = ContactLink::query();
        if ($contactId) { return ContactParents::visibleLinks($query->where('contact_id', $contactId)); }
        return $query->where($type === 'client' ? 'client_id' : 'institution_id', $parentId);
    }
    public static function listing(?int $contactId, ?string $type, ?int $parentId, string $search, bool $inactive)
    {
        $query = self::scoped($contactId, $type, $parentId)->with('contact');
        if (ContactParents::allowed('client')) { $query->with('client'); }
        if (ContactParents::allowed('institution')) { $query->with('institution'); }
        return $query->when(!$inactive, fn ($q) => $q->where('active', true))
            ->when($search !== '', fn ($q) => $q->where(function ($m) use ($search) {
                $m->whereHas('contact', fn ($c) => $c->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%'));
                foreach (['client', 'institution'] as $parent) {
                    if (ContactParents::allowed($parent)) { $m->orWhereHas($parent, fn ($p) => $p->where('name', 'like', '%'.$search.'%')); }
                }
            }))->orderByDesc('is_primary')->orderByDesc('active')->orderBy('id')->paginate(15);
    }
    public static function contacts(string $search, string $selected): array
    {
        $items = Contact::where('active', true)->where('name', 'like', '%'.$search.'%')->orderBy('name')->limit(30)->get();
        if ($selected && ($item = Contact::find($selected)) && !$items->contains('id', $item->id)) { $items->prepend($item); }
        return $items->mapWithKeys(fn ($item) => [$item->id => $item->code.' · '.$item->name])->all();
    }
}
