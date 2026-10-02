<?php

namespace Tests\Feature\Modules\Cases;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CasePagesTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{string, string}> */
    public static function caseCreatorRoles(): array
    {
        return [
            'comite' => ['cphs', 'Comité Paritario'],
            'delegado' => ['delegado', 'Delegado de Seguridad'],
            'prevencionista' => ['prevencion', 'Prevencionista'],
        ];
    }

    #[DataProvider('caseCreatorRoles')]
    public function test_authorized_operational_role_can_open_case_list_and_creation_form(string $roleSlug, string $roleName): void
    {
        $user = $this->userWithRole($roleSlug, $roleName);

        $this->actingAs($user)
            ->get(route('modules.cases.index'))
            ->assertOk()
            ->assertSee('Casos registrados')
            ->assertSee('Registrar caso')
            ->assertSee('Previsualizar')
            ->assertSee('Iniciar investigación')
            ->assertSee(route('modules.investigations.index', ['case' => 'GIATEP-2026-001']), false);

        $this->actingAs($user)
            ->get(route('modules.cases.create'))
            ->assertOk()
            ->assertSee('Datos del empleador')
            ->assertSee('Datos de la persona accidentada')
            ->assertSee('Relato del caso y propuesta asistida')
            ->assertSee('Medidas de control y plan de acción')
            ->assertSee('Agregar medida al plan')
            ->assertSee('Recopilación del caso');
    }

    public function test_role_without_case_permission_cannot_open_case_pages(): void
    {
        $user = $this->userWithRole('jefatura', 'Jefatura Directa');

        $this->actingAs($user)->get(route('modules.cases.index'))->assertForbidden();
        $this->actingAs($user)->get(route('modules.cases.create'))->assertForbidden();
    }

    public function test_unauthenticated_user_is_redirected_from_case_pages(): void
    {
        $this->get(route('modules.cases.index'))->assertRedirect(route('login'));
        $this->get(route('modules.cases.create'))->assertRedirect(route('login'));
    }

    private function userWithRole(string $slug, string $name): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::factory()->create(compact('slug', 'name')));

        return $user;
    }
}
