<?php

namespace App\Http\Requests;

use App\Models\RegistrationRequest;
use App\Models\User;
use App\Support\Rut;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class StoreRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rut' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'job_title' => ['required', 'string', 'max:255'],
            'establishment_id' => ['required', Rule::exists('establishments', 'id')->where('is_active', true)],
            'requested_role' => ['required', Rule::in(['delegado', 'cphs'])],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['rut' => Rut::normalize((string) $this->input('rut'))]);
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if (! $validator->errors()->has('rut') && ! Rut::isValid($this->string('rut')->toString())) {
                $validator->errors()->add('rut', 'Ingresa un RUT válido.');
            }

            if (User::where('rut', $this->string('rut'))->exists()) {
                $validator->errors()->add('rut', 'Ya existe una cuenta asociada a este RUT.');
            }

            if (RegistrationRequest::where('rut', $this->string('rut'))->where('status', 'pending')->exists()) {
                $validator->errors()->add('rut', 'Ya existe una solicitud pendiente para este RUT.');
            }
        }];
    }

    public function messages(): array
    {
        return [
            'required' => 'Este campo es obligatorio.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'requested_role.in' => 'El rol solicitado no está disponible para registro público.',
        ];
    }
}
