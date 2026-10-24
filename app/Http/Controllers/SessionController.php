<?php

namespace App\Http\Controllers;

use App\Modules\Access\Actions\CompleteLogin;
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
        return app(CompleteLogin::class)->execute($request);
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
