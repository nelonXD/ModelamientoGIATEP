<?php

namespace Tests\Feature;

use App\Models\RegistrationRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminRegistrationRequestTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_non_admin_cannot_access_registration_requests(): void
    {
        $this->actingAs(User::factory()->create())->get('/administracion/solicitudes')->assertForbidden();
    }

    public function test_admin_can_approve_request_once_and_assign_scope(): void
    {
        $admin = $this->admin();
        $role = Role::factory()->create(['slug' => 'delegado', 'name' => 'Delegado de Seguridad']);
        $registration = RegistrationRequest::factory()->create();

        $this->actingAs($admin)->patch(route('admin.registration-requests.update', $registration), ['action' => 'approve'])
            ->assertRedirect(route('admin.registration-requests.index'));

        $user = User::where('rut', $registration->rut)->firstOrFail();
        $this->assertTrue($user->roles->contains($role));
        $this->assertTrue($user->establishments->contains($registration->establishment_id));
        $this->assertDatabaseHas('registration_requests', ['id' => $registration->id, 'status' => 'approved', 'resolved_by' => $admin->id, 'pending_key' => null]);

        $this->actingAs($admin)->patch(route('admin.registration-requests.update', $registration), ['action' => 'approve'])->assertConflict();
    }

    public function test_rejection_requires_reason_and_records_resolution(): void
    {
        $admin = $this->admin();
        $registration = RegistrationRequest::factory()->create();

        $this->actingAs($admin)->patch(route('admin.registration-requests.update', $registration), ['action' => 'reject'])->assertSessionHasErrors('rejection_reason');
        $this->actingAs($admin)->patch(route('admin.registration-requests.update', $registration), ['action' => 'reject', 'rejection_reason' => 'Antecedentes insuficientes'])->assertRedirect();

        $this->assertDatabaseHas('registration_requests', ['id' => $registration->id, 'status' => 'rejected', 'rejection_reason' => 'Antecedentes insuficientes', 'resolved_by' => $admin->id]);
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::factory()->create(['slug' => 'administrador', 'name' => 'Administrador']));

        return $admin;
    }
}
