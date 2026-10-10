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
            ->assertSee('Ver antecedentes')
            ->assertSee('Cambiar tipo')
            ->assertSee('Se debe actualizar el formulario del caso')
            ->assertSee('Formulario por actualizar')
            ->assertSee('Evidencias adjuntas')
            ->assertSee('Iniciar investigación')
            ->assertSee(route('modules.investigations.index', ['case' => 'GIATEP-2026-001']), false);

        $this->actingAs($user)
            ->get(route('modules.cases.create'))
            ->assertOk()
            ->assertSee('Datos del empleador')
            ->assertSee('Actividad económica')
            ->assertSee('Datos de la persona accidentada')
            ->assertSee('Pertenencia a pueblo originario')
            ->assertSee('Tipo de contrato')
            ->assertSee('Categoría ocupacional')
            ->assertSee('Antecedentes del caso')
            ->assertSee('Enfermedad profesional')
            ->assertSee('Datos del accidente o incidente')
            ->assertSee('Datos de la enfermedad profesional')
            ->assertSee('data-case-classification', false)
            ->assertSee('data-accident-report', false)
            ->assertSee('data-disease-report', false)
            ->assertSee('Evidencias del caso')
            ->assertSee('data-evidence-dropzone', false)
            ->assertSee('data-evidence-list', false)
            ->assertDontSee('Agregar medida inicial')
            ->assertDontSee('Propuesta de análisis con IA')
            ->assertSee('Recopilación del caso')
            ->assertSee("wizard.addEventListener('click'", false)
            ->assertSee('showStep(currentStep + 1)', false);
    }

    public function test_role_without_case_permission_cannot_open_case_pages(): void
    {
        $user = $this->userWithRole('jefatura', 'Jefatura Directa');

        $this->actingAs($user)->get(route('modules.cases.index'))->assertForbidden();
        $this->actingAs($user)->get(route('modules.cases.create'))->assertForbidden();
    }

    public function test_start_investigation_opens_editable_form_for_selected_case(): void
    {
        $user = $this->userWithRole('delegado', 'Delegado de Seguridad');

        $this->actingAs($user)
            ->get(route('modules.investigations.index', ['case' => 'GIATEP-2026-001']))
            ->assertOk()
            ->assertSee('INV-NUEVA')
            ->assertSee('Información recuperada del caso GIATEP-2026-001')
            ->assertSee('name="worker_name"', false)
            ->assertSee('value="Camila Soto Rojas"', false)
            ->assertSee('Entrevistas')
            ->assertSee('Crear entrevista')
            ->assertSee('Crear entrevista anterior')
            ->assertSee('data-legacy-questionnaire', false)
            ->assertSee('Formulario de entrevista')
            ->assertSee('data-interview-validation', false)
            ->assertSee('Factores musculoesqueléticos')
            ->assertSee('Organización del trabajo')
            ->assertSee('Analizar relato final')
            ->assertSee('Debe ser redactado y revisado por el prevencionista')
            ->assertSee('Hechos detectados')
            ->assertSee('Árbol de causas propuesto')
            ->assertSee('Medidas propuestas')
            ->assertSee('Guardar propuesta')
            ->assertSee('data-final-ai-result', false)
            ->assertSee('data-ai-derived-nav', false)
            ->assertDontSee('Propuesta IA');
    }

    public function test_changed_case_type_is_forwarded_to_the_investigation(): void
    {
        $user = $this->userWithRole('prevencion', 'Prevencionista');

        $this->actingAs($user)
            ->get(route('modules.investigations.index', [
                'case' => 'GIATEP-2026-001',
                'case_type' => 'Enfermedad profesional',
            ]))
            ->assertOk()
            ->assertSee('value="Enfermedad profesional"', false);
    }

    public function test_browser_case_can_start_a_linked_investigation(): void
    {
        $user = $this->userWithRole('prevencion', 'Prevencionista');

        $this->actingAs($user)
            ->get(route('modules.investigations.index', [
                'case' => 'GIATEP-DEMO-1234567',
                'case_type' => 'Accidente de trabajo',
            ]))
            ->assertOk()
            ->assertSee('Información recuperada del caso GIATEP-DEMO-1234567')
            ->assertSee('value="Accidente de trabajo"', false);
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
