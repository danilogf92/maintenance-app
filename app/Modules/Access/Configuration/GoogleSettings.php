<?php

namespace App\Modules\Access\Configuration;

class GoogleSettings
{
    public static function ready(): bool
    {
        $settings = config('services.google', []);
        $redirect = $settings['redirect'] ?? '';
        $scheme = parse_url($redirect, PHP_URL_SCHEME);
        $host = parse_url($redirect, PHP_URL_HOST);

        return ($settings['enabled'] ?? false)
            && filled($settings['client_id'] ?? null)
            && filled($settings['client_secret'] ?? null)
            && filter_var($redirect, FILTER_VALIDATE_URL)
            && ($scheme === 'https' || ($scheme === 'http' && in_array($host, ['localhost', '127.0.0.1', '[::1]'])));
    }
}
