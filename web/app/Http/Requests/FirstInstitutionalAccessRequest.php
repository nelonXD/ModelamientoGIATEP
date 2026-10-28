<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\Rut;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class FirstInstitutionalAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'rut' => ['required', 'string', 'max:12'],
            'email' => ['required', 'email', 'max:254', Rule::unique((new User)->getTable(), User::columnFor('email'))],
            'password' => ['required', 'confirmed', Password::min(8)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'rut' => Rut::normalize((string) $this->input('rut')),
            'email' => strtolower(trim((string) $this->input('email'))),
        ]);
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if (! $validator->errors()->has('rut') && ! Rut::isValid($this->string('rut')->toString())) {
                $validator->errors()->add('rut', 'Ingresa un RUT válido.');
            }
        }];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Ingresa un correo electrónico.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'email.unique' => 'Ese correo ya está asociado a otra cuenta.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ];
    }
}
