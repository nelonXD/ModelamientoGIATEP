<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCaseEvidenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('cases.create') === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'evidence_files' => ['required', 'array', 'min:1', 'max:10'],
            'evidence_files.*' => ['required', 'file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,webp,mp3,wav,mp4,mov'],
        ];
    }
}
