<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\FirstInstitutionalAccessRequest;
use App\Models\Establishment;
use App\Models\Role;
use App\Models\User;
use App\Services\InstitutionDirectoryClient;
use App\Support\OriginalGiatepSchema;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class FirstInstitutionalAccessController extends Controller
{
    public function store(FirstInstitutionalAccessRequest $request, InstitutionDirectoryClient $directory): RedirectResponse
    {
        $data = $request->validated();

        try {
            $profile = $directory->findEligibleUser($data['rut']);
        } catch (RuntimeException $exception) {
            return back()->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors(['rut' => $exception->getMessage()]);
        }

        if (! $profile) {
            throw ValidationException::withMessages(['rut' => 'El RUT no figura en el directorio institucional autorizado.']);
        }

        try {
            $user = DB::transaction(function () use ($data, $profile): User {
                $existingUser = User::query()->where(User::columnFor('rut'), $data['rut'])->lockForUpdate()->first();
                if ($existingUser) {
                    throw ValidationException::withMessages(['rut' => 'La cuenta ya fue creada. Ingresa desde el formulario habitual.']);
                }

                $roleColumn = OriginalGiatepSchema::isActive() ? 'Codigo_Rol' : 'slug';
                $role = Role::query()->where($roleColumn, $profile['role_code'])->first();
                if (! $role) {
                    throw new RuntimeException('El rol institucional no está configurado en GIATEP.');
                }

                if (OriginalGiatepSchema::isActive()) {
                    return $this->createOriginalSchemaUser($data, $profile, $role->getKey());
                }

                $establishment = Establishment::query()->firstOrCreate(
                    ['name' => $profile['dependency_name']],
                    ['type' => 'Dependencia institucional', 'is_active' => true],
                );
                $user = User::query()->create([
                    'rut' => $data['rut'], 'name' => $profile['name'], 'email' => $data['email'],
                    'job_title' => $profile['job_title'], 'status' => 'active', 'password' => $data['password'],
                ]);
                $user->roles()->syncWithoutDetaching([$role->getKey()]);
                $user->establishments()->syncWithoutDetaching([$establishment->getKey()]);

                return $user;
            });
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['rut' => 'La cuenta ya fue creada. Ingresa desde el formulario habitual.']);
        } catch (RuntimeException $exception) {
            return back()->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors(['rut' => $exception->getMessage()]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /** @param array{rut: string, email: string, password: string} $data
     * @param  array<string, int|string>  $profile
     */
    private function createOriginalSchemaUser(array $data, array $profile, int|string $roleId): User
    {
        $dependencyId = DB::table('dependencia')->where('Nombre_Dependencia', $profile['dependency_name'])->value('Id_Dependencia');
        if (! $dependencyId) {
            $dependencyId = DB::table('dependencia')->insertGetId(['Nombre_Dependencia' => $profile['dependency_name']], 'Id_Dependencia');
        }

        [$firstName, $lastName] = array_pad(preg_split('/\s+/', trim((string) $profile['name']), 2) ?: [], 2, '');
        DB::table('usuario')->insert([
            'Rut_Usuario' => $data['rut'], 'Nombre' => $firstName, 'Apellido' => $lastName,
            'Correo' => $data['email'], 'Contrasena_Hash' => Hash::make($data['password']),
            'Id_Dependencia' => $dependencyId, 'Cargo' => $profile['job_title'], 'Estado' => 'active',
        ]);
        DB::table('rol_usuario')->insert(['Rut_Usuario' => $data['rut'], 'Id_Rol' => $roleId]);

        return User::query()->where(User::columnFor('rut'), $data['rut'])->firstOrFail();
    }
}
