<?php

namespace App\Modules\Equipment\Actions;

use App\Modules\Equipment\Models\Equipment;
use App\Modules\Workflow\Actions\SaveRecord;

class SaveEquipment
{
    public function execute(array $data, ?int $id = null): Equipment
    {
        return app(SaveRecord::class)->execute('equipment', $data, $id);
    }
}
