<?php
namespace App\Modules\Catalogs\UI;

use App\Modules\Catalogs\Actions\SaveManufacturerLogo;
use App\Modules\Workflow\Configuration\Permissions;
use Livewire\Attributes\Locked;

trait ManagesManufacturerLogo
{
    public $logoUpload;
    #[Locked] public array $pendingLogoRemoval = [];
    #[Locked] public string $logoNotice = '';

    protected function ownsLogoAction(int $id, string $session): bool
    {
        abort_unless($this->catalog === 'manufacturers', 404); Permissions::authorize('manufacturers', 'manage');
        return $this->formOpen && $this->editingId === $id && $this->editorSession === $session && $session !== '';
    }
    public function attachLogo(int $id, string $session): void
    {
        if (!$this->ownsLogoAction($id, $session) || !$this->finishCurrentRecord()) { return; }
        $this->validate(['logoUpload' => 'required|image|mimes:png,jpg,jpeg,webp|max:1024']);
        $record = app(SaveManufacturerLogo::class)->execute($id, $this->lockVersion, $this->logoUpload);
        $this->fillRecord($record); $this->logoNotice = 'Logo guardado. Actualiza la revisión del informe para incorporarlo.';
    }
    public function requestRemoveLogo(int $id, string $session): void
    {
        if ($this->ownsLogoAction($id, $session)) { $this->pendingLogoRemoval = compact('id', 'session'); }
    }
    public function cancelLogoRemoval(): void { $this->pendingLogoRemoval = []; }
    public function confirmRemoveLogo(): void
    {
        $pending = $this->pendingLogoRemoval; $this->pendingLogoRemoval = [];
        if (!$pending || !$this->ownsLogoAction($pending['id'], $pending['session']) || !$this->finishCurrentRecord()) { return; }
        $this->fillRecord(app(SaveManufacturerLogo::class)->execute($pending['id'], $this->lockVersion));
        $this->logoNotice = 'Logo retirado del fabricante. Las revisiones anteriores conservan su copia.';
    }
}
