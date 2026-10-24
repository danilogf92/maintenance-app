<?php

namespace App\Modules\Clients\Configuration;

use Illuminate\Validation\Rule;

class Rules
{
    public static function make(): array
    {
        return ['name' => 'required|string|max:160', 'tax_id' => 'nullable|string|max:40', 'email' => 'nullable|email|max:160',
            'phone' => 'nullable|string|max:40', 'address' => 'nullable|string|max:240', 'city' => 'nullable|string|max:100', 'active' => 'required|boolean'];
    }
}
