<?php
namespace App\Modules\Brands\Configuration;
class Rules
{
    public static function make(): array { return ['distributor' => 'nullable|string|max:255', 'origin' => 'nullable|string|max:255', 'name' => 'required|string|max:100', 'active' => 'required|boolean', 'notes' => 'nullable|string|max:5000', 'lock_version' => 'required|integer|min:0', 'manufacturer_id' => 'nullable|integer|exists:manufacturers,id']; }
}
