<?php

namespace Tests\Feature;

use App\Models\Establishment;
use App\Models\Investigation;
use App\Models\OccupationalCase;
use App\Models\Role;
use App\Models\User;
use App\Support\DemoWorkspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_open_related_demo_detail(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::factory()->create(['slug' => 'delegado', 'name' => 'Delegado']));
        $this->createDemoCaseAndInvestigation($user);

        $this->actingAs($user)
            ->get(route('demo.show', ['module' => 'cases', 'id' => 'CAS-2026-017']))
            ->assertOk()
            ->assertSee('CAS-2026-017')
            ->assertSee('INV-2026-011');
    }

    public function test_demo_action_is_stored_only_in_session_and_can_be_reset(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::factory()->create(['slug' => 'delegado', 'name' => 'Delegado']));

        $this->actingAs($user)
            ->post(route('demo.action', 'cases'), ['action' => 'Borrador guardado'])
            ->assertSessionHas(DemoWorkspace::SESSION_KEY);

        $this->post(route('demo.reset'))
            ->assertSessionMissing(DemoWorkspace::SESSION_KEY)
            ->assertSessionHas('demo_status');
    }

    public function test_investigation_interview_includes_case_witness_and_not_applicable_options(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::factory()->create(['slug' => 'delegado', 'name' => 'Delegado']));
        $this->createDemoCaseAndInvestigation($user);

        $this->actingAs($user)
            ->get(route('demo.show', ['module' => 'investigations', 'id' => 'INV-2026-011']))
            ->assertOk()
            ->assertSee('Testigo presencial directo')
            ->assertSee('Testigo documental')
            ->assertSee('4. Reconstrucción cronológica de los hechos')
            ->assertSee('chronology_not_applicable', false)
            ->assertSee('7. Equipos de protección personal')
            ->assertSee('8. Información y capacitación')
            ->assertSee('No aplica');
    }

    private function createDemoCaseAndInvestigation(User $user): void
    {
        $establishment = Establishment::firstOrCreate(
            ['name' => 'CESFAM Nororiente'],
            ['type' => 'CESFAM', 'is_active' => true]
        );

        $case = OccupationalCase::create([
            'folio' => 'CAS-2026-017',
            'created_by' => $user->id,
            'establishment_id' => $establishment->id,
            'status' => 'En investigación',
            'case_type' => 'Accidente del trabajo',
            'employer_name' => 'Dirección Comunal de Salud de Los Ángeles',
            'employer_rut' => '69.170.102-6',
            'employer_address' => 'Calle Valdivia #150, Los Ángeles',
            'employer_commune' => 'Los Ángeles',
            'economic_activity' => 'Salud pública',
            'company_type' => 'Principal',
            'administrator' => 'ACHS',
            'worker_name' => 'Camila Soto Rojas',
            'worker_rut' => '17.456.321-8',
            'job_title' => 'Técnica en enfermería',
            'workplace' => 'Bodega clínica',
            'event_date' => '2026-10-02',
            'event_time' => '09:15',
            'event_location' => 'Pasillo de acceso',
            'narrative' => 'Durante el traslado de insumos, la trabajadora sufrió una caída.',
            'witnesses' => 'Juan Pérez (Testigo directo)',
        ]);

        Investigation::create([
            'folio' => 'INV-2026-011',
            'occupational_case_id' => $case->id,
            'owner_id' => $user->id,
            'status' => 'En desarrollo',
            'final_story' => 'Relato final de la investigación',
        ]);
    }

    public function test_user_cannot_open_demo_module_without_permission(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::factory()->create(['slug' => 'delegado', 'name' => 'Delegado']));

        $this->actingAs($user)
            ->get(route('demo.show', ['module' => 'users']))
            ->assertForbidden();
    }
}
