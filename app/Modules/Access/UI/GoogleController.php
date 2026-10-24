<?php

namespace App\Modules\Access\UI;

use App\Modules\Access\Actions\CompleteLogin;
use App\Modules\Access\Actions\ResolveGoogleAccount;
use App\Modules\Access\Configuration\GoogleSettings;
use App\Modules\Access\Models\GoogleAccount;
use App\Modules\Access\Queries\GoogleProviderFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class GoogleController
{
    public function redirect(Request $request) { return $this->start($request, null); }
    public function connect(Request $request) { return $this->start($request, $request->user()->id); }

    private function start(Request $request, ?int $userId)
    {
        if (!GoogleSettings::ready()) return $this->failure($request, $userId !== null, 'El acceso con Google todavía no está configurado.');
        $request->session()->put('google_oauth_context', ['user_id' => $userId, 'expires' => now()->addMinutes(10)->timestamp]);

        return $this->provider()->with(['prompt' => 'select_account'])->redirect();
    }

    public function callback(Request $request)
    {
        $context = $request->session()->pull('google_oauth_context');
        $link = is_array($context) && !empty($context['user_id']);
        $state = $request->session()->get('state');
        $verifier = $request->session()->get('code_verifier');
        if (!GoogleSettings::ready() || !is_array($context) || ($context['expires'] ?? 0) < now()->timestamp
            || ($context['user_id'] ?? null) !== Auth::id() || !is_string($state) || !is_string($request->query('state'))
            || !is_string($verifier) || strlen($verifier) < 43 || strlen($verifier) > 128
            || !hash_equals($state, $request->query('state'))) {
            return $this->failure($request, $link, 'La solicitud de Google venció o no es válida. Inténtalo nuevamente.');
        }
        if ($request->has('error') || !is_string($request->query('code')) || $request->query('code') === '') {
            return $this->failure($request, $link, 'No se completó la autorización con Google. Puedes intentarlo otra vez.');
        }
        try {
            $identity = $this->provider()->user();
            $user = app(ResolveGoogleAccount::class)->execute($identity, $context['user_id']);
            $request->session()->forget('code_verifier');
            if ($link) return redirect()->route('profile.edit')->with('status', 'Cuenta de Google vinculada correctamente.');
            Auth::login($user);
            return app(CompleteLogin::class)->execute($request);
        } catch (ValidationException $exception) {
            return $this->failure($request, $link, $exception->errors()['google'][0]);
        } catch (Throwable $exception) {
            Log::warning('Google authentication failed.', ['exception_type' => get_class($exception)]);
            return $this->failure($request, $link, 'No pudimos completar el ingreso con Google. Inténtalo de nuevo o utiliza tu contraseña.');
        }
    }

    public function disconnect(Request $request)
    {
        DB::transaction(function () use ($request) {
            \App\Models\User::lockForUpdate()->findOrFail($request->user()->id);
            GoogleAccount::where('user_id', $request->user()->id)->delete();
        });
        $request->session()->forget(['google_oauth_context', 'state', 'code_verifier']);

        return redirect()->route('profile.edit')->with('status', 'Google desvinculado. Puedes seguir usando tu contraseña.');
    }

    private function provider()
    {
        return app(GoogleProviderFactory::class)->make();
    }

    private function failure(Request $request, bool $link, string $message)
    {
        $request->session()->forget(['google_oauth_context', 'state', 'code_verifier']);
        return redirect()->route($link && Auth::check() ? 'profile.edit' : 'login')->withErrors(['google' => $message]);
    }
}
