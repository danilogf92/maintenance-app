<?php

namespace App\Modules\Workflow\UI\Controllers;

use App\Modules\Activities\Actions\PrivatePhotos;
use App\Modules\Workflow\Queries\VisibleRecords;
use Illuminate\Support\Facades\Storage;

class ActivityPhotoController
{
    public function __invoke(int $activity, int $photo)
    {
        $record = VisibleRecords::query('activities')->findOrFail($activity);
        $path = array_values($record->photos ?? [])[$photo] ?? null;
        abort_unless($path, 404); PrivatePhotos::image($path);

        return response()->file(Storage::disk('local')->path($path), ['Cache-Control' => 'no-store', 'X-Content-Type-Options' => 'nosniff'])->setPrivate();
    }
}
