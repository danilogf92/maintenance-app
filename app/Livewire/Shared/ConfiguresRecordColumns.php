<?php

namespace App\Livewire\Shared;

use Illuminate\Validation\ValidationException;

trait ConfiguresRecordColumns
{
    public function updatedViewName(): void
    {
        $this->saveViewSettings();
    }

    public function updatedVisibleColumns(): void
    {
        $this->saveViewSettings();
    }

    public function saveViewSettings(): void
    {
        $this->validate(['visibleColumns' => 'required|array', 'visibleColumns.*' => 'required|string']);
        $this->viewSaveState = 'saving';
        $allowed = array_keys($this->viewFields());
        $columns = array_values(array_unique(array_intersect($this->visibleColumns, $allowed)));
        if (! count($columns)) {
            $this->viewSaveState = 'error';
            throw ValidationException::withMessages(['visibleColumns' => 'Mantén al menos una columna visible.']);
        }
        $name = trim($this->viewName);
        if ($name === '' || mb_strlen($name) > 100) {
            $this->viewSaveState = 'error';
            throw ValidationException::withMessages(['viewName' => 'Introduce un nombre de 1 a 100 caracteres.']);
        }
        $this->viewQuery()->findOrFail($this->currentViewId)->update([
            'name' => $name, 'columns' => $columns,
        ]);
        $this->visibleColumns = $columns;
        $this->viewSaveState = 'saved';
        $this->resetValidation();
    }

    public function showAllColumns(): void
    {
        $this->visibleColumns = array_keys($this->viewFields());
        $this->saveViewSettings();
    }

    public function resetColumns(): void
    {
        $defaults = $this->viewModule() === 'users'
            ? ['name', 'institution', 'job_title', 'roles', 'status']
            : ['name', 'code', 'type', 'city', 'users'];
        $this->visibleColumns = array_values(array_intersect($defaults, array_keys($this->viewFields())));
        $this->saveViewSettings();
    }

    public function toggleColumn(string $column): void
    {
        if (! array_key_exists($column, $this->viewFields())) {
            abort(422);
        }
        if (count($this->visibleColumns) === 1 && in_array($column, $this->visibleColumns, true)) {
            throw ValidationException::withMessages(['visibleColumns' => 'Mantén al menos una columna visible.']);
        }
        $this->visibleColumns = in_array($column, $this->visibleColumns, true)
            ? array_values(array_diff($this->visibleColumns, [$column]))
            : [...$this->visibleColumns, $column];
        $this->saveViewSettings();
    }

    public function moveColumn(string $column, int $direction): void
    {
        $index = array_search($column, $this->visibleColumns, true);
        if ($index === false) {
            return;
        }
        $target = $index + $direction;
        if ($target < 0 || $target >= count($this->visibleColumns)) {
            return;
        }
        [$this->visibleColumns[$index], $this->visibleColumns[$target]] =
            [$this->visibleColumns[$target], $this->visibleColumns[$index]];
        $this->saveViewSettings();
    }
}
