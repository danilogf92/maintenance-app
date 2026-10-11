<?php

namespace App\Models;

use App\Support\Cache\PageDataCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class BrandSetting extends Model
{
    protected $fillable = ['name', 'logo_path'];

    public static function current(): ?self
    {
        return app(PageDataCache::class)->remember('brand', [],
            fn () => Schema::hasTable('brand_settings') ? self::find(1) : null,
            config('page-cache.settings_seconds', 300));
    }

    public static function displayName(): string
    {
        return self::current()?->name ?? config('app.name');
    }

    public static function logoUrl(): ?string
    {
        $setting = self::current();

        return $setting?->logo_path ? Storage::disk('public')->url($setting->logo_path).'?v='.$setting->updated_at->timestamp : null;
    }
}
