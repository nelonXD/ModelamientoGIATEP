<?php

namespace App\Http\Requests;

use App\Support\OriginalGiatepSchema;
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
        if (OriginalGiatepSchema::isActive()) {
            $commune = Rule::exists('comuna', 'Nombre_Comuna');
            $caseType = Rule::exists('tipo_caso', 'Nombre_Tipo_Caso');
            $dependency = Rule::exists('dependencia', 'Id_Dependencia');

            return [
                'employer_name' => ['required', 'string', 'max:150'], 'employer_rut' => ['required', 'string', 'max:20'],
                'employer_address' => ['required', 'string', 'max:255'], 'employer_commune' => ['required', $commune],
                'employer_phone' => ['nullable', 'string', 'max:20'], 'economic_activity' => ['required', 'string', 'max:255'],
                'company_type' => ['required', 'string', 'max:50'], 'administrator' => ['required', 'string', 'max:20'],
                'establishment_id' => ['required', $dependency], 'worker_name' => ['required', 'string', 'max:201'],
                'worker_rut' => ['required', 'string', 'max:20'], 'worker_address' => ['nullable', 'string', 'max:255'],
                'worker_commune' => ['nullable', $commune], 'phone' => ['nullable', 'string', 'max:20'],
                'worker_sex' => ['nullable', 'string', 'max:20'], 'birth_date' => ['nullable', 'date', 'before:today'],
                'nationality' => ['nullable', 'string', 'max:60'], 'job_title' => ['required', 'string', 'max:100'],
                'workplace' => ['required', 'string', 'max:150'], 'contract_type' => ['nullable', 'string', 'max:80'],
                'occupational_category' => ['nullable', 'string', 'max:100'], 'hire_date' => ['nullable', 'date', 'before_or_equal:today'],
                'case_type' => ['required', $caseType], 'event_date' => ['required', 'date', 'before_or_equal:today'],
                'event_time' => ['nullable', 'date_format:H:i'], 'event_location' => ['nullable', 'string', 'max:255'],
                'narrative' => ['required', 'string', 'max:20000'], 'witnesses' => ['nullable', 'string', 'max:5000'],
                'income_type' => ['nullable', 'string', 'max:80'], 'work_schedule' => ['nullable', 'string', 'max:80'],
                'indigenous_people' => ['nullable', Rule::exists('pueblos_originarios', 'Nombre_Pueblo')],
                'doing_usual_job' => ['nullable', Rule::in(['Sí', 'No'])], 'previous_symptoms' => ['nullable', Rule::in(['Sí', 'No'])],
                'coworkers_same_symptoms' => ['nullable', Rule::in(['Sí', 'No'])], 'route_type' => ['nullable', 'string', 'max:100'],
                'proof_types' => ['nullable', 'array'], 'proof_types.*' => ['string', 'max:100'],
                'company_tenure' => ['nullable', 'string', 'max:50'], 'activity_before_event' => ['nullable', 'string', 'max:5000'],
                'usual_job_description' => ['nullable', 'string', 'max:150'], 'work_start_time' => ['nullable', 'date_format:H:i'],
                'work_end_time' => ['nullable', 'date_format:H:i'], 'affected_body_part' => ['nullable', 'string', 'max:255'],
                'symptom_duration' => ['nullable', 'string', 'max:255'], 'activity_when_symptoms_began' => ['nullable', 'string', 'max:255'],
                'symptom_job_position' => ['nullable', 'string', 'max:150'], 'suspected_work_agents' => ['nullable', 'string', 'max:5000'],
                'exposure_duration' => ['nullable', 'string', 'max:255'], 'proof_detail' => ['nullable', 'string', 'max:5000'],
                'accident_severity' => ['nullable', 'string', 'max:50'], 'accident_scope' => ['nullable', 'string', 'max:255'],
                'evidence_files' => ['nullable', 'array', 'max:10'], 'evidence_files.*' => ['file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,webp,mp3,wav,mp4,mov'],
            ];
        }

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
            'company_tenure' => ['nullable', 'string', 'max:255'],
            'activity_before_event' => ['nullable', 'string', 'max:5000'],
            'usual_job_description' => ['nullable', 'string', 'max:5000'],
            'work_start_time' => ['nullable', 'date_format:H:i'],
            'work_end_time' => ['nullable', 'date_format:H:i'],
            'affected_body_part' => ['nullable', 'string', 'max:255'],
            'symptom_duration' => ['nullable', 'string', 'max:255'],
            'activity_when_symptoms_began' => ['nullable', 'string', 'max:5000'],
            'symptom_job_position' => ['nullable', 'string', 'max:255'],
            'suspected_work_agents' => ['nullable', 'string', 'max:5000'],
            'exposure_duration' => ['nullable', 'string', 'max:255'],
            'proof_detail' => ['nullable', 'string', 'max:5000'],
            'accident_severity' => ['nullable', 'string', 'max:255'],
            'accident_scope' => ['nullable', 'string', 'max:255'],
            'evidence_files' => ['nullable', 'array', 'max:10'], 'evidence_files.*' => ['file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,webp,mp3,wav,mp4,mov'],
        ];
    }
}
