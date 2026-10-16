<?php

namespace App\Modules\ReportTemplates\UI\Concerns;

use App\Modules\ReportTemplates\Configuration\Blocks;
use App\Modules\ReportTemplates\Configuration\DataTable;
use Filament\Notifications\Notification;

trait EditsBlocks
{
    public function selectBlock(int $index): void
    {
        $this->syncBlock();
        abort_unless(isset($this->definition['blocks'][$index]), 422);
        $this->selected = $index;
        $this->selectedColumn = null;
        $this->selectedLine = null;
        $this->dataColumnIndex = 0;
        $this->dataRowIndex = null;
        $this->formatTarget = 'value';
        $this->blockData = $this->definition['blocks'][$index];
        $this->cacheSchema('form', null);
        $this->form->fill($this->blockData);
    }

    public function addBlock(): void
    {
        if (count($this->definition['blocks']) >= 30) { return; }
        abort_unless(isset(Blocks::options()[$this->newBlock]), 422);
        $this->syncBlock();
        $this->definition['blocks'][] = Blocks::make($this->newBlock);
        $this->selectBlock(count($this->definition['blocks']) - 1);
    }

    public function moveBlock(int $index, int $direction): void
    {
        $this->syncBlock();
        $target = $index + $direction;
        abort_unless(in_array($direction, [-1, 1]), 422);
        if (! isset($this->definition['blocks'][$index], $this->definition['blocks'][$target])) { return; }
        $blocks = &$this->definition['blocks'];
        [$blocks[$index], $blocks[$target]] = [$blocks[$target], $blocks[$index]];
        $this->selected = $this->selected === $index ? $target : ($this->selected === $target ? $index : $this->selected);
    }

    public function removeBlock(int $index): void
    {
        $this->syncBlock();
        if (count($this->definition['blocks']) <= 1) {
            Notification::make()->warning()->title('Conserva al menos un bloque.')->send(); return;
        }
        abort_unless(isset($this->definition['blocks'][$index]), 422);
        array_splice($this->definition['blocks'], $index, 1);
        $this->selectedColumn = null;
        $this->selectedLine = null;
        $this->dataColumnIndex = 0;
        $this->dataRowIndex = null;
        $this->formatTarget = 'value';
        $this->selected = min($this->selected, count($this->definition['blocks']) - 1);
        $this->blockData = $this->definition['blocks'][$this->selected];
        $this->cacheSchema('form', null);
        $this->form->fill($this->blockData);
    }

    public function updatedBlockData(): void
    {
        if (isset($this->definition['blocks'][$this->selected])) {
            $block = DataTable::upgrade(['blocks' => [$this->blockData]])['blocks'][0];
            if (($block['type'] ?? '') === 'data_table') { $this->blockData = $block; }
            if (($block['font_size'] ?? null) === '') { $block['font_size'] = null; }
            foreach (['company_logo', 'factory_logo'] as $key) {
                if (is_array($block[$key] ?? null)) {
                    $files = array_values($block[$key]);
                    $block[$key] = isset($files[0]) && is_string($files[0]) ? $files[0] : null;
                }
            }
            $this->definition['blocks'][$this->selected] = $block;
        }
    }

    private function syncBlock(): void
    {
        $state = $this->form->getState();
        $this->definition['blocks'][$this->selected] = array_replace($this->definition['blocks'][$this->selected], $state);
    }
}
