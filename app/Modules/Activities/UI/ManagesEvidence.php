<?php

namespace App\Modules\Activities\UI;

use App\Modules\Workflow\Configuration\Permissions;
use App\Modules\Workflow\Queries\VisibleRecords;
use Livewire\Attributes\Locked;

trait ManagesEvidence
{
    public array $uploads = [];
    #[Locked] public string $photoNotice = '';
    #[Locked] public array $pendingPhotoRemoval = [];

    protected function resetEvidence(): void { $this->uploads = []; $this->photoNotice = ''; $this->pendingPhotoRemoval = []; }

    private function evidenceContext(int $id, string $session): bool
    {
        Permissions::authorize('activities', 'manage');
        if (!$this->formOpen || $this->editingId !== $id || $session !== $this->editorSession || $session === '') { return false; }
        VisibleRecords::query('activities')->findOrFail($id);
        return true;
    }

    public function attachPhotos(int $id, string $session): string
    {
        if (!$this->evidenceContext($id, $session)) { return 'stale'; }
        $this->validate(['uploads' => 'required|array|min:1|max:4', 'uploads.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:1024']);
        if (count($this->photos) + count($this->uploads) > 4) { $this->addError('uploads', 'Máximo cuatro fotografías por actividad.'); return 'invalid'; }
        $paths = array_map(fn ($upload) => $upload->store('service-photos/'.auth()->id(), 'local'), $this->uploads);
        $this->uploads = [];
        return $this->saveEvidence([...$this->photos, ...$paths], $id, $session);
    }

    public function movePhoto(int $index, int $direction, int $id, string $session): string
    {
        if (!$this->evidenceContext($id, $session)) { return 'stale'; }
        abort_unless(in_array($direction, [-1, 1], true), 422);
        $next = $index + $direction; $photos = $this->photos;
        if (!isset($photos[$index], $photos[$next])) { return 'stale'; }
        [$photos[$index], $photos[$next]] = [$photos[$next], $photos[$index]];
        return $this->saveEvidence($photos, $id, $session);
    }

    public function requestRemovePhoto(int $index, int $id, string $session): void
    {
        if ($this->evidenceContext($id, $session) && isset($this->photos[$index])) {
            $this->pendingPhotoRemoval = compact('index', 'id', 'session');
        }
    }

    public function cancelPhotoRemoval(): void { $this->pendingPhotoRemoval = []; }

    public function confirmRemovePhoto(): void
    {
        $pending = $this->pendingPhotoRemoval; $this->pendingPhotoRemoval = [];
        if (!$pending || !$this->evidenceContext($pending['id'], $pending['session'])) { return; }
        $photos = $this->photos; unset($photos[$pending['index']]);
        $this->saveEvidence(array_values($photos), $pending['id'], $pending['session']);
    }

    private function saveEvidence(array $photos, int $id, string $session): string
    {
        $previous = $this->photos; $this->photos = $photos; $this->recordDirty = true;
        $state = $this->autosaveRecord($id, $session);
        if ($state !== 'saved') { $this->photos = $previous; }
        $this->photoNotice = $state === 'saved' ? 'Fotografías guardadas.' : 'No se guardaron las fotografías. Revisa los campos o recarga si hay un conflicto.';
        return $state;
    }
}
