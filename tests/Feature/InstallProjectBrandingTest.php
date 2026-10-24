<?php

namespace Tests\Feature;

use App\Models\BrandSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InstallProjectBrandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_installation_preserves_name_previous_file_and_is_idempotent(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('brand/previous.png', 'previous-logo');
        BrandSetting::forceCreate(['id' => 1, 'name' => 'Mi organización', 'logo_path' => 'brand/previous.png']);

        $this->artisan('branding:install-project-logo')->assertSuccessful();
        $setting = BrandSetting::findOrFail(1);
        $this->assertSame('Mi organización', $setting->name);
        $this->assertSame('brand/maintenance-mark-v11.59.0.png', $setting->logo_path);
        $this->assertSame('previous-logo', Storage::disk('public')->get('brand/previous.png'));
        $this->assertSame(file_get_contents(public_path('branding/maintenance/maintenance-mark-512.png')),
            Storage::disk('public')->get($setting->logo_path));
        $updatedAt = $setting->updated_at->toDateTimeString();
        $this->travel(1)->minutes();
        $this->artisan('branding:install-project-logo')->assertSuccessful();
        $this->assertSame($updatedAt, $setting->fresh()->updated_at->toDateTimeString());
        $this->assertSame(1, BrandSetting::count());
    }

    public function test_installation_creates_the_singleton_when_brand_settings_are_empty(): void
    {
        Storage::fake('public');
        $this->artisan('branding:install-project-logo')->assertSuccessful();
        $this->assertSame(config('app.name'), BrandSetting::findOrFail(1)->name);
    }
}
