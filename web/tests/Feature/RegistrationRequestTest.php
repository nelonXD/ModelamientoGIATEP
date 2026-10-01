<?php

namespace Tests\Feature;

use App\Models\Establishment;
use App\Models\RegistrationRequest;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationRequestTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_valid_request_is_stored_pending_with_hashed_password(): void
    {
        $establishment = Establishment::factory()->create();
        $payload = $this->validPayload($establishment);

        $this->post('/solicitar-acceso', $payload)->assertRedirect(route('register.success'));

        $request = RegistrationRequest::firstOrFail();
        $this->assertSame('12345678-5', $request->rut);
        $this->assertSame('pending', $request->status);
        $this->assertTrue(Hash::check('secret123', $request->password));
    }

    public function test_duplicate_pending_request_is_rejected(): void
    {
        $establishment = Establishment::factory()->create();
        RegistrationRequest::factory()->create(['rut' => '12345678-5', 'pending_key' => '12345678-5', 'establishment_id' => $establishment]);

        $this->post('/solicitar-acceso', $this->validPayload($establishment))->assertSessionHasErrors('rut');
        $this->assertSame(1, RegistrationRequest::count());
    }

    public function test_invalid_rut_and_private_role_are_rejected(): void
    {
        $establishment = Establishment::factory()->create();
        $payload = [...$this->validPayload($establishment), 'rut' => '12.345.678-4', 'requested_role' => 'administrador'];

        $this->post('/solicitar-acceso', $payload)->assertSessionHasErrors(['rut', 'requested_role']);
        $this->assertDatabaseCount('registration_requests', 0);
    }

    private function validPayload(Establishment $establishment): array
    {
        return ['rut' => '12.345.678-5', 'name' => 'Persona Demostración', 'job_title' => 'Técnico',
            'establishment_id' => $establishment->id, 'requested_role' => 'delegado',
            'email' => 'persona@example.test', 'phone' => '+56 9 0000 0000',
            'password' => 'secret123', 'password_confirmation' => 'secret123'];
    }
}
