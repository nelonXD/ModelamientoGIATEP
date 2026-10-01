<?php

namespace Database\Seeders;

use App\Models\Establishment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class GiatepDemoSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'delegado' => 'Delegado de Seguridad', 'cphs' => 'Integrante del Comité Paritario',
            'prevencion' => 'Unidad de Prevención', 'jefatura' => 'Jefatura Directa',
            'alta-direccion' => 'Alta Dirección', 'administrador' => 'Administrador',
        ];

        foreach ($roles as $slug => $name) {
            Role::updateOrCreate(['slug' => $slug], ['name' => $name]);
        }

        foreach ([['CESFAM', 'Centro Demostración Norte'], ['CECOSF', 'Centro Demostración Sur'], ['Oficina administrativa', 'Dependencia Demostración Central']] as [$type, $name]) {
            Establishment::updateOrCreate(compact('type', 'name'), ['is_active' => true]);
        }

        $password = env('GIATEP_ADMIN_PASSWORD');
        if (! is_string($password) || strlen($password) < 8) {
            $this->command?->warn('Define GIATEP_ADMIN_PASSWORD con al menos 8 caracteres para crear el administrador inicial.');

            return;
        }

        $admin = User::updateOrCreate(['rut' => '12345678-5'], [
            'name' => 'Administrador de Demostración', 'email' => 'admin.demo@giatep.local',
            'job_title' => 'Administración de plataforma', 'status' => 'active', 'password' => $password,
        ]);
        $admin->roles()->syncWithoutDetaching(Role::where('slug', 'administrador')->firstOrFail());
    }
}
