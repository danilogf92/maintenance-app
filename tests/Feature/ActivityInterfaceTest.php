<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Activities\Models\Activity;
use App\Modules\Activities\UI\ActivityPage;
use App\Modules\Activities\Queries\{ActivityOptions, ActivityQuery};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Support\WorkflowFixture;
use Tests\TestCase;

class ActivityInterfaceTest extends TestCase
{
    use RefreshDatabase, WorkflowFixture;
    protected function setUp(): void { parent::setUp(); $this->bootWorkflow(); Storage::fake('local'); }

    private function photo(string $name = 'photo.png'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aP9sAAAAASUVORK5CYII='));
    }

    public function test_native_route_tabs_and_contextual_draft_do_not_insert(): void
    {
        $f = $this->workflow();
        $this->assertSame('activities-page', app(\Livewire\Mechanisms\ComponentRegistry::class)->getName(ActivityPage::class));
        $this->get('/operations/activities?record='.$f['activity']->id)->assertOk()->assertSee('Información de la actividad')->assertSee('Evidencias');
        $count = Activity::count();
        $this->get('/operations/activities?new_for_service='.$f['service']->id)->assertOk()->assertSee('Nuevo registro');
        $this->assertSame($count, Activity::count());
        $this->get('/operations/activities?new_for_service=invalid')->assertNotFound();
        $this->get('/operations/activities?record=bad')->assertNotFound();
    }

    public function test_create_autosaves_once_and_records_result_without_photos(): void
    {
        $f = $this->workflow(); $page = Livewire::test(ActivityPage::class)->call('create'); $session = $page->get('editorSession');
        $page->set('service_id', (string)$f['service']->id)->call('autosaveDraft', $session)->assertReturned('invalid')->assertHasErrors('description');
        $page->set('description', 'Hallazgo desde la ficha')->call('autosaveDraft', $session)->assertReturned('saved');
        $id = $page->get('editingId');
        $page->call('autosaveDraft', $session)->assertReturned('saved')->set('result', 'Conforme')
            ->call('autosaveRecord', $id, $session)->assertReturned('saved');
        $this->assertSame(2, Activity::count()); $this->assertSame(2, Activity::findOrFail($id)->lock_version);
        $this->assertSame([], Activity::findOrFail($id)->photos);
    }

    public function test_upload_reorder_and_confirmed_removal_preserve_private_originals(): void
    {
        $f = $this->workflow(); $page = Livewire::test(ActivityPage::class)->call('edit', $f['activity']->id); $session = $page->get('editorSession');
        $page->set('uploads', [$this->photo('a.png'), $this->photo('b.png')])->call('attachPhotos', $f['activity']->id, $session)->assertReturned('saved');
        $paths = $f['activity']->fresh()->photos; $this->assertCount(2, $paths);
        Storage::disk('local')->assertExists($paths);
        $page->call('movePhoto', 1, -1, $f['activity']->id, $session)->assertReturned('saved');
        $this->assertSame(array_reverse($paths), $f['activity']->fresh()->photos);
        $page->call('requestRemovePhoto', 0, $f['activity']->id, $session)->call('cancelPhotoRemoval');
        $this->assertCount(2, $f['activity']->fresh()->photos);
        $page->call('requestRemovePhoto', 0, $f['activity']->id, $session)->call('confirmRemovePhoto');
        $this->assertSame([$paths[0]], $f['activity']->fresh()->photos);
        Storage::disk('local')->assertExists($paths[1]);
        $this->get(route('workflow.photo', [$f['activity']->id, 0]))->assertOk()->assertHeader('Cache-Control', 'no-store, private');
        $this->get(route('workflow.photo', [$f['activity']->id, 1]))->assertNotFound();
        $page->call('closeForm')->assertSet('photos', [])->assertSet('formOpen', false)
            ->call('edit', $f['activity']->id)->assertSet('photos', [$paths[0]]);
    }

    public function test_evidence_requires_saved_record_correct_session_and_active_selection(): void
    {
        $f = $this->workflow(); $other = $this->workflow();
        $page = Livewire::test(ActivityPage::class)->call('create')->set('uploads', [$this->photo()]);
        $page->call('attachPhotos', $f['activity']->id, $page->get('editorSession'))->assertReturned('stale');
        $page->call('edit', $f['activity']->id); $session = $page->get('editorSession');
        $page->call('requestRemovePhoto', 0, $f['activity']->id, $session)->call('edit', $other['activity']->id)
            ->set('uploads', [$this->photo()])->call('attachPhotos', $f['activity']->id, $session)->assertReturned('stale');
        $this->assertSame([], $f['activity']->fresh()->photos); $this->assertSame([], $other['activity']->fresh()->photos);
    }

    public function test_rejects_fake_images_oversized_files_and_fifth_photo(): void
    {
        $f = $this->workflow(); $page = Livewire::test(ActivityPage::class)->call('edit', $f['activity']->id); $session = $page->get('editorSession');
        $page->set('uploads', [UploadedFile::fake()->create('fake.jpg', 10, 'text/plain')])
            ->call('attachPhotos', $f['activity']->id, $session)->assertHasErrors('uploads.0');
        $page->set('uploads', [UploadedFile::fake()->createWithContent('big.png', $this->photo()->getContent().str_repeat('x', 1024 * 1024))])
            ->call('attachPhotos', $f['activity']->id, $session)->assertHasErrors('uploads.0');
        $page->set('uploads', [$this->photo('1.png'), $this->photo('2.png'), $this->photo('3.png'), $this->photo('4.png')])
            ->call('attachPhotos', $f['activity']->id, $session)->assertReturned('saved');
        $page->set('uploads', [$this->photo('5.png')])->call('attachPhotos', $f['activity']->id, $session)->assertReturned('invalid')->assertHasErrors('uploads');
        $this->assertCount(4, $f['activity']->fresh()->photos);
    }

    public function test_foreign_activity_and_private_photo_are_inaccessible(): void
    {
        $tech = User::factory()->create(); $tech->assignRole('Técnico'); $own = $this->workflow($tech); $foreign = $this->workflow();
        $path = $this->evidence(); $foreign['activity']->update(['photos' => [$path]]); $this->actingAs($tech);
        $this->get('/operations/activities?record='.$foreign['activity']->id)->assertNotFound();
        $this->get('/operations/activities?new_for_service='.$foreign['service']->id)->assertNotFound();
        $this->get(route('workflow.photo', [$foreign['activity']->id, 0]))->assertNotFound();
        $this->assertSame([], ActivityOptions::context((string)$foreign['service']->id));
        $options = ActivityOptions::services('no-match', (string)$own['service']->id);
        $this->assertArrayHasKey($own['service']->id, $options); $this->assertArrayNotHasKey($foreign['service']->id, $options);
    }

    public function test_read_only_user_can_preview_but_cannot_change_photos_or_save(): void
    {
        $f = $this->workflow(); $f['activity']->update(['photos' => [$this->evidence()]]);
        $reader = User::factory()->create(); $reader->assignRole('CEO'); $this->actingAs($reader);
        $page = Livewire::test(ActivityPage::class)->call('edit', $f['activity']->id)->assertSee('Solo lectura')->assertDontSee('Añadir fotografías');
        $this->get(route('workflow.photo', [$f['activity']->id, 0]))->assertOk();
        $page->call('movePhoto', 0, 1, $f['activity']->id, $page->get('editorSession'))->assertForbidden();
        Livewire::test(ActivityPage::class)->call('create')->assertForbidden();
    }

    public function test_photos_are_locked_and_cannot_be_injected_from_another_record(): void
    {
        $f = $this->workflow();
        $this->expectException(\Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException::class);
        Livewire::test(ActivityPage::class)->call('edit', $f['activity']->id)->set('photos', ['service-photos/999/private.png']);
    }

    public function test_filters_cache_and_concurrency_apply_to_evidence(): void
    {
        $f = $this->workflow(); $query = app(ActivityQuery::class);
        $this->assertCount(1, $query->records('', 'with_result')); $this->assertCount(0, $query->records('', 'with_photos'));
        $page = Livewire::test(ActivityPage::class)->call('edit', $f['activity']->id); $session = $page->get('editorSession');
        $page->set('uploads', [$this->photo()])->call('attachPhotos', $f['activity']->id, $session)->assertReturned('saved');
        $this->assertSame(1, $query->records('', 'with_photos')->first()->photo_count);
        $f['activity']->fresh()->update(['lock_version' => $f['activity']->fresh()->lock_version + 1, 'result' => 'Cambio externo']);
        $page->call('requestRemovePhoto', 0, $f['activity']->id, $session)->call('confirmRemovePhoto')->assertHasErrors('lock_version');
        $this->assertCount(1, $f['activity']->fresh()->photos); $this->assertSame('Cambio externo', $f['activity']->fresh()->result);
    }
}
