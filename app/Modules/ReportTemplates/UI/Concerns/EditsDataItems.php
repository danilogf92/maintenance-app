<?php

namespace App\Modules\ReportTemplates\UI\Concerns;

use App\Modules\ReportTemplates\Configuration\DataTable;

trait EditsDataItems
{
    public function addDataItem(): void
    {
        abort_unless(($this->blockData['type'] ?? '') === 'data_table', 422);
        abort_unless(in_array($this->newDataFieldKind, ['variable', 'manual', 'signature'], true), 422);
        $sections = array_values($this->blockData['data_sections']);
        $fields = array_values($sections[$this->dataColumnIndex]['fields']);
        if (count($fields) >= 30) { return; }
        $field = $this->newDataFieldKind === 'signature' ? DataTable::signatureField() : DataTable::field('Dato:', 'client.name');
        $field['mode'] = $this->newDataFieldKind;
        $fields[] = $field;
        $sections[$this->dataColumnIndex]['fields'] = $fields;
        $this->refillDataSections($sections);
        $this->dataRowIndex = count($fields) - 1;
        $this->formatTarget = $this->newDataFieldKind === 'signature' ? 'signature' : 'value';
    }

    public function removeDataItem(): void
    {
        abort_unless($this->dataRowIndex !== null, 422);
        $sections = array_values($this->blockData['data_sections']);
        $fields = array_values($sections[$this->dataColumnIndex]['fields']);
        if (count($fields) <= 1) { return; }
        array_splice($fields, $this->dataRowIndex, 1);
        $sections[$this->dataColumnIndex]['fields'] = $fields;
        $this->refillDataSections($sections);
        $this->dataRowIndex = min($this->dataRowIndex, count($fields) - 1);
        $this->formatTarget = $fields[$this->dataRowIndex]['mode'] === 'signature' ? 'signature' : 'value';
    }

    public function moveDataItem(int $direction): void
    {
        abort_unless(in_array($direction, [-1, 1], true) && $this->dataRowIndex !== null, 422);
        $sections = array_values($this->blockData['data_sections']);
        $fields = array_values($sections[$this->dataColumnIndex]['fields']);
        $target = $this->dataRowIndex + $direction;
        if (! isset($fields[$target])) { return; }
        [$fields[$target], $fields[$this->dataRowIndex]] = [$fields[$this->dataRowIndex], $fields[$target]];
        $sections[$this->dataColumnIndex]['fields'] = $fields;
        $this->refillDataSections($sections);
        $this->dataRowIndex = $target;
    }

    public function addDataColumn(): void
    {
        abort_unless(($this->blockData['type'] ?? '') === 'data_table', 422);
        $sections = array_values($this->blockData['data_sections']);
        if (count($sections) >= 3) { return; }
        $sections[] = ['title' => 'Nueva columna', 'show_title' => true, 'item_alignment' => 'left',
            'fields' => [DataTable::field('Dato:', 'client.name')]];
        $this->refillDataSections($sections);
        $this->dataColumnIndex = count($sections) - 1;
        $this->dataRowIndex = null;
        $this->formatTarget = 'title';
    }

    public function removeDataColumn(): void
    {
        abort_unless(($this->blockData['type'] ?? '') === 'data_table', 422);
        $sections = array_values($this->blockData['data_sections']);
        if (count($sections) <= 1) { return; }
        array_splice($sections, $this->dataColumnIndex, 1);
        $this->refillDataSections($sections);
        $this->dataColumnIndex = min($this->dataColumnIndex, count($sections) - 1);
        $this->dataRowIndex = null;
        $this->formatTarget = 'title';
    }

    private function refillDataSections(array $sections): void
    {
        $this->blockData['data_sections'] = $sections;
        $this->form->fill($this->blockData);
        $this->updatedBlockData();
    }
}
