<?php

namespace App\Modules\Clients\Actions;

use App\Modules\Clients\Models\Client;
use App\Modules\Workflow\Actions\SaveRecord;

class SaveClient
{
    public function execute(array $data, ?int $id = null): Client
    {
        return app(SaveRecord::class)->execute('clients', $data, $id);
    }
}
