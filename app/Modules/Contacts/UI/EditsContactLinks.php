<?php
namespace App\Modules\Contacts\UI;
use App\Modules\Contacts\Configuration\Permissions;
use App\Modules\Contacts\Actions\SaveContactLink;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Locked;
trait EditsContactLinks
{
    #[Locked] public ?int $editLinkId = null, $linkVersion = null;
    public bool $linkFormOpen = false, $isPrimary = false;
    public string $selectionType = 'client', $selectionId = '', $category = 'other';
    public function beginLink(): void
    {
        Permissions::authorize('manage'); $this->authorizeContext(); $this->cancelLink(); $this->linkFormOpen = true; $this->notice = '';
        if (!\App\Modules\Contacts\Queries\ContactParents::allowed('client')) { $this->selectionType = 'institution'; }
    }
    public function cancelLink(): void
    {
        $this->editLinkId = null; $this->linkVersion = null; $this->linkFormOpen = false;
        $this->selectionId = ''; $this->selectionSearch = ''; $this->category = 'other'; $this->isPrimary = false; $this->resetValidation();
    }
    public function editLink(int $id): void
    {
        Permissions::authorize('manage'); $link = $this->scopedLink($id); $this->cancelLink();
        $this->editLinkId = $id; $this->linkVersion = $link->lock_version; $this->linkFormOpen = true; $this->notice = '';
        $this->selectionType = $link->parentType();
        $this->selectionId = (string) ($this->contactId ? $link->parentId() : $link->contact_id);
        $this->category = $link->category; $this->isPrimary = $link->is_primary;
    }
    public function saveLink(): void
    {
        Permissions::authorize('manage'); $this->authorizeContext();
        abort_unless($this->linkFormOpen, 422);
        if ($this->editLinkId) {
            $link = $this->scopedLink($this->editLinkId);
            $contact = $link->contact_id; $type = $link->parentType(); $parent = $link->parentId();
        } else {
            Validator::make(['selectionId' => $this->selectionId], ['selectionId' => 'required|integer|min:1'])->validate();
            $contact = $this->contactId ?? (int) $this->selectionId;
            $type = $this->contactId ? $this->selectionType : $this->parentType;
            $parent = $this->contactId ? (int) $this->selectionId : $this->parentId;
        }
        app(SaveContactLink::class)->execute($contact, $type, $parent, ['category' => $this->category, 'is_primary' => $this->isPrimary], $this->linkVersion);
        $this->cancelLink(); $this->notice = 'Vínculo guardado.'; $this->resetPage();
    }
}
