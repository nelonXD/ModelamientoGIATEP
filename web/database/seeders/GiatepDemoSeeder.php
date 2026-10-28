<?php

namespace Database\Seeders;

use App\Models\Establishment;
use App\Models\Role;
use App\Models\User;
use App\Support\OriginalGiatepSchema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class GiatepDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (OriginalGiatepSchema::isActive()) {
            $this->seedOriginalSchema();

            return;
        }

        $roles = [
            'delegado' => 'Delegado de Seguridad', 'cphs' => 'Comité Paritario',
            'prevencion' => 'Prevencionista', 'jefatura' => 'Jefatura',
            'alta-direccion' => 'Alta Dirección', 'administrador' => 'Administrador',
        ];

        foreach ($roles as $slug => $name) {
            Role::updateOrCreate(['slug' => $slug], ['name' => $name]);
        }

        foreach ([['CESFAM', 'Centro Demostración Norte'], ['CECOSF', 'Centro Demostración Sur'], ['Oficina administrativa', 'Dependencia Demostración Central']] as [$type, $name]) {
            Establishment::updateOrCreate(compact('type', 'name'), ['is_active' => true]);
        }

        $demoPassword = env('GIATEP_DEMO_PASSWORD');
        if ((! is_string($demoPassword) || strlen($demoPassword) < 8) && app()->environment(['local', 'testing'])) {
            $demoPassword = 'GiatepDemo2026!';
        }

        if (! is_string($demoPassword) || strlen($demoPassword) < 8) {
            $this->command?->warn('Define GIATEP_DEMO_PASSWORD con al menos 8 caracteres para crear los usuarios operativos de demostración.');
        } else {
            $establishment = Establishment::where('name', 'Dependencia Demostración Central')->firstOrFail();
            $demoUsers = [
                [
                    'rut' => '44444444-4',
                    'name' => 'Comité Paritario de Demostración',
                    'email' => 'comite.demo@giatep.local',
                    'job_title' => 'Representante del Comité Paritario',
                    'role' => 'cphs',
                ],
                [
                    'rut' => '55555555-5',
                    'name' => 'Delegado de Seguridad de Demostración',
                    'email' => 'delegado.demo@giatep.local',
                    'job_title' => 'Delegado de Seguridad',
                    'role' => 'delegado',
                ],
                [
                    'rut' => '11111111-1',
                    'name' => 'Prevencionista de Demostración',
                    'email' => 'prevencionista.demo@giatep.local',
                    'job_title' => 'Prevencionista',
                    'role' => 'prevencion',
                ],
                [
                    'rut' => '22222222-2',
                    'name' => 'Jefatura de Demostración',
                    'email' => 'jefatura.demo@giatep.local',
                    'job_title' => 'Jefatura',
                    'role' => 'jefatura',
                ],
                [
                    'rut' => '33333333-3',
                    'name' => 'Alta Dirección de Demostración',
                    'email' => 'alta.direccion.demo@giatep.local',
                    'job_title' => 'Alta Dirección',
                    'role' => 'alta-direccion',
                ],
            ];

            foreach ($demoUsers as $demoUser) {
                $user = User::updateOrCreate(['rut' => $demoUser['rut']], [
                    'name' => $demoUser['name'],
                    'email' => $demoUser['email'],
                    'job_title' => $demoUser['job_title'],
                    'status' => 'active',
                    'password' => $demoPassword,
                ]);
                $user->roles()->sync([Role::where('slug', $demoUser['role'])->firstOrFail()->id]);
                $user->establishments()->sync([$establishment->id]);
            }
        }

        $password = env('GIATEP_ADMIN_PASSWORD');
        if ((! is_string($password) || strlen($password) < 8) && app()->environment(['local', 'testing'])) {
            $password = $demoPassword;
        }

        if (! is_string($password) || strlen($password) < 8) {
            $this->command?->warn('Define GIATEP_ADMIN_PASSWORD con al menos 8 caracteres para crear el administrador inicial.');

            return;
        }

        $admin = User::updateOrCreate(['rut' => '21200314-K'], [
            'name' => 'Administrador de Demostración', 'email' => 'admin.demo@giatep.local',
            'job_title' => 'Administración de plataforma', 'status' => 'active', 'password' => $password,
        ]);
        $admin->roles()->syncWithoutDetaching(Role::where('slug', 'administrador')->firstOrFail());
    }

    private function seedOriginalSchema(): void
    {
        $dependencyId = DB::table('dependencia')->where('Nombre_Dependencia', 'Dependencia Demostración Central')->value('Id_Dependencia');
        if (! $dependencyId) {
            $dependencyId = DB::table('dependencia')->insertGetId(['Nombre_Dependencia' => 'Dependencia Demostración Central'], 'Id_Dependencia');
        }

        $roles = [
            'delegado' => 'Delegado de Seguridad', 'cphs' => 'Comité Paritario', 'prevencion' => 'Prevencionista',
            'jefatura' => 'Jefatura', 'alta-direccion' => 'Alta Dirección', 'administrador' => 'Administrador',
        ];
        $roleIds = [];
        foreach ($roles as $code => $name) {
            $roleId = DB::table('rol')->where('Nombre_Rol', $name)->value('Id_Rol');
            if (! $roleId) {
                $roleId = DB::table('rol')->insertGetId(['Nombre_Rol' => $name], 'Id_Rol');
            }
            DB::table('rol')->where('Id_Rol', $roleId)->update(['Codigo_Rol' => $code]);
            $roleIds[$code] = $roleId;
        }

        $demoPassword = env('GIATEP_DEMO_PASSWORD');
        if ((! is_string($demoPassword) || strlen($demoPassword) < 8) && app()->environment(['local', 'testing'])) {
            $demoPassword = 'GiatepDemo2026!';
        }
        if (! is_string($demoPassword) || strlen($demoPassword) < 8) {
            $this->command?->warn('Define GIATEP_DEMO_PASSWORD con al menos 8 caracteres para crear los usuarios de prueba.');

            return;
        }

        foreach ([
            ['rut' => '44444444-4', 'name' => 'Comité Paritario de Demostración', 'email' => 'comite.demo@giatep.local', 'job' => 'Representante del Comité Paritario', 'role' => 'cphs'],
            ['rut' => '55555555-5', 'name' => 'Delegado de Seguridad de Demostración', 'email' => 'delegado.demo@giatep.local', 'job' => 'Delegado de Seguridad', 'role' => 'delegado'],
            ['rut' => '11111111-1', 'name' => 'Prevencionista de Demostración', 'email' => 'prevencionista.demo@giatep.local', 'job' => 'Prevencionista', 'role' => 'prevencion'],
            ['rut' => '22222222-2', 'name' => 'Jefatura de Demostración', 'email' => 'jefatura.demo@giatep.local', 'job' => 'Jefatura', 'role' => 'jefatura'],
            ['rut' => '33333333-3', 'name' => 'Alta Dirección de Demostración', 'email' => 'alta.direccion.demo@giatep.local', 'job' => 'Alta Dirección', 'role' => 'alta-direccion'],
            ['rut' => '21200314-K', 'name' => 'Administrador de Demostración', 'email' => 'admin.demo@giatep.local', 'job' => 'Administración de plataforma', 'role' => 'administrador'],
        ] as $demoUser) {
            [$first, $last] = array_pad(preg_split('/\s+/', trim($demoUser['name']), 2) ?: [], 2, '');
            DB::table('usuario')->updateOrInsert(['Rut_Usuario' => $demoUser['rut']], [
                'Contrasena_Hash' => Hash::make($demoPassword), 'Correo' => $demoUser['email'], 'Id_Dependencia' => $dependencyId,
                'Nombre' => $first, 'Apellido' => $last, 'Cargo' => $demoUser['job'], 'Estado' => 'active',
            ]);
            DB::table('rol_usuario')->updateOrInsert(['Id_Rol' => $roleIds[$demoUser['role']], 'Rut_Usuario' => $demoUser['rut']], []);
        }
    }
}
