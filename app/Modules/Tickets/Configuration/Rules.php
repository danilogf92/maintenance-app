<?php

namespace App\Modules\Tickets\Configuration;

use Illuminate\Validation\Rule;

class Rules
{
    public static function make(): array
    {
        return ['origin' => ['sometimes', 'required', Rule::in(array_keys(Origins::all()))],
            'requester_contact_id' => 'sometimes|nullable|integer|exists:contacts,id', 'reported_by' => 'sometimes|nullable|string|max:160',
            'equipment_id' => 'required|integer|exists:equipment,id', 'technician_id' => 'required|integer|exists:users,id',
            'subject' => 'required|string|max:160', 'description' => 'nullable|string|max:5000',
            'priority' => ['required', Rule::in(['low','normal','high','urgent'])],
            'status' => ['required', Rule::in(array_diff(array_keys(Fields::statuses()), ['historical']))], 'opened_at' => 'required|date'];
    }
}
