<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class RoleMenuTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_administrator_menu_excludes_cases_and_includes_access_requests(): void
    {
        $user = $this->userWithRole('administrador', 'Administrador');

        $this->actingAs($user)->get('/inicio')->assertSee('Solicitudes de registro')->assertDontSee('Casos');
    }

    public function test_prevention_menu_includes_statistics_and_reports(): void
    {
        $user = $this->userWithRole('prevencion', 'Prevencionista');

        $this->actingAs($user)->get('/inicio')
            ->assertSee('Prevencionista')
            ->assertSee('Revisiones y validaciones')
            ->assertSee('Dashboards y estadísticas')
            ->assertSee('Reportes');
    }

    public function test_direct_management_dashboard_excludes_global_statistics(): void
    {
        $user = $this->userWithRole('jefatura', 'Jefatura');

        $this->actingAs($user)->get('/inicio')
            ->assertSee('Jefatura')
            ->assertSee('Investigaciones')
            ->assertSee('Observaciones')
            ->assertDontSee('Dashboards y estadísticas')
            ->assertDontSee('Reportes');
    }

    public function test_senior_management_dashboard_includes_global_statistics(): void
    {
        $user = $this->userWithRole('alta-direccion', 'Alta Dirección');

        $this->actingAs($user)->get('/inicio')
            ->assertSee('Alta Dirección')
            ->assertSee('Investigaciones')
            ->assertSee('Dashboards y estadísticas')
            ->assertSee('Reportes')
            ->assertDontSee('Revisiones y validaciones');
    }

    public function test_delegate_menu_excludes_global_statistics(): void
    {
        $user = $this->userWithRole('delegado', 'Delegado de Seguridad');

        $this->actingAs($user)->get('/inicio')->assertSee('Casos')->assertDontSee('Dashboards y estadísticas');
    }

    private function userWithRole(string $slug, string $name): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::factory()->create(compact('slug', 'name')));

        return $user;
    }
}
