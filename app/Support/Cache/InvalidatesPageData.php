<?php

namespace App\Support\Cache;

use App\Models\BrandSetting;
use App\Models\Institution;
use App\Models\UiColorSetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InvalidatesPageData
{
    public function saved(Model $model): void
    {
        $this->changed($model);
    }

    public function deleted(Model $model): void
    {
        $this->changed($model);
    }

    private function changed(Model $model): void
    {
        $buckets = match (true) {
            $model instanceof User => ['users', 'institutions'],
            $model instanceof Institution => ['institutions', 'users', 'institution-options'],
            $model instanceof BrandSetting => ['brand'],
            $model instanceof UiColorSetting => ['palette'],
            default => ['users'],
        };
        $this->invalidate($buckets);
    }

    public function rolesChanged(): void
    {
        $this->invalidate(['users']);
    }

    private function invalidate(array $buckets): void
    {
        app(PageDataCache::class)->invalidate(...$buckets);
        // A reader may refill old data before commit. Rotate again after a successful commit.
        if (DB::transactionLevel() > 0) {
            DB::afterCommit(fn () => app(PageDataCache::class)->invalidate(...$buckets));
        }
    }
}
