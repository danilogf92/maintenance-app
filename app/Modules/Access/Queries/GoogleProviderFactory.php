<?php

namespace App\Modules\Access\Queries;

use GuzzleHttp\Client;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;

class GoogleProviderFactory
{
    public function make(): GoogleProvider
    {
        return Socialite::buildProvider(GoogleProvider::class, config('services.google'))
            ->enablePKCE()->setHttpClient(new Client(['connect_timeout' => 5, 'timeout' => 15]));
    }
}
