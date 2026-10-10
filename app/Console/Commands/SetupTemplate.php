<?php

namespace App\Console\Commands;

use App\Models\BrandSetting;
use App\Models\Institution;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class SetupTemplate extends Command
{
    protected $signature = 'template:setup {email}';

    protected $description = 'Configura una instalación nueva y crea su primer SuperAdmin';

    public function handle(): int
    {
        if (User::whereHas('roles', fn ($q) => $q->where('name', 'SuperAdmin'))->exists()) {
            $this->error('Ya existe un SuperAdmin. Use la administración para crear otras cuentas.');

            return self::FAILURE;
        }
        $email = $this->argument('email');
        $name = $this->ask('Nombre del superadministrador');
        $institution = $this->ask('Nombre de su institución', 'Corpomédica');
        $brand = $this->ask('Nombre de la plataforma', 'Corpo Training');
        $password = $this->secret('Contraseña (mínimo 12 caracteres)');
        $confirm = $this->secret('Repita la contraseña');
        $validator = Validator::make(compact('email', 'name', 'institution', 'brand', 'password', 'confirm'), [
            'email' => 'required|email|unique:users,email', 'name' => 'required|string|max:255',
            'institution' => 'required|string|max:255', 'brand' => 'required|string|max:255',
            'password' => ['required', Password::min(12)], 'confirm' => 'same:password',
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

return self::FAILURE;
        }
        DB::transaction(function () use ($email, $name, $institution, $brand, $password) {
            $this->call('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true]);
            $org = Institution::firstOrCreate(['code' => 'INITIAL'], ['name' => $institution]);
            $user = User::create(['name' => $name, 'email' => $email, 'password' => $password, 'institution_id' => $org->id, 'can_access_admin' => true, 'is_active' => true]);
            $user->assignRole('SuperAdmin');
            BrandSetting::updateOrCreate(['id' => 1], ['name' => $brand]);
        });
        $this->info('Configuración inicial creada. Ingrese a /login o /admin/login.');

        return self::SUCCESS;
    }
}
