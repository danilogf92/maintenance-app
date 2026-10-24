<?php

namespace App\Modules\Clients\UI;

use App\Modules\Workflow\UI\TabbedRecordPage;
use App\Modules\Clients\Configuration\{Columns, TableColumns};
use App\Modules\Clients\Queries\ClientQuery;
use App\Modules\Clients\Actions\SaveClient;
use App\Modules\Workflow\Configuration\Permissions;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;

class ClientPage extends TabbedRecordPage
{
    public string $name = '';
    public string $tax_id = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';
    public string $city = '';
    public bool $active = true;
    #[Locked] public string $activeFilter = '';

    protected function viewModule(): string { return 'clients'; }
    protected function relatedModules(): array { return ['equipment', 'contacts']; }
    protected function relatedModule(): string { return 'equipment'; }
    protected function defaultViews(): array { return Columns::views(); }
    protected function applyViewFilter(string $value): void { $this->activeFilter = $value; }

    protected function viewFields(): array
    {
        $columns = Columns::columns();
        if (! Permissions::allows('equipment')) { unset($columns['equipment']); }
        return $columns;
    }

    protected function tableColumns(): array { return array_intersect_key(TableColumns::definitions(), $this->viewFields()); }

    public function resetColumns(): void
    {
        $this->visibleColumns = ['name', 'code', 'tax_id', 'status', 'city'];
        $this->saveViewSettings();
    }

    protected function persistRecord(): Model
    {
        return app(SaveClient::class)->execute([...$this->recordData(), 'lock_version' => $this->lockVersion], $this->editingId);
    }

    public function render()
    {
        $items = app(ClientQuery::class)->records($this->search, $this->activeFilter);
        return view('clients::page', array_merge(compact('items'), $this->operationViewsData(), $this->prepareTable($items), ClientEditor::configuration(),
            ['editorRelations' => ['equipment' => ['allowed' => Permissions::allows('equipment'), 'component' => 'client-related-equipment', 'key' => 'clientId'],
                'contacts' => ['allowed' => Permissions::allows('contacts'), 'component' => 'contact-links', 'key' => 'clientId']],
            'canManage' => Permissions::allows('clients', 'manage'), 'canViewEquipment' => Permissions::allows('equipment')]))->layout('components.layouts.app');
    }
}
