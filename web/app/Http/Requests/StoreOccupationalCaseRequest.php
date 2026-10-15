<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'employer_address' => ['required', 'string', 'max:255'], 'employer_commune' => ['required', Rule::exists('catalog_options', 'value')->where('category', 'commune')->where('is_active', true)],
            'employer_phone' => ['nullable', 'string', 'max:30'], 'economic_activity' => ['required', 'string', 'max:255'],
            'company_type' => ['required', Rule::exists('catalog_options', 'value')->where('category', 'company_type')->where('is_active', true)],
            'administrator' => ['required', Rule::exists('catalog_options', 'value')->where('category', 'administrator')->where('is_active', true)],
            'establishment_id' => ['required', Rule::exists('establishments', 'id')->where('is_active', true)],
            'worker_name' => ['required', 'string', 'max:255'],
            'worker_rut' => ['required', 'string', 'max:20'], 'worker_address' => ['nullable', 'string', 'max:255'],
            'worker_commune' => ['nullable', Rule::exists('catalog_options', 'value')->where('category', 'commune')->where('is_active', true)], 'phone' => ['nullable', 'string', 'max:30'],
            'worker_sex' => ['nullable', Rule::exists('catalog_options', 'value')->where('category', 'worker_sex')->where('is_active', true)], 'birth_date' => ['nullable', 'date', 'before:today'],
            'nationality' => ['nullable', Rule::exists('catalog_options', 'value')->where('category', 'nationality')->where('is_active', true)], 'job_title' => ['required', 'string', 'max:255'],
            'workplace' => ['required', 'string', 'max:255'],
            'contract_type' => ['nullable', Rule::exists('catalog_options', 'value')->where('category', 'contract_type')->where('is_active', true)],
            'occupational_category' => ['nullable', Rule::exists('catalog_options', 'value')->where('category', 'occupational_category')->where('is_active', true)], 'hire_date' => ['nullable', 'date', 'before_or_equal:today'],
            'case_type' => ['required', Rule::exists('catalog_options', 'value')->where('category', 'case_type')->where('is_active', true)], 'event_date' => ['required', 'date', 'before_or_equal:today'],
            'event_time' => ['nullable', 'date_format:H:i'], 'event_location' => ['nullable', 'string', 'max:255'],
            'narrative' => ['required', 'string', 'max:20000'], 'witnesses' => ['nullable', 'string', 'max:5000'],
            'income_type' => ['nullable', Rule::exists('catalog_options', 'value')->where('category', 'income_type')->where('is_active', true)],
            'work_schedule' => ['nullable', Rule::exists('catalog_options', 'value')->where('category', 'work_schedule')->where('is_active', true)],
            'indigenous_people' => ['nullable', Rule::exists('catalog_options', 'value')->where('category', 'indigenous_people')->where('is_active', true)],
            'doing_usual_job' => ['nullable', Rule::exists('catalog_options', 'value')->where('category', 'yes_no')->where('is_active', true)],
            'previous_symptoms' => ['nullable', Rule::exists('catalog_options', 'value')->where('category', 'yes_no')->where('is_active', true)],
            'coworkers_same_symptoms' => ['nullable', Rule::exists('catalog_options', 'value')->where('category', 'yes_no')->where('is_active', true)],
            'route_type' => ['nullable', Rule::exists('catalog_options', 'value')->where('category', 'route_type')->where('is_active', true)],
            'proof_types' => ['nullable', 'array'], 'proof_types.*' => [Rule::exists('catalog_options', 'value')->where('category', 'proof_type')->where('is_active', true)],
            'evidence_files' => ['nullable', 'array', 'max:10'], 'evidence_files.*' => ['file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,webp,mp3,wav,mp4,mov'],
        ];
    }
}
