<?php

namespace App\Modules\User;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserData
{
    public const FIELDS = ['name', 'email', 'institution_id', 'identification', 'phone', 'job_title', 'password', 'is_active', 'can_access_admin', 'role_ids'];

    public function validate(array $data, ?User $target): array
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($target)],
            'institution_id' => 'required|integer|exists:institutions,id',
            'identification' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'password' => [$target ? 'nullable' : 'required', Password::min(12)],
            'is_active' => 'required|boolean',
            'can_access_admin' => 'required|boolean',
            'role_ids' => 'present|array',
            'role_ids.*' => ['integer', 'distinct', Rule::exists('roles', 'id')->where('guard_name', 'web')],
        ])->validate();
    }
}
