<?php

namespace Tests\Feature;

use App\Livewire\User\UserPage;
use App\Models\Institution;
use App\Models\OperationView;
use App\Models\OperationViewPreference;
use App\Models\User;
use App\Modules\Institutions\UI\InstitutionPage;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FavoriteViewTest extends TestCase
{
    use RefreshDatabase;

    private function actor(): User
    {
        $institution = Institution::firstOrCreate(['code' => 'FAV'], ['name' => 'Institution']);
        $actor = User::create(['name' => 'Owner', 'email' => uniqid().'@example.com',
            'password' => 'password-for-testing', 'institution_id' => $institution->id]);
        $actor->assignRole('SuperAdmin');

        return $actor;
    }

    protected function setUp(): void
    {
        parent::setUp();
        config(['page-cache.store' => 'array']);
        $this->seed(DatabaseSeeder::class);
    }

    public function test_only_one_favorite_and_entry_opens_it(): void
    {
        $actor = $this->actor();
        $editor = Livewire::actingAs($actor)->test(UserPage::class);
        $views = OperationView::where('user_id', $actor->id)->orderBy('sort_order')->get();
        $editor->call('chooseFavorite', $views[1]->id)->call('chooseFavorite', $views[2]->id);
        $this->assertSame(1, OperationViewPreference::where('user_id', $actor->id)->where('module', 'users')->count());
        Livewire::actingAs($actor)->test(UserPage::class)->assertSet('currentViewId', $views[2]->id)
            ->assertSet('favoriteViewId', $views[2]->id)->assertSet('activeFilter', '0');
    }

    public function test_favorites_are_independent_for_each_user_and_module(): void
    {
        $actor = $this->actor();
        Livewire::actingAs($actor)->test(UserPage::class);
        Livewire::actingAs($actor)->test(InstitutionPage::class);
        $this->assertSame(2, OperationViewPreference::where('user_id', $actor->id)->count());
        $other = $this->actor();
        Livewire::actingAs($other)->test(UserPage::class);
        $this->assertSame(1, OperationViewPreference::where('user_id', $other->id)->count());
    }

    public function test_foreign_view_cannot_be_favorited(): void
    {
        $actor = $this->actor();
        Livewire::actingAs($actor)->test(UserPage::class);
        $foreign = Livewire::actingAs($this->actor())->test(UserPage::class)->get('currentViewId');
        $editor = Livewire::actingAs($actor)->test(UserPage::class);
        $this->expectException(ModelNotFoundException::class);
        $editor->call('chooseFavorite', $foreign);
    }

    public function test_deleting_favorite_falls_back_to_first_existing_view(): void
    {
        $actor = $this->actor();
        $editor = Livewire::actingAs($actor)->test(UserPage::class);
        $first = $editor->get('currentViewId');
        $editor->call('addView');
        $id = $editor->get('currentViewId');
        $editor->call('chooseFavorite', $id)->call('deleteView')->assertSet('currentViewId', $first)
            ->assertSet('favoriteViewId', $first);
        $this->assertDatabaseMissing('operation_views', ['id' => $id]);
    }

    public function test_settings_show_svg_controls_and_favorite_button(): void
    {
        Livewire::actingAs($this->actor())->test(UserPage::class)->call('openViewSettings')
            ->assertSee('Mostrar todas')->assertSee('Vista favorita')->assertSee('Subir Usuario', false)->assertSee('ops-column-table', false)
            ->assertSee('ops-column-danger', false)->assertSee('Restablecer columnas');
    }
}
