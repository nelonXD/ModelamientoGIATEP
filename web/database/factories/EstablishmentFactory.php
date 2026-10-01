<?php

namespace Database\Factories;

use App\Models\Establishment;
use Illuminate\Database\Eloquent\Factories\Factory;

class EstablishmentFactory extends Factory
{
    protected $model = Establishment::class;

    public function definition(): array
    {
        return ['type' => 'CESFAM', 'name' => 'Centro Demostración '.fake()->unique()->numberBetween(1, 999), 'is_active' => true];
    }
}
