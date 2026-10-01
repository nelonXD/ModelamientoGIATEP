<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResolveRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('review-registration-requests') ?? false;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in(['approve', 'reject'])],
            'rejection_reason' => ['nullable', 'required_if:action,reject', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return ['rejection_reason.required_if' => 'Debes indicar el motivo del rechazo.'];
    }
}
