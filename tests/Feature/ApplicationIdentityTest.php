<?php
namespace Tests\Feature;
use App\Models\UiColorSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class ApplicationIdentityTest extends TestCase
{
    use RefreshDatabase;
    public function test_public_and_normal_application_share_configured_color_while_admin_is_distinct(): void
    {
        $this->artisan('ui:apply-project-palette')->assertSuccessful();
        $this->assertSame('#BE4128',UiColorSetting::currentPalette()['primary']);
        $this->get('/')->assertOk()->assertSee('--portal-accent:#BE4128',false);
        $this->assertNotSame(config('identity.admin_primary'),UiColorSetting::currentPalette()['primary']);
        $setting=UiColorSetting::findOrFail(1); $setting->update(['primary'=>'#8F3B26']);
        $this->get('/')->assertSee('--portal-accent:#8F3B26',false);
    }
}
