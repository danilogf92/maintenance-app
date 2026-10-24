<?php
namespace App\Modules\Manufacturers\Configuration;
class Rules
{
    public static function make(): array { return ['name' => 'required|string|max:160', 'active' => 'required|boolean', 'notes' => 'nullable|string|max:5000', 'lock_version' => 'required|integer|min:0', 'country' => 'nullable|string|max:100', 'website' => ['nullable','url:http,https','max:255']]; }
}
