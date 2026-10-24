<?php

namespace App\Modules\Access\Actions;

use App\Models\User;
use App\Modules\Access\Models\GoogleAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Two\User as GoogleUser;

class ResolveGoogleAccount
{
    public function execute(GoogleUser $identity, ?int $linkUserId): User
    {
        $data = ['subject' => $identity->getId(), 'email' => $identity->getEmail()];
        if (Validator::make($data, ['subject' => 'required|string|max:255', 'email' => 'required|email|max:255'])->fails()
            || ($identity->user['email_verified'] ?? false) !== true) {
            $this->reject('Google no devolvió una identidad con correo verificado.');
        }

        return DB::transaction(function () use ($data, $linkUserId) {
            if ($linkUserId) return $this->link($linkUserId, $data);
            $account = GoogleAccount::where('subject', $data['subject'])->first();
            $user = $account ? User::lockForUpdate()->find($account->user_id) : null;
            if (!$user || !$user->is_active || !$this->sameEmail($user->email, $data['email'])
                || !GoogleAccount::where('user_id', $user->id)->where('subject', $data['subject'])->lockForUpdate()->exists()) {
                $this->reject('No se pudo acceder con esta cuenta. Ingresa con tu contraseña y revisa la vinculación en Mi perfil.');
            }
            return $user;
        });
    }

    private function link(int $userId, array $data): User
    {
        $user = User::lockForUpdate()->find($userId);
        if (!$user || !$user->is_active || !$this->sameEmail($user->email, $data['email'])) {
            $this->reject('Selecciona la cuenta de Google con el mismo correo de tu perfil.');
        }
        $existing = GoogleAccount::where('user_id', $userId)->first();
        $claimed = GoogleAccount::where('subject', $data['subject'])->first();
        if (($existing && $existing->subject !== $data['subject']) || ($claimed && $claimed->user_id !== $userId)) {
            $this->reject('La cuenta de Google ya está vinculada. Desvincúlala antes de cambiarla.');
        }
        GoogleAccount::updateOrCreate(['user_id' => $userId], $data);

        return $user;
    }

    private function sameEmail(string $local, string $google): bool { return strcasecmp($local, $google) === 0; }
    private function reject(string $message): never { throw ValidationException::withMessages(['google' => $message]); }
}
