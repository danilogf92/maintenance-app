<?php

namespace App\Filament\Pages\Auth;

class Login extends \Filament\Auth\Pages\Login
{
    protected function getCredentialsFromFormData(array $data): array
    {
        return parent::getCredentialsFromFormData($data) + ['is_active' => true];
    }
}
