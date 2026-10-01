<?php

namespace Database\Factories;

use App\Models\Establishment;
use App\Models\RegistrationRequest;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class RegistrationRequestFactory extends Factory
{
    protected $model = RegistrationRequest::class;

    public function definition(): array
    {
        return [
            'rut' => '11.111.111-1', 'pending_key' => '11.111.111-1',
            'name' => fake()->name(), 'job_title' => 'Técnico de demostración',
            'establishment_id' => Establishment::factory(), 'requested_role' => 'delegado',
            'email' => fake()->unique()->safeEmail(), 'phone' => '+56 9 0000 0000',
            'password' => Hash::make('password'), 'status' => 'pending',
        ];
    }

    public function resolved(string $status = 'approved'): static
    {
        return $this->state(['pending_key' => null, 'status' => $status, 'resolved_at' => now()]);
    }
}
