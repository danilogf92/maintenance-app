<?php

namespace App\Console\Commands;

use App\Models\BrandSetting;
use App\Support\Cache\PageDataCache;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class InstallProjectBranding extends Command
{
    protected $signature = 'branding:install-project-logo';

    protected $description = 'Instala el nuevo símbolo del proyecto y conserva el nombre configurado.';

    public function handle(): int
    {
        $source = public_path('branding/maintenance/maintenance-mark-512.png');
        if (! is_file($source)) {
            $this->error('No se encontró el logo incluido en esta actualización.');

            return self::FAILURE;
        }

        $path = 'brand/maintenance-mark-v11.59.0.png';
        $bytes = file_get_contents($source);
        $disk = Storage::disk('public');
        if (! $disk->exists($path) || hash('sha256', $disk->get($path)) !== hash('sha256', $bytes)) {
            if (! $disk->put($path, $bytes)) {
                $this->error('No se pudo guardar el nuevo logo. Revisa los permisos de storage.');

                return self::FAILURE;
            }
        }

        $setting = BrandSetting::find(1) ?? new BrandSetting;
        $setting->id = 1;
        if (! $setting->exists) {
            $setting->name = config('app.name', 'Maintenance APP');
        }
        $setting->logo_path = $path;
        if ($setting->isDirty()) {
            $setting->save();
        }
        app(PageDataCache::class)->invalidate('brand');
        $this->info('Nuevo logo instalado; nombre de la aplicación conservado.');

        return self::SUCCESS;
    }
}
