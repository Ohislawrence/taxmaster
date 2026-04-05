<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BusinessFactory extends Factory
{
    protected $model = Business::class;

    public function definition(): array
    {
        $businessName = $this->faker->company();

        return [
            'owner_id' => User::factory(),
            'name' => $businessName,
            'slug' => Str::slug($businessName) . '-' . Str::random(6),
            'registration_number' => 'RC' . $this->faker->numerify('######'),
            'tax_identification_number' => $this->faker->numerify('##########'),
            'business_type' => $this->faker->randomElement(['Limited Liability Company', 'Sole Proprietorship', 'Partnership', 'Enterprise']),
            'industry' => $this->faker->randomElement(['Technology', 'Manufacturing', 'Retail', 'Services', 'Finance', 'Healthcare']),
            'email' => $this->faker->unique()->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'country' => 'Nigeria',
            'state' => $this->faker->randomElement(['Lagos', 'Abuja', 'Rivers', 'Kano', 'Oyo']),
            'city' => $this->faker->city(),
            'address' => $this->faker->address(),
            'description' => $this->faker->sentence(),
            'status' => 'active',
            'is_vat_exempt' => false,
            'email_verified' => true,
            'email_verified_at' => now(),
            'settings' => [],
            'accounting_year_end' => 12,
            'incorporation_date' => $this->faker->dateTimeBetween('-10 years', '-1 year'),
            'has_staff' => true,
            'staff_count' => $this->faker->numberBetween(1, 50),
            'billing_managed_by_platform' => true,
        ];
    }

    /**
     * Indicate that the business is inactive
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
