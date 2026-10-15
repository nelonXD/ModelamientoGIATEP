<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOccupationalCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('cases.create') === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'employer_name' => ['required', 'string', 'max:255'], 'employer_rut' => ['required', 'string', 'max:20'],
            'employer_address' => ['required', 'string', 'max:255'], 'employer_commune' => ['required', 'string', 'max:80'],
            'employer_phone' => ['nullable', 'string', 'max:30'], 'economic_activity' => ['required', 'string', 'max:255'],
            'company_type' => ['required', 'string', 'max:50'], 'administrator' => ['required', 'string', 'max:80'],
            'establishment' => ['required', 'string', 'max:255'], 'worker_name' => ['required', 'string', 'max:255'],
            'worker_rut' => ['required', 'string', 'max:20'], 'worker_address' => ['nullable', 'string', 'max:255'],
            'worker_commune' => ['nullable', 'string', 'max:80'], 'phone' => ['nullable', 'string', 'max:30'],
            'worker_sex' => ['nullable', 'string', 'max:30'], 'birth_date' => ['nullable', 'date', 'before:today'],
            'nationality' => ['nullable', 'string', 'max:80'], 'job_title' => ['required', 'string', 'max:255'],
            'workplace' => ['required', 'string', 'max:255'], 'contract_type' => ['nullable', 'string', 'max:80'],
            'occupational_category' => ['nullable', 'string', 'max:80'], 'hire_date' => ['nullable', 'date', 'before_or_equal:today'],
            'case_type' => ['required', 'string', 'max:80'], 'event_date' => ['required', 'date', 'before_or_equal:today'],
            'event_time' => ['nullable', 'date_format:H:i'], 'event_location' => ['nullable', 'string', 'max:255'],
            'narrative' => ['required', 'string', 'max:20000'], 'witnesses' => ['nullable', 'string', 'max:5000'],
            'evidence_files' => ['nullable', 'array', 'max:10'], 'evidence_files.*' => ['file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,webp,mp3,wav,mp4,mov'],
        ];
    }
}
