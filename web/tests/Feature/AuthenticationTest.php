<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_active_user_can_log_in_with_normalized_rut_and_log_out(): void
    {
        $user = User::factory()->create(['rut' => '12345678-5', 'password' => 'secret123']);

        $this->post('/login', ['rut' => '123456785', 'password' => 'secret123'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        $this->post('/cerrar-sesion')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_pending_user_cannot_log_in(): void
    {
        User::factory()->create(['status' => 'pending', 'password' => 'secret123']);

        $this->post('/login', ['rut' => '12.345.678-5', 'password' => 'secret123'])
            ->assertSessionHasErrors('rut');
        $this->assertGuest();
    }
}
