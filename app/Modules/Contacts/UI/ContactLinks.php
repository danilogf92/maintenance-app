<?php
namespace App\Modules\Contacts\UI;
use App\Modules\Contacts\Configuration\{Permissions, Fields};
use App\Modules\Contacts\Queries\{ContactParents, ContactLinkQuery};
use App\Modules\Contacts\Models\Contact;
use App\Modules\Contacts\Actions\{SaveContactLink, DeactivateContactLink};
use Livewire\{Component, WithPagination};
use Livewire\Attributes\Locked;
class ContactLinks extends Component
{
    use WithPagination, EditsContactLinks;
    #[Locked] public ?int $contactId = null, $parentId = null;
    #[Locked] public ?string $parentType = null;
    #[Locked] public ?int $pendingDeactivate = null, $pendingVersion = null;
    public string $search = '', $selectionSearch = '', $notice = '';
    public bool $includeInactive = false;
    public function boot(): void { Permissions::authorize(); }
    public function mount(?int $contactId = null, ?int $clientId = null, ?int $institutionId = null): void
    {
        abort_unless(count(array_filter([$contactId, $clientId, $institutionId])) === 1, 422);
        $this->contactId = $contactId;
        $this->parentId = $clientId ?? $institutionId;
        $this->parentType = $clientId ? 'client' : ($institutionId ? 'institution' : null);
        $this->authorizeContext();
    }
    protected function authorizeContext(): void
    {
        Permissions::authorize();
        if ($this->contactId) { Contact::findOrFail($this->contactId); }
        else { ContactParents::find($this->parentType, $this->parentId); }
    }
    protected function scopedLink(int $id)
    {
        $this->authorizeContext();
        return ContactLinkQuery::scoped($this->contactId, $this->parentType, $this->parentId)->findOrFail($id);
    }
    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedIncludeInactive(): void { $this->resetPage(); }
    public function updatedSelectionType(): void { $this->selectionId = ''; $this->selectionSearch = ''; }
    public function requestDeactivate(int $id): void
    {
        Permissions::authorize('manage'); $link = $this->scopedLink($id);
        $this->pendingDeactivate = $id; $this->pendingVersion = $link->lock_version;
    }
    public function cancelDeactivate(): void { $this->pendingDeactivate = null; $this->pendingVersion = null; }
    public function deactivate(): void
    {
        Permissions::authorize('manage'); abort_unless($this->pendingDeactivate, 422);
        $link = $this->scopedLink($this->pendingDeactivate);
        app(DeactivateContactLink::class)->execute($link->id, $this->pendingVersion);
        $this->cancelDeactivate(); $this->cancelLink(); $this->notice = 'Vínculo desactivado. Se conserva el historial.';
    }
    public function render()
    {
        $this->authorizeContext();
        $items = ContactLinkQuery::listing($this->contactId, $this->parentType, $this->parentId, $this->search, $this->includeInactive);
        $options = $this->contactId ? ContactParents::options($this->selectionType, $this->selectionSearch, $this->selectionId)
            : ContactLinkQuery::contacts($this->selectionSearch, $this->selectionId);
        return view('contacts::links', ['items' => $items, 'options' => $options, 'categories' => Fields::categories(),
            'canManage' => Permissions::allows('manage'), 'parentTypes' => array_filter(['client' => 'Cliente', 'institution' => 'Institución'], fn ($label, $type) => ContactParents::allowed($type), ARRAY_FILTER_USE_BOTH)]);
    }
}
