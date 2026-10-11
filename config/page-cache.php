<?php

return [
    'enabled' => env('PAGE_CACHE_ENABLED', true),
    'store' => env('PAGE_CACHE_STORE', 'page-data'),
    'records_seconds' => 30,
    'settings_seconds' => 300,
];
