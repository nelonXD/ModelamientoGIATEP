<?php

namespace App\Http\Requests;

use App\Support\Rut;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rut' => ['required', 'string'],
            'password' => ['required', 'string'],
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
        }];
    }

    public function messages(): array
    {
        return ['rut.required' => 'El RUT es obligatorio.', 'password.required' => 'La contraseña es obligatoria.'];
    }
}
