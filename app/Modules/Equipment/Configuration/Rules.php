<?php

namespace App\Modules\Equipment\Configuration;

use Illuminate\Validation\Rule;

class Rules
{
    public static function make(): array
    {
        return ['client_id' => 'required|integer|exists:clients,id', 'institution_id' => 'required|integer|exists:institutions,id',
            'name' => 'required|string|max:160', 'brand' => 'nullable|string|max:100', 'model' => 'nullable|string|max:100',
            'brand_id' => 'nullable|integer|exists:brands,id', 'equipment_model_id' => 'nullable|integer|exists:equipment_models,id',
            'serial' => 'nullable|string|max:100', 'location' => 'nullable|string|max:160', 'status' => ['required', Rule::in(array_keys(Fields::statuses()))]];
    }
}
