<?php

namespace App\Support\Demo;

use App\Models\Institution;
use App\Models\User;
use App\Support\Cache\PageDataCache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class ResetDemoData
{
    public function execute(int $count = 500): array
    {
        abort_unless(app()->environment('testing'), 403, 'La generación de demostraciones está deshabilitada.');
        abort_unless($count >= 1 && $count <= 500, 422);
        $result = DB::transaction(fn () => $this->replace($count));
        app(PageDataCache::class)->invalidate('users', 'institutions', 'institution-options');

        return $result;
    }

    private function replace(int $count): array
    {
        $admins = User::role('SuperAdmin')->lockForUpdate()->get();
        abort_if($admins->isEmpty(), 422, 'Debe existir al menos un SuperAdmin.');
        $adminIds = $admins->pluck('id')->all();
        $institutions = $admins->pluck('institution_id')->unique()->all();
        User::whereNotIn('id', $adminIds)->get()->each(fn (User $user) => $user->delete());
        DB::table('sessions')->whereNotNull('user_id')->whereNotIn('user_id', $adminIds)->delete();
        DB::table('password_reset_tokens')->delete();
        DB::table('operation_record_creations')->delete();
        Institution::whereNotIn('id', $institutions)->delete();
        $newIds = $this->createInstitutions($count);
        $this->createUsers($count, $newIds);

        return ['preserved_admins' => count($adminIds), 'institutions' => count($newIds), 'users' => $count];
    }

    private function createInstitutions(int $count): array
    {
        $ids = [];
        for ($index = 1; $index <= $count; $index++) {
            $data = DemoCatalog::institution($index);
            // Preserve an admin's institution even if it already uses a demo code.
            while (Institution::where('code', $data['code'])->exists()) {
                $data['code'] .= '-NEW';
            }
            $ids[] = Institution::create($data)->id;
        }

        return $ids;
    }

    private function createUsers(int $count, array $institutionIds): void
    {
        $passwordHash = Hash::make(Str::random(64));
        for ($index = 1; $index <= $count; $index++) {
            $institution = $institutionIds[(intdiv($index - 1, 5) * 17) % count($institutionIds)];
            $data = DemoCatalog::user($index, $institution, $passwordHash);
            while (User::where('email', $data['email'])->exists()) {
                $data['email'] = 'demo.'.Str::random(12).'@example.test';
            }
            User::create($data)->assignRole($index % 3 === 0 ? 'Cliente' : 'Técnico');
        }
    }
}
