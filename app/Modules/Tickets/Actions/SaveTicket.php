<?php

namespace App\Modules\Tickets\Actions;

use App\Modules\Tickets\Models\Ticket;
use App\Modules\Workflow\Actions\SaveRecord;

class SaveTicket
{
    public function execute(array $data, ?int $id = null): Ticket
    {
        return app(SaveRecord::class)->execute('tickets', $data, $id);
    }
}
