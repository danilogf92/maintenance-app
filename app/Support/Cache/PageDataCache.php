<?php

namespace App\Support\Cache;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/** Small, versioned data snapshots. Authorization always runs outside this cache. */
class PageDataCache
{
    private function store()
    {
        return Cache::store(config('page-cache.store', 'page-data'));
    }

    public function remember(string $bucket, array $variant, Closure $load, int $seconds, ?int $userId = null): mixed
    {
        if (! config('page-cache.enabled', true)) {
            return $load();
        }
        $versions = [$this->version($bucket)];
        if ($userId) {
            $versions[] = $this->version($bucket.'-entry-'.$userId);
        }
        $key = 'page-data:v1:'.hash('sha256', json_encode([$bucket, $versions, $userId, $variant]));
        // Wrap null results, since Cache::remember otherwise repeatedly loads missing settings.
        $result = $this->store()->remember($key, $seconds, fn () => ['value' => $load()]);

        return $result['value'];
    }

    public function invalidate(string ...$buckets): void
    {
        if (! config('page-cache.enabled', true)) {
            return;
        }
        foreach ($buckets as $bucket) {
            $this->store()->put('page-data:version:'.$bucket, (string) Str::uuid(), 86400);
        }
    }

    public function freshEntry(string $bucket, int $userId): void
    {
        $this->invalidate($bucket.'-entry-'.$userId);
    }

    private function version(string $bucket): string
    {
        $key = 'page-data:version:'.$bucket;
        $this->store()->add($key, (string) Str::uuid(), 86400);

        return (string) $this->store()->get($key);
    }
}
