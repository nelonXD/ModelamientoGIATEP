<?php

namespace Database\Seeders;

use App\Models\Establishment;
use Illuminate\Database\Seeder;

class OperationalSampleSeeder extends Seeder
{
    public function run(): void
    {
        // Establecimientos reales de la Red de Salud Comunal de Los Ángeles
        Establishment::updateOrCreate(['name' => 'CESFAM Nororiente'], ['type' => 'CESFAM', 'is_active' => true]);
        Establishment::updateOrCreate(['name' => 'CESFAM Los Aromos'], ['type' => 'CESFAM', 'is_active' => true]);
        Establishment::updateOrCreate(['name' => 'CESFAM Nuevo Horizonte'], ['type' => 'CESFAM', 'is_active' => true]);
        Establishment::updateOrCreate(['name' => 'CESFAM 2 de Septiembre'], ['type' => 'CESFAM', 'is_active' => true]);
        Establishment::updateOrCreate(['name' => 'SAR Central'], ['type' => 'SAR', 'is_active' => true]);
        Establishment::updateOrCreate(['name' => 'Dirección Comunal de Salud'], ['type' => 'Oficina administrativa', 'is_active' => true]);
    }
}
