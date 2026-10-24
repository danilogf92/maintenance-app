<?php
namespace App\Modules\Contacts\Queries;

use App\Modules\Contacts\Models\Contact;
use App\Modules\Contacts\Configuration\Permissions;

class ContactQuery
{
    public function records(string $search, string $active)
    {
        Permissions::authorize();
        return Contact::query()->when($search !== '', fn ($q) => $q->where(fn ($m) => $m->where('name', 'like', '%'.$search.'%')
            ->orWhere('code', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%')->orWhere('phone', 'like', '%'.$search.'%')))
            ->when($active !== '', fn ($q) => $q->where('active', $active === '1'))->orderBy('name')->orderBy('id')->get();
    }
}
