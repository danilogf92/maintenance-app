<?php

namespace App\Modules\Activities\Actions;

use App\Modules\Workflow\Actions\SaveRecord;
use App\Modules\Activities\Models\Activity;

class SaveActivity
{
    public function execute(array $data, ?int $id = null): Activity
    {
        return app(SaveRecord::class)->execute('activities', $data, $id);
    }
}
