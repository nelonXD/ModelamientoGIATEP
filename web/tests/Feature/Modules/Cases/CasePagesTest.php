<?php

namespace Tests\Feature\Modules\Cases;

use App\Models\Establishment;
use App\Models\Investigation;
use App\Models\OccupationalCase;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\CaseCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CasePagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CaseCatalogSeeder::class);
    }

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
            ->assertSee('Doble clic para ver antecedentes')
            ->assertSee('Editar caso')
            ->assertSee('Ver evidencia')
            ->assertSee(route('modules.cases.evidence.index', ['case' => 'GIATEP-2026-001']), false)
            ->assertSee(route('modules.cases.edit', ['case' => 'GIATEP-2026-001']), false)
            ->assertSee('Editar tipo de caso')
            ->assertSee('Tipo de caso')
            ->assertSee('¿Deseas continuar?')
            ->assertSee('Se debe actualizar el formulario del caso')
            ->assertSee('Formulario por actualizar')
            ->assertSee('Evidencias adjuntas')
            ->assertSee('Iniciar investigación')
            ->assertSee(route('modules.investigations.start', ['case' => 'GIATEP-2026-001']), false);

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
            ->assertSee('Así quedará registrado en el sistema')
            ->assertSee('Registro real · no solo vista previa')
            ->assertSee("name='establishment_id'", false)
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

    public function test_authorized_role_can_edit_and_update_a_case(): void
    {
        Storage::fake('local');
        $user = $this->userWithRole('prevencion', 'Prevencionista');
        $case = $this->occupationalCase($user);

        $this->actingAs($user)
            ->get(route('modules.cases.edit', ['case' => $case->folio]))
            ->assertOk()
            ->assertSee('Editar caso GIATEP-2026-001')
            ->assertSee("value='Camila Soto Rojas'", false)
            ->assertDontSee('Agregar nueva evidencia')
            ->assertSee('Guardar cambios');

        $response = $this->actingAs($user)->patch(route('modules.cases.update', ['case' => $case->folio]), [
            'employer_name' => 'Dirección Comunal de Salud',
            'employer_rut' => '69.170.102-6',
            'employer_address' => 'Avenida Central 1250',
            'employer_commune' => 'Los Ángeles',
            'economic_activity' => 'Salud pública',
            'company_type' => 'Principal',
            'administrator' => 'ACHS',
            'establishment_id' => $case->establishment_id,
            'worker_name' => 'Camila Soto Actualizada',
            'worker_rut' => '17.456.321-8',
            'job_title' => 'Técnica en enfermería',
            'workplace' => 'Urgencias',
            'case_type' => 'Accidente de trabajo',
            'event_date' => now()->subDay()->toDateString(),
            'event_time' => '10:30',
            'event_location' => 'Acceso principal',
            'narrative' => 'Relato actualizado con los nuevos antecedentes disponibles.',
        ]);

        $response->assertRedirect(route('modules.cases.index'));
        $this->assertDatabaseHas('occupational_cases', [
            'id' => $case->id,
            'worker_name' => 'Camila Soto Actualizada',
            'workplace' => 'Urgencias',
            'event_location' => 'Acceso principal',
        ]);

        $this->actingAs($user)
            ->get(route('modules.cases.evidence.index', ['case' => $case->folio]))
            ->assertOk()
            ->assertSee('Evidencias del caso')
            ->assertSee('Agregar nueva evidencia')
            ->assertDontSee('Datos del empleador');

        $this->actingAs($user)->post(route('modules.cases.evidence.store', ['case' => $case->folio]), [
            'evidence_files' => [UploadedFile::fake()->create('informe-medico.pdf', 100, 'application/pdf')],
        ])->assertRedirect(route('modules.cases.evidence.index', ['case' => $case->folio]));

        $evidence = $case->evidences()->sole();
        Storage::disk('local')->assertExists($evidence->path);

        $this->actingAs($user)
            ->get(route('modules.cases.evidence.download', ['case' => $case->folio, 'evidence' => $evidence->id]))
            ->assertOk()
            ->assertDownload('informe-medico.pdf');
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

    public function test_case_with_initiated_investigation_only_shows_the_active_investigation_link(): void
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
            ->assertDontSee('data-investigation-disabled', false)
            ->assertDontSee('title="Este caso ya cuenta con una investigación iniciada', false)
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
            ->assertDontSee('Entorno de pruebas guiado')
            ->assertDontSee('Completitud metodológica')
            ->assertDontSee('Evidencia testimonial registrada')
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
            ->assertSee('Generar borrador con el modelo seleccionado')
            ->assertSee('El texto generado es solo una propuesta')
            ->assertDontSee('Control de calidad del relato')
            ->assertDontSee('data-story-quality', false)
            ->assertSee('Hechos del relato')
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
        $establishment = Establishment::factory()->create(['name' => 'CESFAM Norte']);

        $response = $this->actingAs($user)->post(route('modules.cases.store'), [
            'employer_name' => 'Dirección Comunal de Salud', 'employer_rut' => '69.170.102-6',
            'employer_address' => 'Avenida Central 1250', 'employer_commune' => 'Los Ángeles',
            'economic_activity' => 'Salud pública', 'company_type' => 'Principal', 'administrator' => 'ACHS',
            'establishment_id' => $establishment->id, 'worker_name' => 'Camila Soto Rojas',
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

    public function test_authorized_user_can_store_a_case_with_extended_details(): void
    {
        $user = $this->userWithRole('prevencion', 'Prevencionista');
        $establishment = Establishment::factory()->create(['name' => 'CESFAM Nororiente']);

        $response = $this->actingAs($user)->post(route('modules.cases.store'), [
            'employer_name' => 'CMPC',
            'employer_rut' => '69.170.100-K',
            'employer_address' => 'Balmaceda 722',
            'employer_commune' => 'Laja',
            'employer_phone' => '936606218',
            'economic_activity' => 'Salud municipal',
            'company_type' => 'Principal',
            'administrator' => 'ACHS',
            'establishment_id' => $establishment->id,
            'worker_name' => 'Claudia Rubilar',
            'worker_rut' => '21.312.312-3',
            'worker_address' => 'Balmaceda 722',
            'worker_commune' => 'Laja',
            'phone' => '936606218',
            'worker_sex' => 'Hombre',
            'birth_date' => '1994-07-15',
            'nationality' => 'Chilena',
            'job_title' => 'Supervisor',
            'workplace' => 'Cesfam Nororiente',
            'contract_type' => 'Indefinido',
            'occupational_category' => 'Trabajador dependiente',
            'hire_date' => '2023-06-15',
            'income_type' => 'Remuneración fija',
            'work_schedule' => 'Diurna',
            'indigenous_people' => 'Ninguno',
            'case_type' => 'Accidente de trabajo',
            'event_date' => '2026-10-15',
            'event_time' => '12:30',
            'event_location' => 'Box Odontológico Cesfam Nororiente',
            'narrative' => 'Relato detallado del evento ocurrido en box odontológico.',
            'witnesses' => 'Testigo 1',
        ]);

        $response->assertRedirect(route('modules.cases.index'));
        $createdCase = OccupationalCase::query()->where('worker_rut', '21.312.312-3')->first();
        $this->assertNotNull($createdCase);
        $this->assertSame('Remuneración fija', $createdCase->details['income_type']);
        $this->assertSame('Diurna', $createdCase->details['work_schedule']);
        $this->assertSame('Ninguno', $createdCase->details['indigenous_people']);
        $this->assertSame('936606218', $createdCase->worker_phone);
    }

    public function test_authorized_user_can_store_a_disease_case_with_disease_details(): void
    {
        $user = $this->userWithRole('prevencion', 'Prevencionista');
        $establishment = Establishment::factory()->create(['name' => 'CESFAM Nororiente']);

        $response = $this->actingAs($user)->post(route('modules.cases.store'), [
            'employer_name' => 'Dirección Comunal de Salud',
            'employer_rut' => '69.170.102-6',
            'employer_address' => 'Avenida Central 1250',
            'employer_commune' => 'Los Ángeles',
            'economic_activity' => 'Salud pública',
            'company_type' => 'Principal',
            'administrator' => 'ACHS',
            'establishment_id' => $establishment->id,
            'worker_name' => 'Marcela Gomez',
            'worker_rut' => '15.987.654-2',
            'job_title' => 'Kinesióloga',
            'workplace' => 'Sala de Rehabilitación',
            'case_type' => 'Enfermedad profesional',
            'event_date' => '2026-10-10',
            'narrative' => 'Dolor crónico en hombro derecho tras movimientos repetitivos continuos.',
            'affected_body_part' => 'Hombro derecho',
            'symptom_duration' => '3 meses',
            'previous_symptoms' => 'No',
            'activity_when_symptoms_began' => 'Terapia física con pacientes de alta complejidad',
            'symptom_job_position' => 'Box de Kinesioterapia',
            'coworkers_same_symptoms' => 'Sí',
            'suspected_work_agents' => 'Movimientos repetitivos y posturas forzadas',
            'exposure_duration' => '2 años',
        ]);

        $response->assertRedirect(route('modules.cases.index'));
        $createdCase = OccupationalCase::query()->where('worker_rut', '15.987.654-2')->first();
        $this->assertNotNull($createdCase);
        $this->assertSame('Enfermedad profesional', $createdCase->case_type);
        $this->assertSame('Hombro derecho', $createdCase->details['affected_body_part']);
        $this->assertSame('3 meses', $createdCase->details['symptom_duration']);
        $this->assertSame('Terapia física con pacientes de alta complejidad', $createdCase->details['activity_when_symptoms_began']);
        $this->assertSame('Movimientos repetitivos y posturas forzadas', $createdCase->details['suspected_work_agents']);
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
