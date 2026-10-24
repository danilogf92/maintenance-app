<?php

namespace Database\Seeders;

use App\Models\Institution;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/** Adds identifiable demo records; never updates or deletes existing records. */
class NavigationDemoSeeder extends Seeder
{
    private const RECORD_COUNT = 500;
    private const CODE_PREFIX = 'NAV-DEMO-';

    public function run(): void
    {
        if (!app()->environment('testing')) { $this->command?->warn('La carga de demostraciones está deshabilitada.'); return; }

        $this->validateSchema();
        $role = Role::where('name', 'Cliente')->where('guard_name', 'web')->first();
        // Unknown random password: demo accounts receive no shared public credential.
        $password = Hash::make(Str::random(64));
        $created = DB::transaction(function () use ($role, $password): array {
            $institutions = 0;
            $users = 0;
            for ($number = 1; $number <= self::RECORD_COUNT; $number++) {
                $institution = $this->institution($number);
                $institutions += (int) $institution->wasRecentlyCreated;
                $user = $this->user($number, $institution, $password);
                if ($user->wasRecentlyCreated) {
                    $users++;
                    if ($role) {
                        $user->assignRole($role);
                    }
                }
            }
            return compact('institutions', 'users');
        });
        $this->command?->info("Demo: {$created['institutions']} instituciones y {$created['users']} usuarios nuevos.");
        $this->command?->info('Los registros existentes se conservaron. Repetir no duplica los datos demo.');
    }

    private function validateSchema(): void
    {
        $required = [
            'institutions' => ['name', 'code', 'type', 'city', 'contact_email', 'contact_phone'],
            'users' => ['institution_id', 'name', 'email', 'password', 'job_title', 'is_active', 'can_access_admin'],
        ];
        foreach ($required as $table => $columns) {
            if (!Schema::hasColumns($table, $columns)) {
                throw new \RuntimeException("Esquema incompatible en {$table}; no se generaron datos demo.");
            }
        }
    }

    private function institution(int $number): Institution
    {
        $types = ['hospital', 'clinic', 'university', 'company', 'other'];
        $labels = ['Hospital', 'Clínica', 'Universidad', 'Empresa', 'Centro'];
        $cities = ['Quito', 'Guayaquil', 'Cuenca', 'Ambato', 'Loja', 'Manta'];
        $index = ($number - 1) % count($types);
        $suffix = str_pad((string) $number, 4, '0', STR_PAD_LEFT);

        return Institution::firstOrCreate(['code' => self::CODE_PREFIX.$suffix], [
            'name' => "{$labels[$index]} Demo Navegación {$suffix}",
            'type' => $types[$index],
            'city' => $cities[($number - 1) % count($cities)],
            'contact_email' => "institucion.{$suffix}@navigation-demo.example.test",
            'contact_phone' => '02'.str_pad((string) $number, 7, '0', STR_PAD_LEFT),
        ]);
    }

    private function user(int $number, Institution $institution, string $password): User
    {
        $suffix = str_pad((string) $number, 4, '0', STR_PAD_LEFT);
        $jobs = ['Ingeniero', 'Técnico', 'Coordinador', 'Asesor', 'Participante'];

        return User::firstOrCreate(['email' => "usuario.{$suffix}@navigation-demo.example.test"], [
            'name' => "Usuario Demo Navegación {$suffix}",
            'institution_id' => $institution->id,
            'job_title' => $jobs[($number - 1) % count($jobs)],
            'identification' => 'NAV-'.$suffix,
            'phone' => '09'.str_pad((string) $number, 8, '0', STR_PAD_LEFT),
            'password' => $password,
            'is_active' => $number % 5 !== 0,
            'can_access_admin' => false,
        ]);
    }
}
