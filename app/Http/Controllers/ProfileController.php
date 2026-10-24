<?php

namespace App\Http\Controllers;

use App\Modules\Access\Models\GoogleAccount;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController
{
    public function edit(Request $request)
    {
        return view('profile.edit', [
            'user' => $request->user(),
            'googleAccount' => GoogleAccount::where('user_id', $request->user()->id)->first(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'theme_preference' => ['required', Rule::in(['light', 'dark', 'system'])],
        ]);
        $request->user()->update($data);

        return back()->with('status', 'Preferencias guardadas correctamente.');
    }
}
