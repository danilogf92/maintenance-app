<?php

namespace Tests\Feature;

use App\Livewire\Institution\InstitutionPage;
use App\Livewire\User\UserPage;
use App\Models\Institution;
use App\Models\OperationView;
use App\Models\User;
use App\Modules\Institution\InstitutionTableColumns;
use App\Modules\Shared\RecordTable;
use App\Modules\User\UserTableColumns;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class RecordTableToolsTest extends TestCase
{
    use RefreshDatabase;

    private function actor(): User
    {
        $this->seed(DatabaseSeeder::class);
        $institution = Institution::create(['name' => 'Base', 'code' => 'TABLE-BASE']);
        $user = User::create(['name' => 'Actor', 'email' => 'table@example.test',
            'password' => 'test-password-123', 'institution_id' => $institution->id]);
        $user->assignRole('SuperAdmin');

        return $user;
    }

    public function test_column_tools_persist_per_owned_view_and_survive_reentry(): void
    {
        $actor = $this->actor();
        $editor = Livewire::actingAs($actor)->test(InstitutionPage::class);
        $viewId = $editor->get('currentViewId');
        $editor->call('filterColumn', 'city', 'Quito')->call('sortColumn', 'name', 'desc')
            ->call('groupColumn', 'type')->assertSet('tableGroup', 'type');
        $settings = OperationView::findOrFail($viewId)->table_settings;
        $this->assertSame(['city' => 'Quito'], $settings['filters']);
        $this->assertSame('desc', $settings['direction']);
        $editor->call('addView')->assertSet('tableFilters', [])->assertSet('tableGroup', '')
            ->call('selectView', $viewId)->assertSet('tableFilters.city', 'Quito');
        Livewire::test(InstitutionPage::class)->assertSet('tableGroup', 'type')->assertSet('tableSort', 'name');
        $editor->call('clearTableTools')->assertSet('tableFilters', [])->assertSet('tableSort', '')
            ->assertSet('tableGroup', '');
    }

    public function test_unknown_columns_directions_and_disabled_actions_are_rejected(): void
    {
        $actor = $this->actor();
        Livewire::actingAs($actor)->test(InstitutionPage::class)->call('sortColumn', 'name; DROP TABLE users', 'asc')->assertStatus(422);
        Livewire::test(InstitutionPage::class)->call('sortColumn', 'name', 'sideways')->assertStatus(422);
        Livewire::test(InstitutionPage::class)->call('groupColumn', 'contact_email')->assertStatus(422);
        Livewire::test(InstitutionPage::class)->call('filterColumn', 'password', 'x')->assertStatus(422);
        $columns = InstitutionTableColumns::definitions();
        $columns['city']['filterable'] = false;
        $settings = app(RecordTable::class)->normalize(['filters' => ['city' => 'Quito']], $columns);
        $this->assertSame([], $settings['filters']);
    }

    public function test_filter_sort_and_group_transform_the_snapshot_without_queries(): void
    {
        $records = collect([
            new Institution(['id' => 10, 'name' => 'Diez', 'city' => 'Quito', 'type' => 'hospital']),
            new Institution(['id' => 2, 'name' => 'Dos', 'city' => 'Quito', 'type' => 'company']),
            new Institution(['id' => 3, 'name' => 'Tres', 'city' => 'Loja', 'type' => 'hospital']),
        ]);
        // IDs are guarded on the model; force them only for this in-memory fixture.
        foreach ($records as $index => $record) {
            $record->id = [10, 2, 3][$index];
        }
        $service = app(RecordTable::class);
        DB::enableQueryLog();
        DB::flushQueryLog();
        $result = $service->prepare($records, InstitutionTableColumns::definitions(),
            ['filters' => ['city' => 'Quito'], 'sort' => 'id', 'direction' => 'desc']);
        $this->assertSame([10, 2], $result['items']->pluck('id')->all());
        $grouped = $service->prepare($records, InstitutionTableColumns::definitions(),
            ['group' => 'type', 'sort' => 'id', 'direction' => 'desc']);
        $this->assertSame(['Empresa', 'Hospital'], array_column($grouped['tableGroups'], 'label'));
        $this->assertSame([2, 10, 3], $grouped['items']->pluck('id')->all());
        $this->assertCount(2, $grouped['tableGroups'][1]['items']);
        $descendingGroups = $service->prepare($records, InstitutionTableColumns::definitions(),
            ['group' => 'type', 'sort' => 'type', 'direction' => 'desc']);
        $this->assertSame(['Hospital', 'Empresa'], array_column($descendingGroups['tableGroups'], 'label'));
        $this->assertCount(0, DB::getQueryLog());
        DB::disableQueryLog();
    }

    public function test_relation_and_boolean_columns_filter_and_group_users(): void
    {
        $actor = $this->actor();
        $actor->load('institution', 'roles');
        $other = new User(['name' => 'Otro', 'is_active' => false]);
        $other->id = 999;
        $other->setRelation('institution', $actor->institution)->setRelation('roles', collect());
        $service = app(RecordTable::class);
        $result = $service->prepare(collect([$actor, $other]), UserTableColumns::definitions(),
            ['filters' => ['status' => '0', 'institution' => 'Base']]);
        $this->assertSame([999], $result['items']->pluck('id')->all());
        $roles = $service->prepare(collect([$actor, $other]), UserTableColumns::definitions(), ['group' => 'roles']);
        $this->assertSame(['Sin dato', 'SuperAdmin'], array_column($roles['tableGroups'], 'label'));
        Livewire::actingAs($actor)->test(UserPage::class)->call('groupColumn', 'status')->assertSee('Estado: Activo');
    }

    public function test_other_users_view_settings_are_not_accessible(): void
    {
        $actor = $this->actor();
        $foreign = OperationView::create(['user_id' => User::factory()->create()->id,
            'module' => 'institutions', 'name' => 'Ajena', 'columns' => ['name'],
            'table_settings' => ['group' => 'type']]);
        $this->assertSame(['group' => 'type'], $foreign->fresh()->table_settings);
        $this->expectException(ModelNotFoundException::class);
        Livewire::actingAs($actor)->test(InstitutionPage::class)->call('selectView', $foreign->id);
    }
}
