<?php

namespace App\Modules\Institutions\Tests;

use App\Models\Institution;
use App\Models\User;
use App\Modules\Institutions\UI\InstitutionPage;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Mechanisms\ComponentRegistry;
use Tests\Support\EnforcedCsrf;
use Tests\TestCase;

class InstitutionHttpTest extends TestCase
{
    use RefreshDatabase;

    public function test_module_page_can_be_resolved_by_its_snapshot_name(): void
    {
        $registry = app(ComponentRegistry::class);
        $this->assertSame('institutions-page', $registry->getName(InstitutionPage::class));
        $this->assertSame(InstitutionPage::class, $registry->getClass('institutions-page'));
    }

    public function test_institution_opens_and_autosaves_through_http_with_csrf_enforced(): void
    {
        $this->app->bind(ValidateCsrfToken::class, EnforcedCsrf::class);
        $this->seed(DatabaseSeeder::class);
        $actor = User::factory()->create();
        $actor->assignRole('SuperAdmin');
        $item = Institution::create(['name' => 'Piloto HTTP', 'code' => 'HTTP', 'type' => 'company']);
        $page = $this->actingAs($actor)->withSession(['_token' => 'valid-token'])
            ->get('/operations/institutions')->assertOk();
        $this->withCredentials()->withCookie(config('session.cookie'), $this->app['session']->getId());
        preg_match('/wire:snapshot="([^"]+)"/', $page->getContent(), $match);
        $snapshot = html_entity_decode($match[1], ENT_QUOTES);
        $opened = $this->update($snapshot, 'edit', [$item->id]);
        $data = json_decode($opened, true)['data'];
        $this->assertTrue($data['formOpen']);
        $this->assertSame($item->id, $data['editingId']);
        $this->update($opened, 'autosaveRecord', [$item->id, $data['editorSession']], ['contact_phone' => '0991234567']);
        $this->assertDatabaseHas('institutions', ['id' => $item->id, 'contact_phone' => '0991234567']);
        $this->postJson('/livewire/update', ['_token' => 'invalid-token', 'components' => []])->assertStatus(419);
    }

    private function update(string $snapshot, string $method, array $params, array $updates = []): string
    {
        return $this->postJson('/livewire/update', ['_token' => 'valid-token', 'components' => [
            ['snapshot' => $snapshot, 'updates' => $updates,
                'calls' => [['path' => '', 'method' => $method, 'params' => $params]]],
        ]])->assertOk()->json('components.0.snapshot');
    }
}
