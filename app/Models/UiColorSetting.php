<?php

namespace App\Models;

use App\Support\Cache\PageDataCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class UiColorSetting extends Model
{
    protected $fillable = ['primary', 'success', 'danger', 'secondary'];

    public static function currentPalette(): array
    {
        return app(PageDataCache::class)->remember('palette', [],
            fn () => static::loadPalette(), config('page-cache.settings_seconds', 300));
    }

    private static function loadPalette(): array
    {
        $defaults = config('identity.operations');
        if (! Schema::hasTable('ui_color_settings')) {
            return $defaults;
        }
        $setting = static::find(1);
        if (! $setting) {
            return $defaults;
        }
        foreach ($defaults as $name => $fallback) {
            $value = $setting->$name;
            if (is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
                $defaults[$name] = $value;
            }
        }

        return $defaults;
    }
}
