<?php

namespace App\Modules\Services\Configuration;

use Illuminate\Validation\Rule;

class Rules
{
    public static function make(): array
    {
        return ['ticket_id' => 'required|integer|exists:tickets,id', 'service_date' => 'required|date', 'service_type' => 'required|string|max:100',
            'status' => ['required', Rule::in(array_diff(array_keys(Fields::statuses()), ['historical']))], 'summary' => 'nullable|string|max:5000', 'recommendations' => 'nullable|string|max:5000'];
    }
}
