<?php

namespace App\Modules\Activities\Configuration;

use Illuminate\Validation\Rule;

class Rules
{
    public static function make(): array
    {
        return ['service_id' => 'required|integer|exists:services,id', 'description' => 'required|string|max:5000',
            'result' => 'nullable|string|max:5000', 'photos' => 'sometimes|nullable|array|max:4',
            'photos.*' => ['string','regex:~^service-photos/[0-9]+/[A-Za-z0-9_-]+\\.(png|jpg|jpeg|webp)$~i']];
    }
}
