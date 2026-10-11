<?php

namespace App\Livewire\Shared;

use App\Modules\Shared\RecordTable;
use Illuminate\Support\Collection;
use Livewire\Attributes\Locked;

trait HasTableTools
{
    #[Locked]
    public array $tableFilters = [];

    #[Locked]
    public string $tableSort = '';

    #[Locked]
    public string $tableDirection = 'asc';

    #[Locked]
    public string $tableGroup = '';

    abstract protected function tableColumns(): array;

    protected function loadTableSettings(array $settings): void
    {
        $settings = app(RecordTable::class)->normalize($settings, $this->tableColumns());
        $this->tableFilters = $settings['filters'];
        $this->tableSort = $settings['sort'];
        $this->tableDirection = $settings['direction'];
        $this->tableGroup = $settings['group'];
    }

    protected function tableSettings(): array
    {
        return ['filters' => $this->tableFilters, 'sort' => $this->tableSort,
            'direction' => $this->tableDirection, 'group' => $this->tableGroup];
    }

    protected function persistTableSettings(): void
    {
        $this->viewQuery()->findOrFail($this->currentViewId)->update(['table_settings' => $this->tableSettings()]);
    }

    protected function assertColumnAction(string $column, string $action): void
    {
        abort_unless($this->tableColumns()[$column][$action] ?? false, 422, 'Acción no permitida en esta columna.');
    }

    public function filterColumn(string $column, string $value): void
    {
        $this->assertColumnAction($column, 'filterable');
        abort_if(mb_strlen($value) > 200, 422);
        if ($value === '') {
            unset($this->tableFilters[$column]);
        } else {
            $this->tableFilters[$column] = $value;
        }
        $this->persistTableSettings();
    }

    public function sortColumn(string $column, string $direction): void
    {
        $this->assertColumnAction($column, 'sortable');
        abort_unless(in_array($direction, ['asc', 'desc'], true), 422);
        $this->tableSort = $column;
        $this->tableDirection = $direction;
        $this->persistTableSettings();
    }

    public function groupColumn(string $column): void
    {
        $this->assertColumnAction($column, 'groupable');
        $this->tableGroup = $this->tableGroup === $column ? '' : $column;
        $this->persistTableSettings();
    }

    public function clearTableTools(): void
    {
        $this->loadTableSettings([]);
        $this->persistTableSettings();
    }

    protected function prepareTable(Collection $records): array
    {
        return app(RecordTable::class)->prepare($records, $this->tableColumns(), $this->tableSettings());
    }
}
