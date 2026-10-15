<?php

namespace Tests\Feature\Modules\Cases;

use App\Models\Establishment;
use App\Models\Investigation;
use App\Models\OccupationalCase;
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
        $this->occupationalCase($user);

        $this->actingAs($user)
            ->get(route('modules.cases.index'))
            ->assertOk()
            ->assertSee('Casos registrados')
            ->assertSee('Registrar caso')
            ->assertSee('Ver antecedentes')
            ->assertSee('Editar tipo de caso')
            ->assertSee('Tipo de caso')
            ->assertSee('¿Deseas continuar?')
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

    public function test_observations_button_is_available_to_an_authorized_role(): void
    {
        $user = $this->userWithRole('delegado', 'Delegado de Seguridad');
        $this->occupationalCase($user);

        $this->actingAs($user)
            ->get(route('modules.cases.index'))
            ->assertOk()
            ->assertSee('Observaciones')
            ->assertSee(route('modules.observations.index', ['case' => 'GIATEP-2026-001']), false);
    }

    public function test_case_with_initiated_investigation_has_investigation_button_disabled(): void
    {
        $user = $this->userWithRole('delegado', 'Delegado de Seguridad');
        $case = $this->occupationalCase($user);

        Investigation::create([
            'folio' => 'INV-2026-001',
            'occupational_case_id' => $case->id,
            'owner_id' => $user->id,
            'status' => 'En investigación',
            'final_story' => $case->narrative,
        ]);
        $case->update(['status' => 'En investigación']);

        $this->actingAs($user)
            ->get(route('modules.cases.index'))
            ->assertOk()
            ->assertSee('data-investigation-disabled', false)
            ->assertSee('disabled', false)
            ->assertSee('Ver expediente en curso (INV-2026-001)')
            ->assertDontSee('href="'.route('modules.investigations.index', ['case' => 'GIATEP-2026-001', 'case_type' => 'Accidente del trabajo']).'"', false);
    }

    public function test_start_investigation_opens_editable_form_for_selected_case(): void
    {
        $user = $this->userWithRole('delegado', 'Delegado de Seguridad');
        $this->occupationalCase($user);

        $this->actingAs($user)
            ->get(route('modules.investigations.index', ['case' => 'GIATEP-2026-001']))
            ->assertOk()
            ->assertSee('INV-NUEVA')
            ->assertSee('Información recuperada del caso GIATEP-2026-001')
            ->assertSee('Entorno de pruebas guiado')
            ->assertSee('Completitud metodológica')
            ->assertSee('Recopilación')
            ->assertSee('Construcción del árbol')
            ->assertSee('Administración')
            ->assertSee('Evidencia testimonial registrada')
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
            ->assertSee('Control de calidad del relato')
            ->assertSee('Por qué verificable')
            ->assertSee('data-story-quality', false)
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
        $this->occupationalCase($user);

        $this->actingAs($user)
            ->get(route('modules.investigations.index', [
                'case' => 'GIATEP-2026-001',
                'case_type' => 'Enfermedad profesional',
            ]))
            ->assertOk()
            ->assertSee('value="Enfermedad profesional"', false);
    }

    public function test_nonexistent_browser_demo_case_is_rejected(): void
    {
        $user = $this->userWithRole('prevencion', 'Prevencionista');

        $this->actingAs($user)
            ->get(route('modules.investigations.index', [
                'case' => 'GIATEP-DEMO-1234567',
                'case_type' => 'Accidente de trabajo',
            ]))
            ->assertNotFound();
    }

    public function test_authorized_user_can_store_a_case_in_the_database(): void
    {
        $user = $this->userWithRole('prevencion', 'Prevencionista');

        $response = $this->actingAs($user)->post(route('modules.cases.store'), [
            'employer_name' => 'Dirección Comunal de Salud', 'employer_rut' => '69.170.102-6',
            'employer_address' => 'Avenida Central 1250', 'employer_commune' => 'Los Ángeles',
            'economic_activity' => 'Salud pública', 'company_type' => 'Principal', 'administrator' => 'ACHS',
            'establishment' => 'CESFAM Norte', 'worker_name' => 'Camila Soto Rojas',
            'worker_rut' => '17.456.321-8', 'job_title' => 'Técnica en enfermería', 'workplace' => 'Bodega clínica',
            'case_type' => 'Accidente de trabajo', 'event_date' => now()->toDateString(), 'event_time' => '09:15',
            'event_location' => 'Pasillo de acceso', 'narrative' => 'La trabajadora informó una caída durante el traslado de insumos.',
        ]);

        $response->assertRedirect(route('modules.cases.index'));
        $this->assertDatabaseHas('occupational_cases', [
            'folio' => 'GIATEP-'.now()->year.'-001', 'worker_rut' => '17.456.321-8', 'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('modules.cases.index'))
            ->assertOk()
            ->assertSee('GIATEP-'.now()->year.'-001');
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

    private function occupationalCase(User $user): OccupationalCase
    {
        $establishment = Establishment::factory()->create(['name' => 'CESFAM Norte']);

        return OccupationalCase::create([
            'folio' => 'GIATEP-2026-001', 'created_by' => $user->id, 'establishment_id' => $establishment->id,
            'status' => 'Registrado', 'case_type' => 'Accidente del trabajo',
            'employer_name' => 'Dirección Comunal de Salud', 'employer_rut' => '69.170.102-6',
            'employer_address' => 'Avenida Central 1250', 'employer_commune' => 'Los Ángeles',
            'economic_activity' => 'Salud pública', 'company_type' => 'Principal', 'administrator' => 'ACHS',
            'worker_name' => 'Camila Soto Rojas', 'worker_rut' => '17.456.321-8',
            'job_title' => 'Técnica en enfermería', 'workplace' => 'Bodega clínica',
            'event_date' => '2026-10-02', 'event_time' => '09:15', 'event_location' => 'Pasillo de acceso',
            'narrative' => 'Durante el traslado de insumos, la trabajadora informó una caída en una superficie húmeda.',
        ]);
    }
}
