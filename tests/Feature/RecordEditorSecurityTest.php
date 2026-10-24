<?php

namespace Tests\Feature;

use App\Livewire\User\UserPage;
use App\Models\Institution;
use App\Models\OperationView;
use App\Models\UiColorSetting;
use App\Models\User;
use App\Modules\Institutions\Actions\InstitutionManager;
use App\Modules\Institutions\UI\InstitutionPage;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

class RecordEditorSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function actor(): User
    {
        $actor = User::create(['name' => 'Owner', 'email' => uniqid().'@example.com', 'password' => 'test-password-123', 'institution_id' => $this->institution()->id]);
        $actor->assignRole('SuperAdmin');

        return $actor;
    }

    private function institution(): Institution
    {
        return Institution::create(['name' => 'Test', 'code' => uniqid(), 'type' => 'company']);
    }

    public function test_institution_route_renders(): void
    {
        $this->actingAs($this->actor())->get('/operations/institutions')->assertOk();
    }

    public function test_record_identity_cannot_be_changed_from_browser(): void
    {
        $editor = Livewire::actingAs($this->actor())->test(UserPage::class);
        $this->expectException(CannotUpdateLockedPropertyException::class);
        $editor->set('editingId', 999);
    }

    public function test_confirmation_cannot_be_forged_from_browser(): void
    {
        $editor = Livewire::actingAs($this->actor())->test(InstitutionPage::class);
        $this->expectException(CannotUpdateLockedPropertyException::class);
        $editor->set('recordDeletionId', $this->institution()->id);
    }

    public function test_other_users_view_cannot_be_selected(): void
    {
        $actor = $this->actor();
        $foreign = OperationView::create(['user_id' => $this->actor()->id, 'module' => 'users',
            'name' => 'Private', 'filter_value' => '', 'columns' => ['name'], 'sort_order' => 1]);
        $editor = Livewire::actingAs($actor)->test(UserPage::class);
        $this->expectException(ModelNotFoundException::class);
        $editor->call('selectView', $foreign->id);
    }

    public function test_stale_autosave_does_not_update_another_institution(): void
    {
        $first = $this->institution();
        $second = $this->institution();
        Livewire::actingAs($this->actor())->test(InstitutionPage::class)->call('edit', $first->id)
            ->set('name', 'Pending')->call('autosaveRecord', $second->id)->assertReturned('stale');
        $this->assertSame('Test', $first->fresh()->name);
        $this->assertSame('Test', $second->fresh()->name);
    }

    public function test_autosave_cannot_create_a_new_record_with_zero_id(): void
    {
        $actor = $this->actor();
        $count = Institution::count();
        Livewire::actingAs($actor)->test(InstitutionPage::class)->call('create')
            ->set('name', 'New')->set('code', 'NEW')->call('autosaveRecord', 0)->assertReturned('stale');
        $this->assertSame($count, Institution::count());
    }

    public function test_institution_with_users_cannot_be_deleted(): void
    {
        $actor = $this->actor();
        $institution = $this->institution();
        $actor->update(['institution_id' => $institution->id]);
        Livewire::actingAs($actor)->test(InstitutionPage::class)->call('edit', $institution->id)
            ->call('requestOpenRecordDelete')->call('deleteConfirmedRecord');
        $this->assertModelExists($institution);
    }

    public function test_phone_length_matches_database(): void
    {
        $actor = $this->actor();
        $institution = $this->institution();
        $this->expectException(ValidationException::class);
        app(InstitutionManager::class)->save($actor, ['name' => 'Changed', 'code' => $institution->code,
            'type' => 'company', 'contact_phone' => str_repeat('1', 41)], $institution);
    }

    public function test_palette_rejects_css_injection_on_render(): void
    {
        UiColorSetting::updateOrCreate(['id' => 1], ['primary' => '#ffffff; background:url(https://example.com)',
            'success' => '#123456', 'danger' => '</style><script>alert(1)</script>', 'secondary' => '#abcdef']);
        $palette = UiColorSetting::currentPalette();
        $this->assertSame(config('identity.operations.primary'), $palette['primary']);
        $this->assertSame(config('identity.operations.danger'), $palette['danger']);
        $this->assertSame('#123456', $palette['success']);
    }
}
