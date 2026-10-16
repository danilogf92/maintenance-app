<?php

namespace App\Modules\ReportTemplates\UI\Concerns;

use Livewire\Attributes\Locked;

trait SelectsPreviewElement
{
    #[Locked] public ?string $selectedColumn = null;
    #[Locked] public ?int $selectedLine = null;

    public function selectPreviewElement(int $index, ?string $column = null, ?int $line = null): void
    {
        abort_unless(isset($this->definition['blocks'][$index]), 422);
        abort_unless($column === null || in_array($column, ['left', 'center', 'right'], true), 422);
        if ($column !== null) {
            abort_unless($this->definition['blocks'][$index]['type'] === 'header', 422);
            $lines = $this->definition['blocks'][$index]['header_columns'][$column]['lines'] ?? [];
            abort_unless($line === null || ($line >= 0 && $line < count($lines)), 422);
        } else { abort_unless($line === null, 422); }
        $this->selectBlock($index);
        $this->selectedColumn = $column;
        $this->selectedLine = $line;
    }

    public function selectedLineKey(): string|int|null
    {
        if ($this->selectedColumn === null || $this->selectedLine === null) { return null; }
        $keys = array_keys($this->blockData['header_columns'][$this->selectedColumn]['lines'] ?? []);

        return $keys[$this->selectedLine] ?? null;
    }
}
