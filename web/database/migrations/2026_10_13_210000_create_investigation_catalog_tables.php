<?php

use App\Support\OriginalGiatepSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (OriginalGiatepSchema::isActive()) {
            return;
        }

        if (Schema::hasTable('cause_factors') && ! Schema::hasTable('ai_prompts')) {
            Schema::dropIfExists('investigation_cause_factor');
            Schema::dropIfExists('interview_answer_options');
            if (Schema::hasColumn('interview_answers', 'questionnaire_question_id')) {
                Schema::table('interview_answers', function (Blueprint $table) {
                    $table->dropConstrainedForeignId('questionnaire_question_id');
                    $table->dropColumn('estado');
                });
            }
            if (Schema::hasColumn('investigation_interviews', 'questionnaire_form_id')) {
                Schema::table('investigation_interviews', function (Blueprint $table) {
                    $table->dropConstrainedForeignId('questionnaire_form_id');
                    $table->dropColumn(['token', 'interview_start_time', 'interview_location', 'interviewee_rut', 'interviewee_company', 'validated_at']);
                });
            }
            Schema::dropIfExists('questionnaire_conditions');
            Schema::dropIfExists('questionnaire_options');
            Schema::dropIfExists('questionnaire_questions');
            Schema::dropIfExists('questionnaire_forms');
            Schema::dropIfExists('cause_factors');
        }

        Schema::create('cause_factors', function (Blueprint $table) {
            $table->id();
            $table->string('categoria');
            $table->string('subcategoria');
            $table->string('codigo', 10)->nullable()->index();
            $table->string('subcodigo', 10)->nullable()->index();
            $table->string('codigo_efectivo', 10)->unique();
            $table->text('glosa');
            $table->text('explicacion_glosa');
            $table->unsignedSmallInteger('pagina_pdf')->nullable();
            $table->boolean('activo')->default(true)->index();
            $table->timestamps();
            if (DB::connection()->getDriverName() === 'mysql') {
                $table->fullText(['glosa', 'explicacion_glosa']);
            }
        });

        Schema::create('questionnaire_forms', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('tipo', 30);
            $table->unsignedSmallInteger('version')->default(1);
            $table->string('estado', 20)->default('activo');
            $table->timestamps();
            $table->unique(['tipo', 'version']);
        });

        Schema::create('questionnaire_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questionnaire_form_id')->constrained()->cascadeOnDelete();
            $table->string('variable');
            $table->text('pregunta');
            $table->string('tipo_respuesta', 30)->default('text');
            $table->string('seccion')->nullable();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('obligatoria')->default(false);
            $table->boolean('activa')->default(true);
            $table->json('configuracion')->nullable();
            $table->timestamps();
            $table->unique(['questionnaire_form_id', 'variable']);
        });

        Schema::create('questionnaire_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questionnaire_question_id')->constrained()->cascadeOnDelete();
            $table->string('valor');
            $table->string('etiqueta');
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });

        Schema::create('questionnaire_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questionnaire_question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('depends_on_question_id')->constrained('questionnaire_questions')->cascadeOnDelete();
            $table->string('operador', 20)->default('equals');
            $table->json('valor_esperado');
            $table->string('accion', 20)->default('show');
            $table->timestamps();
        });

        Schema::table('investigation_interviews', function (Blueprint $table) {
            $table->foreignId('questionnaire_form_id')->nullable()->after('investigation_id')->constrained()->nullOnDelete();
            $table->uuid('token')->nullable()->unique()->after('questionnaire_type');
            $table->time('interview_start_time')->nullable();
            $table->string('interview_location')->nullable();
            $table->string('interviewee_rut', 20)->nullable();
            $table->string('interviewee_company')->nullable();
            $table->timestamp('validated_at')->nullable();
        });

        Schema::table('interview_answers', function (Blueprint $table) {
            $table->foreignId('questionnaire_question_id')->nullable()->after('investigation_interview_id')->constrained()->nullOnDelete();
            $table->string('estado', 20)->default('respondida');
        });

        Schema::create('interview_answer_options', function (Blueprint $table) {
            $table->foreignId('interview_answer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('questionnaire_option_id')->constrained()->cascadeOnDelete();
            $table->primary(['interview_answer_id', 'questionnaire_option_id']);
        });

        Schema::create('investigation_cause_factor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investigation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cause_factor_id')->constrained()->restrictOnDelete();
            $table->string('origen', 20)->default('manual');
            $table->text('fundamento')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
            $table->unique(['investigation_id', 'cause_factor_id'], 'inv_cause_factor_unique');
        });

        Schema::create('ai_prompts', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->unsignedSmallInteger('version')->default(1);
            $table->longText('prompt_base');
            $table->string('modelo')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('ai_analysis_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investigation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ai_prompt_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $table->string('modelo');
            $table->longText('entrada');
            $table->json('salida')->nullable();
            $table->string('estado', 20);
            $table->text('error')->nullable();
            $table->unsignedInteger('duracion_ms')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        if (OriginalGiatepSchema::isActive()) {
            return;
        }

        Schema::dropIfExists('ai_analysis_runs');
        Schema::dropIfExists('ai_prompts');
        Schema::dropIfExists('investigation_cause_factor');
        Schema::dropIfExists('interview_answer_options');
        Schema::table('interview_answers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('questionnaire_question_id');
            $table->dropColumn('estado');
        });
        Schema::table('investigation_interviews', function (Blueprint $table) {
            $table->dropConstrainedForeignId('questionnaire_form_id');
            $table->dropColumn(['token', 'interview_start_time', 'interview_location', 'interviewee_rut', 'interviewee_company', 'validated_at']);
        });
        Schema::dropIfExists('questionnaire_conditions');
        Schema::dropIfExists('questionnaire_options');
        Schema::dropIfExists('questionnaire_questions');
        Schema::dropIfExists('questionnaire_forms');
        Schema::dropIfExists('cause_factors');
    }
};
