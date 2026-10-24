<?php

namespace App\Modules\Access\Actions;

use App\Support\Cache\PageDataCache;
use Illuminate\Http\Request;

class CompleteLogin
{
    public function execute(Request $request)
    {
        $request->session()->regenerate();
        app(PageDataCache::class)->invalidate('brand', 'palette', 'institution-options');

        return redirect()->intended('/dashboard');
    }
}
