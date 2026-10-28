<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FirstInstitutionalAccessTest extends TestCase
{
    use LazilyRefreshDatabase;

    private const USER_RUT = '20404100-8';

    public function test_rut_lookup_finds_and_cleans_an_institutional_user(): void
    {
        $this->fakeDirectoryApi();

        $this->postJson('/login/verificar-rut', ['rut' => '20.404.100-8'])
            ->assertOk()
            ->assertJsonPath('requires_setup', true)
            ->assertJsonPath('profile.name', 'Isabel Andrea Rios Ejemplo')
            ->assertJsonPath('profile.role_name', 'Prevencionista')
            ->assertJsonPath('profile.dependency_name', 'CESFAM Norte > Unidad de Prevención');

        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/users/20404100-8'));
        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/dependencies/510'));
    }

    public function test_first_institutional_access_creates_local_credentials_and_role(): void
    {
        $role = Role::factory()->create(['slug' => 'prevencion', 'name' => 'Prevencionista']);
        $this->fakeDirectoryApi();

        $this->post('/login/primer-ingreso', [
            'rut' => self::USER_RUT,
            'email' => ' Isabel.Rios@GIATEP.local ',
            'password' => 'ClaveTemporal2026!',
            'password_confirmation' => 'ClaveTemporal2026!',
        ])->assertRedirect(route('dashboard'));

        $user = User::query()->where('rut', self::USER_RUT)->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('isabel.rios@giatep.local', $user->email);
        $this->assertSame('Isabel Andrea Rios Ejemplo', $user->name);
        $this->assertSame('active', $user->status);
        $this->assertTrue(Hash::check('ClaveTemporal2026!', $user->getAuthPassword()));
        $this->assertTrue($user->roles()->whereKey($role->getKey())->exists());
        $this->assertTrue($user->establishments()->where('name', 'CESFAM Norte > Unidad de Prevención')->exists());
    }

    public function test_first_access_rejects_a_rut_not_present_in_the_directory(): void
    {
        Http::fake(['*' => Http::response(['message' => 'No encontrado'], 404)]);

        $this->postJson('/login/primer-ingreso', [
            'rut' => self::USER_RUT,
            'email' => 'isabel.rios@giatep.local',
            'password' => 'ClaveTemporal2026!',
            'password_confirmation' => 'ClaveTemporal2026!',
        ])->assertUnprocessable()->assertJsonValidationErrors('rut');

        $this->assertDatabaseMissing('users', ['rut' => self::USER_RUT]);
    }

    public function test_first_access_requires_matching_passwords_and_a_unique_email(): void
    {
        User::factory()->create(['email' => 'ocupado@giatep.local']);

        $this->post('/login/primer-ingreso', [
            'rut' => self::USER_RUT,
            'email' => 'ocupado@giatep.local',
            'password' => 'ClaveTemporal2026!',
            'password_confirmation' => 'OtraClave2026!',
        ])->assertSessionHasErrors(['email', 'password']);
    }

    public function test_existing_local_user_does_not_need_first_access_setup(): void
    {
        User::factory()->create(['rut' => self::USER_RUT, 'status' => 'active']);
        Http::fake();

        $this->postJson('/login/verificar-rut', ['rut' => self::USER_RUT])
            ->assertOk()
            ->assertJsonPath('requires_setup', false);

        Http::assertNothingSent();
    }

    private function fakeDirectoryApi(): void
    {
        Http::fake([
            '*/users/20404100-8' => Http::response(['data' => [
                'external_id' => '53539', 'rut' => self::USER_RUT, 'name' => 'Isabel Andrea Rios Ejemplo',
                'job_title' => 'Prevencionista de Riesgos',
                'dependency_id' => 510, 'dependency_name' => 'DATO SIN CONFIANZA',
                'workplace' => 'DCS > Unidad de Prevención', 'establishment' => 'DCS',
            ]], 200),
            '*/dependencies/510' => Http::response(['data' => [
                'id' => 510, 'denominacion' => 'CESFAM Norte > Unidad de Prevención',
            ]], 200),
        ]);
    }
}
