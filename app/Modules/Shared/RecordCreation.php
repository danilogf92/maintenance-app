<?php

namespace App\Modules\Shared;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/** One database record per editor session, including retried requests. */
class RecordCreation
{
    public function remember(int $userId, string $module, string $token, string $model, Closure $create): Model
    {
        return DB::transaction(function () use ($userId, $module, $token, $model, $create) {
            $key = ['user_id' => $userId, 'module' => $module, 'token' => $token];
            DB::table('operation_record_creations')->insertOrIgnore($key + [
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $entry = DB::table('operation_record_creations')->where($key)->lockForUpdate()->first();
            if ($entry->record_id !== null) {
                // A deleted record must not be recreated by a delayed request.
                return $model::findOrFail($entry->record_id);
            }
            $record = $create();
            DB::table('operation_record_creations')->where('id', $entry->id)->update([
                'record_id' => $record->getKey(), 'updated_at' => now(),
            ]);

            return $record;
        });
    }
}
