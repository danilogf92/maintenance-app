<?php

namespace App\Console\Commands;

use App\Models\UiColorSetting;
use App\Support\Cache\PageDataCache;
use Illuminate\Console\Command;

class ApplyProjectPalette extends Command
{
    protected $signature = 'ui:apply-project-palette';

    protected $description = 'Unifica los colores de portada y operaciones; el administrador conserva su paleta propia.';

    public function handle(): int
    {
        $setting = UiColorSetting::find(1) ?? new UiColorSetting;
        $setting->id = 1;
        $setting->fill(config('identity.operations'));
        if ($setting->isDirty()) {
            $setting->save();
        }
        app(PageDataCache::class)->invalidate('palette');
        $this->info('Paleta operativa aplicada. Administración usa un acento violeta independiente.');

        return self::SUCCESS;
    }
}
