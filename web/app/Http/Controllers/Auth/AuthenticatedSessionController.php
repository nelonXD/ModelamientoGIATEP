<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckLoginRutRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        if (! Auth::attempt(['rut' => $request->string('rut'), 'password' => $request->string('password'), 'status' => 'active'])) {
            return back()->withInput($request->only('rut'))->withErrors([
                'rut' => 'Las credenciales no coinciden o la cuenta aún no está habilitada.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function checkRut(CheckLoginRutRequest $request): JsonResponse
    {
        $isRegistered = User::query()
            ->where('rut', $request->string('rut'))
            ->where('status', 'active')
            ->exists();

        if (! $isRegistered) {
            throw ValidationException::withMessages([
                'rut' => 'El RUT no se encuentra registrado o su cuenta no está habilitada.',
            ]);
        }

        return response()->json(['registered' => true]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
