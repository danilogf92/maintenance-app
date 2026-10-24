<?php
namespace App\Modules\EquipmentModels\Configuration;
class Rules
{
    public static function make(): array { return ['description' => 'nullable|string|max:5000', 'visits_recommended_per_year' => 'nullable|integer|min:0|max:365', 'name' => 'required|string|max:100', 'active' => 'required|boolean', 'notes' => 'nullable|string|max:5000', 'lock_version' => 'required|integer|min:0', 'brand_id' => 'nullable|integer|exists:brands,id']; }
}
