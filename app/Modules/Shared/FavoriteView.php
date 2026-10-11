<?php

namespace App\Modules\Shared;

use App\Models\OperationView;
use App\Models\OperationViewPreference;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FavoriteView
{
    public function current(int $userId, string $module): OperationView
    {
        $preference = OperationViewPreference::where('user_id', $userId)->where('module', $module)->first();
        $views = OperationView::where('user_id', $userId)->where('module', $module);
        $favorite = $preference ? (clone $views)->find($preference->operation_view_id) : null;
        if ($favorite) {
            return $favorite;
        }

        return DB::transaction(function () use ($userId, $module) {
            User::whereKey($userId)->lockForUpdate()->firstOrFail();
            // Recheck after the lock: a concurrent explicit choice must not be overwritten.
            $preference = OperationViewPreference::where('user_id', $userId)->where('module', $module)->first();
            $views = OperationView::where('user_id', $userId)->where('module', $module);
            $favorite = $preference ? (clone $views)->find($preference->operation_view_id) : null;
            if ($favorite) {
                return $favorite;
            }
            $first = $views->orderBy('sort_order')->orderBy('id')->firstOrFail();
            OperationViewPreference::updateOrCreate(['user_id' => $userId, 'module' => $module],
                ['operation_view_id' => $first->id]);

            return $first;
        });
    }

    public function choose(int $userId, string $module, int $viewId): OperationView
    {
        return DB::transaction(function () use ($userId, $module, $viewId) {
            // Serialize choices for the same user; the unique index also guarantees one slot.
            User::whereKey($userId)->lockForUpdate()->firstOrFail();
            $view = OperationView::where('user_id', $userId)->where('module', $module)
                ->whereKey($viewId)->lockForUpdate()->firstOrFail();
            OperationViewPreference::updateOrCreate(['user_id' => $userId, 'module' => $module],
                ['operation_view_id' => $view->id]);

            return $view;
        });
    }
}
