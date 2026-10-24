<?php

namespace App\Modules\Services\Actions;

use App\Modules\Workflow\Actions\SaveRecord;
use App\Modules\Services\Models\Service;

class SaveService
{
    public function execute(array $data, ?int $id = null): Service
    {
        return app(SaveRecord::class)->execute('services', $data, $id);
    }
}
