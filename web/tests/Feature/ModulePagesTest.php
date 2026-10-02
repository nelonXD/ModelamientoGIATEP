<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ModulePagesTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{string, string, string, string}> */
    public static function accessibleModules(): array
    {
        return [
            'casos' => ['delegado', 'Delegado de Seguridad', 'modules.cases.index', 'Casos'],
            'investigaciones' => ['delegado', 'Delegado de Seguridad', 'modules.investigations.index', 'Investigaciones'],
            'revisiones' => ['prevencion', 'Prevencionista', 'modules.reviews.index', 'Revisiones y validaciones'],
            'medidas' => ['jefatura', 'Jefatura Directa', 'modules.measures.index', 'Medidas y planes de acción'],
            'observaciones' => ['jefatura', 'Jefatura Directa', 'modules.observations.index', 'Observaciones'],
            'estadisticas' => ['alta-direccion', 'Alta Dirección', 'modules.statistics.index', 'Dashboards y estadísticas'],
            'reportes' => ['alta-direccion', 'Alta Dirección', 'modules.reports.index', 'Reportes'],
            'perfil' => ['delegado', 'Delegado de Seguridad', 'modules.profile.index', 'Mi perfil'],
            'usuarios' => ['administrador', 'Administrador', 'admin.users.index', 'Usuarios'],
            'roles' => ['administrador', 'Administrador', 'admin.roles.index', 'Roles y permisos'],
            'establecimientos' => ['administrador', 'Administrador', 'admin.establishments.index', 'Establecimientos'],
            'parametros' => ['administrador', 'Administrador', 'admin.settings.index', 'Parámetros institucionales'],
        ];
    }

    #[DataProvider('accessibleModules')]
    public function test_authorized_role_can_open_module_page(string $roleSlug, string $roleName, string $routeName, string $title): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::factory()->create(['slug' => $roleSlug, 'name' => $roleName]));

        $this->actingAs($user)
            ->get(route($routeName))
            ->assertOk()
            ->assertSee($title)
            ->assertSee('Base de diseño');
    }

    public function test_role_without_permission_cannot_open_module_page(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::factory()->create([
            'slug' => 'delegado',
            'name' => 'Delegado de Seguridad',
        ]));

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }
}
