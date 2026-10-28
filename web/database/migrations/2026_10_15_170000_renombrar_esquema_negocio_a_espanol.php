<?php

use App\Support\OriginalGiatepSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLES = [
        'users' => 'usuario', 'establishments' => 'establecimiento', 'roles' => 'rol',
        'registration_requests' => 'solicitud_registro', 'role_user' => 'rol_usuario', 'establishment_user' => 'usuario_establecimiento',
        'occupational_cases' => 'caso', 'case_evidences' => 'evidencia', 'investigations' => 'investigacion',
        'investigation_interviews' => 'declaracion', 'interview_answers' => 'respuesta_declaracion',
        'investigation_facts' => 'hecho_constatado', 'causal_nodes' => 'arbol_causas', 'corrective_measures' => 'monitoreo_medida_control',
        'cause_factors' => 'matriz_factores_causas', 'questionnaire_forms' => 'formulario_declaracion',
        'questionnaire_questions' => 'pregunta_declaracion', 'questionnaire_options' => 'opcion_pregunta',
        'questionnaire_conditions' => 'condicion_pregunta', 'interview_answer_options' => 'respuesta_opcion',
        'investigation_cause_factor' => 'investigacion_matriz_factores_causas', 'ai_prompts' => 'prompts_ia',
        'ai_analysis_runs' => 'ejecuciones_ia', 'catalog_options' => 'opciones_catalogo',
    ];

    private const COLUMNS = [
        'users' => ['name' => 'nombre', 'email' => 'correo', 'email_verified_at' => 'correo_verificado_en', 'password' => 'contrasena', 'remember_token' => 'token_recordar', 'job_title' => 'cargo', 'phone' => 'telefono', 'status' => 'estado'],
        'establishments' => ['type' => 'tipo', 'name' => 'nombre', 'is_active' => 'activo'],
        'roles' => ['slug' => 'codigo', 'name' => 'nombre'],
        'registration_requests' => ['pending_key' => 'clave_pendiente', 'name' => 'nombre', 'job_title' => 'cargo', 'establishment_id' => 'establecimiento_id', 'requested_role' => 'rol_solicitado', 'email' => 'correo', 'phone' => 'telefono', 'password' => 'contrasena', 'status' => 'estado', 'rejection_reason' => 'motivo_rechazo', 'resolved_by' => 'resuelto_por', 'resolved_at' => 'resuelto_en'],
        'role_user' => ['role_id' => 'rol_id', 'user_id' => 'usuario_id'], 'establishment_user' => ['establishment_id' => 'establecimiento_id', 'user_id' => 'usuario_id'],
        'occupational_cases' => ['created_by' => 'creado_por', 'establishment_id' => 'establecimiento_id', 'status' => 'estado', 'case_type' => 'tipo_caso', 'employer_name' => 'nombre_empleador', 'employer_rut' => 'rut_empleador', 'employer_address' => 'direccion_empleador', 'employer_commune' => 'comuna_empleador', 'employer_phone' => 'telefono_empleador', 'economic_activity' => 'actividad_economica', 'company_type' => 'tipo_empresa', 'administrator' => 'organismo_administrador', 'worker_name' => 'nombre_trabajador', 'worker_rut' => 'rut_trabajador', 'worker_address' => 'direccion_trabajador', 'worker_commune' => 'comuna_trabajador', 'worker_phone' => 'telefono_trabajador', 'worker_sex' => 'sexo_trabajador', 'birth_date' => 'fecha_nacimiento', 'nationality' => 'nacionalidad', 'job_title' => 'cargo', 'workplace' => 'lugar_trabajo', 'contract_type' => 'tipo_contrato', 'occupational_category' => 'categoria_ocupacional', 'hire_date' => 'fecha_ingreso', 'event_date' => 'fecha_evento', 'event_time' => 'hora_evento', 'event_location' => 'lugar_evento', 'narrative' => 'relato', 'witnesses' => 'testigos', 'details' => 'detalles'],
        'case_evidences' => ['occupational_case_id' => 'caso_id', 'uploaded_by' => 'cargado_por', 'original_name' => 'nombre_original', 'path' => 'ruta', 'mime_type' => 'tipo_mime', 'size' => 'tamano'],
        'investigations' => ['occupational_case_id' => 'caso_id', 'owner_id' => 'responsable_id', 'status' => 'estado', 'final_story' => 'relato_final', 'ai_model' => 'modelo_ia', 'ai_analyzed_at' => 'analizado_ia_en'],
        'investigation_interviews' => ['investigation_id' => 'investigacion_id', 'created_by' => 'creado_por', 'questionnaire_type' => 'tipo_cuestionario', 'interviewee_name' => 'nombre_entrevistado', 'interviewee_role' => 'cargo_entrevistado', 'witness_type' => 'tipo_testigo', 'interview_date' => 'fecha_entrevista', 'status' => 'estado', 'questionnaire_form_id' => 'formulario_declaracion_id', 'interview_start_time' => 'hora_inicio_entrevista', 'interview_location' => 'lugar_entrevista', 'interviewee_rut' => 'rut_entrevistado', 'interviewee_company' => 'empresa_entrevistado', 'validated_at' => 'validado_en'],
        'interview_answers' => ['investigation_interview_id' => 'declaracion_id', 'questionnaire_question_id' => 'pregunta_declaracion_id', 'question_key' => 'clave_pregunta', 'answer_type' => 'tipo_respuesta', 'value' => 'valor'],
        'investigation_facts' => ['investigation_id' => 'investigacion_id', 'position' => 'posicion', 'description' => 'descripcion', 'ai_generated' => 'generado_por_ia'],
        'causal_nodes' => ['investigation_id' => 'investigacion_id', 'node_type' => 'tipo_nodo', 'position' => 'posicion', 'description' => 'descripcion', 'ai_generated' => 'generado_por_ia'],
        'corrective_measures' => ['investigation_id' => 'investigacion_id', 'description' => 'descripcion', 'category' => 'categoria', 'responsible' => 'responsable', 'due_date' => 'fecha_limite', 'status' => 'estado', 'progress' => 'avance', 'ai_generated' => 'generado_por_ia'],
        'questionnaire_questions' => ['questionnaire_form_id' => 'formulario_declaracion_id'], 'questionnaire_options' => ['questionnaire_question_id' => 'pregunta_declaracion_id'],
        'questionnaire_conditions' => ['questionnaire_question_id' => 'pregunta_declaracion_id', 'depends_on_question_id' => 'depende_de_pregunta_id'],
        'interview_answer_options' => ['interview_answer_id' => 'respuesta_declaracion_id', 'questionnaire_option_id' => 'opcion_pregunta_id'],
        'investigation_cause_factor' => ['investigation_id' => 'investigacion_id', 'cause_factor_id' => 'factor_causa_id', 'validated_by' => 'validado_por', 'validated_at' => 'validado_en'],
        'ai_analysis_runs' => ['investigation_id' => 'investigacion_id', 'ai_prompt_id' => 'prompt_ia_id', 'requested_by' => 'solicitado_por'],
        'catalog_options' => ['category' => 'categoria', 'value' => 'valor', 'label' => 'etiqueta', 'sort_order' => 'orden', 'is_active' => 'activo'],
    ];

    public function up(): void
    {
        if (OriginalGiatepSchema::isActive()) {
            return;
        }

        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        // The physical GIATEP schema is already Spanish and has independent
        // normalized tables. Never rename those tables to fit the prototype.
        if (Schema::hasTable('USUARIO') && Schema::hasTable('CASO') && Schema::hasTable('DEPENDENCIA')) {
            return;
        }

        foreach (self::TABLES as $old => $new) {
            if (Schema::hasTable($old) && ! Schema::hasTable($new)) {
                Schema::rename($old, $new);
            }
        }

        foreach (self::COLUMNS as $oldTable => $renames) {
            $table = self::TABLES[$oldTable];
            foreach ($renames as $old => $new) {
                if (Schema::hasColumn($table, $old)) {
                    Schema::table($table, fn (Blueprint $blueprint) => $blueprint->renameColumn($old, $new));
                }
            }
        }

        $this->createCompatibilityViews();
    }

    public function down(): void
    {
        if (OriginalGiatepSchema::isActive()) {
            return;
        }

        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        if (Schema::hasTable('USUARIO') && Schema::hasTable('CASO') && Schema::hasTable('DEPENDENCIA')) {
            return;
        }

        foreach (array_keys(self::TABLES) as $legacyName) {
            DB::statement('DROP VIEW IF EXISTS `'.$legacyName.'`');
        }

        foreach (self::COLUMNS as $oldTable => $renames) {
            $table = self::TABLES[$oldTable];
            foreach (array_reverse($renames, true) as $old => $new) {
                if (Schema::hasColumn($table, $new)) {
                    Schema::table($table, fn (Blueprint $blueprint) => $blueprint->renameColumn($new, $old));
                }
            }
        }

        foreach (array_reverse(self::TABLES, true) as $old => $new) {
            if (Schema::hasTable($new) && ! Schema::hasTable($old)) {
                Schema::rename($new, $old);
            }
        }
    }

    private function createCompatibilityViews(): void
    {
        foreach (self::TABLES as $old => $new) {
            if (! Schema::hasTable($new)) {
                continue;
            }

            $reverseColumns = array_flip(self::COLUMNS[$old] ?? []);
            $select = collect(Schema::getColumnListing($new))->map(function (string $column) use ($reverseColumns): string {
                $alias = $reverseColumns[$column] ?? $column;

                return '`'.$column.'`'.($alias === $column ? '' : ' AS `'.$alias.'`');
            })->implode(', ');

            DB::statement('CREATE VIEW `'.$old.'` AS SELECT '.$select.' FROM `'.$new.'`');
        }
    }
};
