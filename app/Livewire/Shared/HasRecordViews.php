<?php

namespace App\Livewire\Shared;

use App\Models\OperationView;
use App\Modules\Shared\FavoriteView;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;

trait HasRecordViews
{
    use ConfiguresRecordColumns;
    use HasTableTools;

    #[Locked]
    public ?int $currentViewId = null;

    #[Locked]
    public ?int $favoriteViewId = null;

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
        $favorite = app(FavoriteView::class)->current(auth()->id(), $this->viewModule());
        $this->favoriteViewId = $favorite->id;
        $this->selectView($favorite->id);
    }

    public function chooseFavorite(int $id): void
    {
        $favorite = app(FavoriteView::class)->choose(auth()->id(), $this->viewModule(), $id);
        $this->favoriteViewId = $favorite->id;
    }

    public function selectView(int $id): void
    {
        $view = $this->viewQuery()->findOrFail($id);
        // Maintenance APP v11.16: close record before changing tab.
        if ($this->currentViewId !== null && (int) $this->currentViewId !== $id) {
            if (property_exists($this, 'formOpen') && $this->formOpen) {
                // Preserve the existing close/save behavior and its validation.
                app()->call([$this, 'closeForm']);
                if ($this->formOpen) {
                    return;
                }
            }
            if (property_exists($this, 'editingId')) {
                $this->editingId = null;
            }
            if (property_exists($this, 'confirmDelete')) {
                $this->confirmDelete = false;
            }
            $this->resetValidation();
        }
        $this->currentViewId = $view->id;
        $this->viewName = $view->name;
        $allowed = array_keys($this->viewFields());
        $this->visibleColumns = array_values(array_intersect($view->columns ?? [], $allowed));
        $this->applyViewFilter($view->filter_value);
        $this->loadTableSettings($view->table_settings ?? []);
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

    public function closeViewSettings(): void
    {
        $this->viewSettingsOpen = false;
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
        if ($view->is_default) {
            abort(403, 'No se puede eliminar una vista predeterminada.');
        }
        $view->delete();
        $favorite = app(FavoriteView::class)->current(auth()->id(), $this->viewModule());
        $this->favoriteViewId = $favorite->id;
        $this->selectView($favorite->id);
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
