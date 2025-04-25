<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invitation>
 */
class InvitationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail,
            'role' => 'user', // Default value from your schema
            'invited_by' => $this->faker->name, // Assuming invited_by is a name or user identifier
            'token' => $this->faker->uuid, // Generate a unique token
            'used' => false, // Default value from your schema
        ];
    }
}
