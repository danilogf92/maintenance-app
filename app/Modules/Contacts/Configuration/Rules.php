<?php
namespace App\Modules\Contacts\Configuration;

class Rules
{
    public static function make(): array
    {
        return ['name' => 'required|string|max:160', 'position' => 'nullable|string|max:120', 'department' => 'nullable|string|max:120',
            'email' => 'nullable|email|max:160', 'phone' => 'nullable|string|max:60', 'extension' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:5000', 'active' => 'required|boolean', 'lock_version' => 'required|integer|min:0'];
    }
}
