<?php

namespace App\Modules\ReportTemplates\UI\Concerns;

use Livewire\Attributes\Locked;

trait SelectsDataItems
{
    #[Locked] public int $dataColumnIndex = 0;
    #[Locked] public ?int $dataRowIndex = null;
    public string $newDataFieldKind = 'variable';
    public string $formatTarget = 'value';

    public function selectDataItem(int $block, int $column, ?int $row = null): void
    {
        abort_unless(($this->definition['blocks'][$block]['type'] ?? '') === 'data_table', 422);
        $this->selectBlock($block);
        $sections = array_values($this->blockData['data_sections']);
        abort_unless($column >= 0 && isset($sections[$column]), 422);
        abort_unless($row === null || ($row >= 0 && isset(array_values($sections[$column]['fields'])[$row])), 422);
        $this->dataColumnIndex = $column;
        $this->dataRowIndex = $row;
        $this->formatTarget = $row === null ? 'title' : (array_values($sections[$column]['fields'])[$row]['mode'] === 'signature' ? 'signature' : 'value');
    }

    public function dataColumnPath(): string
    {
        $key = array_keys($this->blockData['data_sections'] ?? [])[$this->dataColumnIndex] ?? null;
        abort_if($key === null, 422);

        return 'blockData.data_sections.'.$key;
    }

    public function dataItemPath(): string
    {
        $column = array_values($this->blockData['data_sections'])[$this->dataColumnIndex];
        $key = array_keys($column['fields'])[$this->dataRowIndex ?? -1] ?? null;
        abort_if($key === null, 422);

        return $this->dataColumnPath().'.fields.'.$key;
    }
}
