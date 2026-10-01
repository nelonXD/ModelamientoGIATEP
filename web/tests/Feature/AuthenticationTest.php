<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_login_displays_demo_credentials_in_testing_environment(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Primero ingresa tu RUT para continuar.')
            ->assertSee('data-login-continue', false)
            ->assertSee('data-login-password-step', false)
            ->assertSee('Credenciales de prueba')
            ->assertSee('21.200.314-K')
            ->assertSee('44.444.444-4')
            ->assertSee('55.555.555-5')
            ->assertSee('GiatepDemo2026!');
    }

    public function test_active_user_can_log_in_with_normalized_rut_and_log_out(): void
    {
        $user = User::factory()->create(['rut' => '12345678-5', 'password' => 'secret123']);

        $this->post('/login', ['rut' => '123456785', 'password' => 'secret123'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        $this->post('/cerrar-sesion')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_password_step_accepts_only_an_active_registered_rut(): void
    {
        User::factory()->create(['rut' => '12345678-5', 'status' => 'active']);

        $this->postJson('/login/verificar-rut', ['rut' => '12.345.678-5'])
            ->assertOk()
            ->assertJson(['registered' => true]);

        $this->postJson('/login/verificar-rut', ['rut' => '11.111.111-1'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('rut');
    }

    public function test_password_step_rejects_an_inactive_registered_rut(): void
    {
        User::factory()->create(['rut' => '12345678-5', 'status' => 'pending']);

        $this->postJson('/login/verificar-rut', ['rut' => '12.345.678-5'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('rut');
    }

    public function test_pending_user_cannot_log_in(): void
    {
        User::factory()->create(['status' => 'pending', 'password' => 'secret123']);

        $this->post('/login', ['rut' => '12.345.678-5', 'password' => 'secret123'])
            ->assertSessionHasErrors('rut');
        $this->assertGuest();
    }
}
