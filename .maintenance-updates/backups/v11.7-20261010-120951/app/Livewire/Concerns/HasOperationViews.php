<?php

namespace App\Livewire\Concerns;

use App\Models\OperationView;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

trait HasOperationViews
{
    public ?int $currentViewId = null;
    public string $viewName = '';
    public array $visibleColumns = [];
    public bool $viewSettingsOpen = false;
    public string $viewSaveState = 'saved';

    abstract protected function viewModule(): string;
    abstract protected function viewFields(): array;
    abstract protected function defaultViews(): array;
    abstract protected function applyViewFilter(string $value): void;

    protected function viewQuery()
    {
        return OperationView::query()->where('user_id', auth()->id())
            ->where('module', $this->viewModule());
    }

    public function initializeViews(): void
    {
        if (! $this->viewQuery()->exists()) {
            DB::transaction(function () {
                foreach ($this->defaultViews() as $index => $view) {
                    OperationView::create([
                        'user_id' => auth()->id(), 'module' => $this->viewModule(),
                        'name' => $view['name'], 'filter_value' => $view['filter'],
                        'columns' => array_keys($this->viewFields()),
                        'is_default' => true, 'sort_order' => $index,
                    ]);
                }
            });
        }
        $first = $this->viewQuery()->orderBy('sort_order')->firstOrFail();
        $this->selectView($first->id);
    }

    public function selectView(int $id): void
    {
        $view = $this->viewQuery()->findOrFail($id);
        $this->currentViewId = $view->id;
        $this->viewName = $view->name;
        $allowed = array_keys($this->viewFields());
        $this->visibleColumns = array_values(array_intersect($view->columns ?? [], $allowed));
        $this->applyViewFilter($view->filter_value);
        $this->viewSaveState = 'saved';
    }

    public function openViewSettings(): void
    {
        $this->viewSettingsOpen = true;
    }

    // El menu contextual configura una vista del usuario, nunca la de otro usuario.
    public function openViewSettingsFor(int $viewId): void
    {
        $this->selectView($viewId);
        $this->openViewSettings();
    }
    public function closeViewSettings(): void { $this->viewSettingsOpen = false; }

    public function updatedViewName(): void { $this->saveViewSettings(); }
    public function updatedVisibleColumns(): void { $this->saveViewSettings(); }

    public function saveViewSettings(): void
    {
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

    public function toggleColumn(string $column): void
    {
        if (! array_key_exists($column, $this->viewFields())) abort(422);
        $this->visibleColumns = in_array($column, $this->visibleColumns, true)
            ? array_values(array_diff($this->visibleColumns, [$column]))
            : [...$this->visibleColumns, $column];
        $this->saveViewSettings();
    }

    public function moveColumn(string $column, int $direction): void
    {
        $index = array_search($column, $this->visibleColumns, true);
        if ($index === false) return;
        $target = $index + $direction;
        if ($target < 0 || $target >= count($this->visibleColumns)) return;
        [$this->visibleColumns[$index], $this->visibleColumns[$target]] =
            [$this->visibleColumns[$target], $this->visibleColumns[$index]];
        $this->saveViewSettings();
    }

    public function addView(): void
    {
        $view = OperationView::create([
            'user_id' => auth()->id(), 'module' => $this->viewModule(),
            'name' => 'Nueva vista', 'filter_value' => '',
            'columns' => array_keys($this->viewFields()),
            'sort_order' => ((int) $this->viewQuery()->max('sort_order')) + 1,
        ]);
        $this->selectView($view->id);
        $this->openViewSettings();
    }

    public function deleteView(): void
    {
        $view = $this->viewQuery()->findOrFail($this->currentViewId);
        if ($view->is_default) abort(403, 'No se puede eliminar una vista predeterminada.');
        $view->delete();
        $this->selectView($this->viewQuery()->orderBy('sort_order')->firstOrFail()->id);
        $this->closeViewSettings();
    }

    protected function operationViewsData(): array
    {
        return [
            'operationViews' => $this->viewQuery()->orderBy('sort_order')->get(),
            'availableColumns' => $this->viewFields(),
        ];
    }
}
