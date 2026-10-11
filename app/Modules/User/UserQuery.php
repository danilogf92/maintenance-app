<?php

namespace App\Modules\User;

use App\Models\User;
use App\Support\Cache\PageDataCache;
use Illuminate\Database\Eloquent\Builder;

class UserQuery
{
    public function records(int $userId, string $search, string $institution, string $active)
    {
        return app(PageDataCache::class)->remember('users', [$search, $institution, $active],
            fn () => $this->build($search, $institution, $active)->get(),
            config('page-cache.records_seconds', 30), $userId);
    }

    public function build(string $search, string $institution, string $active): Builder
    {
        return User::query()->select(['id', 'institution_id', 'name', 'email', 'identification', 'phone',
            'job_title', 'is_active', 'can_access_admin', 'theme_preference', 'email_verified_at', 'created_at', 'updated_at'])
            ->with(['institution:id,name', 'roles:id,name'])
            ->when($search !== '', fn ($query) => $query->where(fn ($match) => $match
                ->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%')))
            ->when($institution !== '', fn ($query) => $query->where('institution_id', $institution))
            ->when(in_array($active, ['0', '1'], true), fn ($query) => $query->where('is_active', (int) $active))
            ->orderBy('name')->orderBy('id');
    }
}
