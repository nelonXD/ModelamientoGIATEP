<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRegistrationRequest;
use App\Models\Establishment;
use App\Models\RegistrationRequest;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegistrationRequestController extends Controller
{
    public function create(): View
    {
        return view('auth.register', [
            'establishments' => Establishment::where('is_active', true)->orderBy('type')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreRegistrationRequest $request): RedirectResponse
    {
        $data = $request->safe()->only([
            'rut', 'name', 'job_title', 'establishment_id', 'requested_role', 'email', 'phone', 'password',
        ]);
        $data['pending_key'] = $data['rut'];

        try {
            DB::transaction(fn () => RegistrationRequest::create($data));
        } catch (UniqueConstraintViolationException) {
            return back()->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors(['rut' => 'Ya existe una solicitud pendiente para este RUT.']);
        }

        return redirect()->route('register.success');
    }

    public function success(): View
    {
        return view('auth.register-success');
    }
}
