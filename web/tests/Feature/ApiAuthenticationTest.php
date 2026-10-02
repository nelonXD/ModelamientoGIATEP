<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiAuthenticationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_active_user_can_create_use_and_revoke_an_api_token(): void
    {
        $user = User::factory()->create([
            'rut' => '21200314-K',
            'password' => 'admin123',
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'rut' => '21.200.314-k',
            'password' => 'admin123',
            'device_name' => 'Pruebas',
        ]);

        $loginResponse
            ->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.rut', '21200314-K')
            ->assertJsonStructure(['access_token', 'token_type', 'user' => ['id', 'rut', 'name', 'email', 'status']]);

        $token = $loginResponse->json('access_token');
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'Pruebas',
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.rut', '21200314-K');

        $this->withToken($token)
            ->deleteJson('/api/v1/auth/token')
            ->assertNoContent();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);

        Auth::guard('sanctum')->forgetUser();

        $this->withToken($token)
            ->getJson('/api/v1/me')
            ->assertUnauthorized();
    }

    public function test_login_rejects_invalid_credentials_with_422(): void
    {
        User::factory()->create([
            'rut' => '21200314-K',
            'password' => 'admin123',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'rut' => '21200314-K',
            'password' => 'incorrecta',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('rut')
            ->assertJsonPath('errors.rut.0', 'Las credenciales no coinciden o la cuenta aún no está habilitada.');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_login_rejects_inactive_user_with_422(): void
    {
        User::factory()->create([
            'rut' => '21200314-K',
            'password' => 'admin123',
            'status' => 'pending',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'rut' => '21200314-K',
            'password' => 'admin123',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('rut');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_protected_routes_require_a_valid_token_with_401(): void
    {
        $this->getJson('/api/v1/me')->assertUnauthorized();
        $this->getJson('/api/v1/dashboard')->assertUnauthorized();
        $this->deleteJson('/api/v1/auth/token')->assertUnauthorized();
    }

    public function test_authenticated_user_can_get_role_filtered_dashboard_data(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::factory()->create([
            'slug' => 'prevencion',
            'name' => 'Prevencionista',
        ]));
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('profile.label', 'Prevencionista')
            ->assertJsonFragment(['module' => 'estadisticas'])
            ->assertJsonFragment(['module' => 'reportes']);
    }
}
