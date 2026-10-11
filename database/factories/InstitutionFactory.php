<?php
namespace Database\Factories;
use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;
class InstitutionFactory extends Factory {
    protected $model = Institution::class;
    public function definition(): array {
        return ['name' => fake()->company(), 'code' => 'ORG-'.fake()->unique()->numerify('######'), 'type' => fake()->randomElement(['company','hospital','clinic','university']), 'contact_email' => fake()->companyEmail(), 'contact_phone' => fake()->phoneNumber(), 'city' => fake()->city()];
    }
}
