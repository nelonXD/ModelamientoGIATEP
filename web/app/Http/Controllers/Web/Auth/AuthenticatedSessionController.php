<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckLoginRutRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Services\InstitutionDirectoryClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $user = User::query()
            ->where(User::columnFor('rut'), $request->string('rut')->toString())
            ->first();

        if (! $user || $user->status !== 'active' || ! Hash::check($request->string('password')->toString(), $user->getAuthPassword())) {
            return back()->withInput($request->only('rut'))->withErrors([
                'rut' => 'Las credenciales no coinciden o la cuenta aún no está habilitada.',
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function checkRut(CheckLoginRutRequest $request, InstitutionDirectoryClient $directory): JsonResponse
    {
        $user = User::query()
            ->where(User::columnFor('rut'), $request->string('rut')->toString())
            ->first();

        if ($user?->status === 'active') {
            return response()->json(['registered' => true, 'requires_setup' => false]);
        }

        if ($user) {
            throw ValidationException::withMessages(['rut' => 'La cuenta está deshabilitada. Contacta a la administración de GIATEP.']);
        }

        try {
            $profile = $directory->findEligibleUser($request->string('rut')->toString());
        } catch (\RuntimeException $exception) {
            return response()->json(['errors' => ['rut' => [$exception->getMessage()]]], 503);
        }

        if (! $profile) {
            throw ValidationException::withMessages([
                'rut' => 'El RUT no se encuentra registrado en el sistema ni en el directorio institucional.',
            ]);
        }

        return response()->json([
            'registered' => true,
            'requires_setup' => true,
            'setup_url' => route('login.first-access'),
            'profile' => [
                'name' => $profile['name'], 'job_title' => $profile['job_title'],
                'role_name' => $profile['role_name'], 'dependency_name' => $profile['dependency_name'],
            ],
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
