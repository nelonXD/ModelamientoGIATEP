<?php

namespace Tests\Feature;

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

    public function test_user_cannot_open_demo_module_without_permission(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::factory()->create(['slug' => 'delegado', 'name' => 'Delegado']));

        $this->actingAs($user)
            ->get(route('demo.show', ['module' => 'users']))
            ->assertForbidden();
    }
}
