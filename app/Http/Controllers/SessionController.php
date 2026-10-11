<?php

namespace App\Http\Controllers;

use App\Support\Cache\PageDataCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SessionController
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $data = $request->validate(['email' => 'required|email', 'password' => 'required|string']);
        if (! Auth::attempt($data + ['is_active' => true])) {
            throw ValidationException::withMessages(['email' => 'Credenciales incorrectas o cuenta desactivada.']);
        }
        $request->session()->regenerate();
        app(PageDataCache::class)->invalidate('brand', 'palette', 'institution-options');

        return redirect()->intended('/dashboard');
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
