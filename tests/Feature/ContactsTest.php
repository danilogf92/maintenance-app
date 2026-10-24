<?php
namespace Tests\Feature;
use App\Modules\Contacts\Actions\{SaveContact, SaveContactLink, DeactivateContactLink};
use App\Modules\Contacts\Models\{Contact, ContactLink};
use App\Modules\Contacts\UI\{ContactPage, ContactLinks};
use App\Modules\Clients\UI\ClientPage;
use App\Models\{User, Institution};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\Support\WorkflowFixture;
use Tests\TestCase;
class ContactsTest extends TestCase
{
    use RefreshDatabase, WorkflowFixture;
    protected function setUp(): void { parent::setUp(); $this->bootWorkflow(); }
    private function contact(string $name = 'Ana Pérez'): Contact
    {
        return app(SaveContact::class)->execute(['name' => $name, 'active' => true, 'lock_version' => 0]);
    }
    private function link(Contact $contact, string $type, int $id, bool $primary = false): ContactLink
    {
        return app(SaveContactLink::class)->execute($contact->id, $type, $id, ['category' => 'technical', 'is_primary' => $primary]);
    }
    public function test_person_has_multiple_real_links_without_creating_accounts(): void
    {
        $f = $this->workflow(); $users = User::count(); $contact = $this->contact();
        $this->link($contact, 'client', $f['client']->id, true);
        $this->link($contact, 'institution', $f['institution']->id);
        $this->assertSame(2, $contact->links()->count()); $this->assertSame($users, User::count());
        $this->get(route('operations.contacts'))->assertOk()->assertSee('Ana Pérez');
        Livewire::test(ContactPage::class)->call('edit', $contact->id)->call('loadEditorRelation', 'contacts')->assertSee('Vínculos de contactos');
        Livewire::test(ClientPage::class)->call('edit', $f['client']->id)->call('loadEditorRelation', 'contacts')->assertSee('Ana Pérez');
    }
    public function test_primary_is_unique_and_stale_update_is_rejected(): void
    {
        $f = $this->workflow(); $a = $this->link($this->contact(), 'client', $f['client']->id, true);
        $b = $this->link($this->contact('Luis'), 'client', $f['client']->id, true);
        $this->assertFalse($a->fresh()->is_primary); $this->assertTrue($b->is_primary);
        $this->assertNull($a->fresh()->primary_key);
        $this->expectException(ValidationException::class);
        app(SaveContactLink::class)->execute($a->contact_id, 'client', $f['client']->id, ['category' => 'reception', 'is_primary' => true], $a->lock_version);
    }
    public function test_deactivation_preserves_history_and_reactivation_is_explicit(): void
    {
        $f = $this->workflow(); $contact = $this->contact(); $link = $this->link($contact, 'institution', $f['institution']->id, true);
        app(SaveContact::class)->execute(['name' => $contact->name, 'active' => false, 'lock_version' => $contact->lock_version], $contact->id);
        $this->assertTrue($link->fresh()->active); $this->assertFalse($link->fresh()->is_primary);
        app(DeactivateContactLink::class)->execute($link->id, $link->fresh()->lock_version);
        $this->assertFalse($link->fresh()->active); $this->assertSame(1, $contact->links()->count());
        $this->expectException(ValidationException::class);
        app(SaveContactLink::class)->execute($contact->id, 'institution', $f['institution']->id, ['category' => 'other', 'is_primary' => false], $link->fresh()->lock_version);
    }
    public function test_link_actions_reject_a_foreign_context_and_allow_cancel(): void
    {
        $f = $this->workflow(); $a = $this->contact(); $b = $this->contact('Luis'); $link = $this->link($b, 'client', $f['client']->id);
        try { Livewire::test(ContactLinks::class, ['contactId' => $a->id])->call('editLink', $link->id); $this->fail(); }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException) { $this->assertTrue(true); }
        Livewire::test(ContactLinks::class, ['contactId' => $b->id])->call('requestDeactivate', $link->id)->call('cancelDeactivate')->assertSet('pendingDeactivate', null);
        $this->assertTrue($link->fresh()->active);
    }
    public function test_draft_autosave_is_idempotent_and_validates_name(): void
    {
        $page = Livewire::test(ContactPage::class)->call('create'); $session = $page->get('editorSession');
        $page->call('autosaveDraft', $session)->assertReturned('invalid')->assertHasErrors('name');
        $page->set('name', 'Nueva persona')->call('autosaveDraft', $session)->assertReturned('saved')->call('autosaveDraft', $session);
        $this->assertSame(1, Contact::count());
    }
    public function test_viewer_cannot_write_or_see_unpermitted_organization_data(): void
    {
        $f = $this->workflow(); $contact = $this->contact(); $this->link($contact, 'institution', $f['institution']->id);
        $user = User::factory()->create(['is_active' => true]); $user->givePermissionTo(['workflow.access', 'contacts.view']); $this->actingAs($user);
        $this->get(route('operations.contacts'))->assertOk();
        Livewire::test(ContactLinks::class, ['contactId' => $contact->id])->assertDontSee($f['institution']->name)->call('beginLink')->assertForbidden();
        Livewire::test(ContactPage::class)->call('create')->assertForbidden();
        Livewire::test(ContactLinks::class, ['institutionId' => $f['institution']->id])->assertForbidden();
    }
    public function test_duplicate_link_is_rejected_and_contact_creation_rolls_back_for_invalid_parent(): void
    {
        $f = $this->workflow(); $contact = $this->contact(); $this->link($contact, 'client', $f['client']->id);
        try { $this->link($contact, 'client', $f['client']->id); $this->fail(); } catch (ValidationException) {}
        $this->assertSame(1, ContactLink::count());
        try { app(SaveContact::class)->execute(['name' => 'Invalid', 'active' => true, 'lock_version' => 0], null, ['type' => 'invalid', 'id' => 1]); } catch (\Symfony\Component\HttpKernel\Exception\HttpException) {}
        $this->assertSame(1, Contact::count());
    }
    public function test_parent_shortcut_creates_person_and_link_together(): void
    {
        $f = $this->workflow();
        $page = Livewire::withQueryParams(['new_for_client' => $f['client']->id])->test(ContactPage::class);
        $page->set('name', 'Persona del cliente')->call('autosaveDraft', $page->get('editorSession'))->assertReturned('saved');
        $this->assertSame($f['client']->id, ContactLink::sole()->client_id);
        $this->assertSame($page->get('editingId'), ContactLink::sole()->contact_id);
    }
    public function test_demo_is_idempotent_and_keeps_user_edits(): void
    {
        $this->seed(\Database\Seeders\WorkflowDemoSeeder::class);
        $users = User::count(); $this->seed(\Database\Seeders\ContactDemoSeeder::class);
        $person = Contact::where('code', 'DEMO-CTO-1')->firstOrFail();
        $person->update(['name' => 'Nombre editado']);
        $this->seed(\Database\Seeders\ContactDemoSeeder::class);
        $this->assertSame(2, Contact::count()); $this->assertSame(3, ContactLink::count());
        $this->assertSame('Nombre editado', $person->fresh()->name); $this->assertSame($users, User::count());
    }

}
