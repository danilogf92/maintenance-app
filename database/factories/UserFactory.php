<?php
namespace Database\Factories;
use App\Models\User;
use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class UserFactory extends Factory {
    protected $model = User::class;
    public function definition(): array {
        return ['institution_id' => Institution::factory(), 'name' => fake()->name(), 'email' => fake()->unique()->safeEmail(), 'password' => Hash::make(Str::random(32)), 'job_title' => fake()->jobTitle(), 'is_active' => true, 'can_access_admin' => false, 'theme_preference' => 'system'];
    }
}
