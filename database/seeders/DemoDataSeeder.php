<?php
namespace Database\Seeders;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
class DemoDataSeeder extends Seeder {
    public function run(): void {
        if (!app()->environment('testing')) { $this->command?->warn('La carga de demostraciones está deshabilitada.'); return; }

        $companies = [
            ['code'=>'DEMO-EMP-01','name'=>'Empresa Demo Industrial','type'=>'company','city'=>'Quito'],
            ['code'=>'DEMO-HOS-01','name'=>'Hospital Demo Central','type'=>'hospital','city'=>'Guayaquil'],
            ['code'=>'DEMO-UNI-01','name'=>'Universidad Demo Técnica','type'=>'university','city'=>'Cuenca'],
        ];
        foreach ($companies as $data) {
            $institution = Institution::firstOrCreate(['code'=>$data['code']], $data);
            foreach (range(1, 3) as $number) {
                $email = strtolower(str_replace('-', '.', $data['code'])).".usuario{$number}@example.test";
                $user = User::firstOrCreate(['email'=>$email], [
                    'institution_id'=>$institution->id,
                    'name'=>"Usuario Demo {$number} - {$institution->name}",
                    'password'=>Hash::make(Str::random(48)),
                    'job_title'=>'Participante', 'is_active'=>true,
                    'can_access_admin'=>false, 'theme_preference'=>'system',
                ]);
                if (Role::where('name','Cliente')->where('guard_name','web')->exists() && ! $user->hasAnyRole(['SuperAdmin','Administrador'])) {
                    $user->assignRole('Cliente');
                }
            }
        }
    }
}
