<?php

use App\Support\OriginalGiatepSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (OriginalGiatepSchema::isActive()) {
            return;
        }

        Schema::create('occupational_cases', function (Blueprint $table) {
            $table->id();
            $table->string('folio')->unique();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('establishment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 30)->default('Borrador')->index();
            $table->string('case_type', 80)->index();
            $table->string('employer_name');
            $table->string('employer_rut', 20);
            $table->string('employer_address');
            $table->string('employer_commune', 80);
            $table->string('employer_phone', 30)->nullable();
            $table->string('economic_activity');
            $table->string('company_type', 50);
            $table->string('administrator', 80);
            $table->string('worker_name');
            $table->string('worker_rut', 20)->index();
            $table->string('worker_address')->nullable();
            $table->string('worker_commune', 80)->nullable();
            $table->string('worker_phone', 30)->nullable();
            $table->string('worker_sex', 30)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('nationality', 80)->nullable();
            $table->string('job_title');
            $table->string('workplace');
            $table->string('contract_type', 80)->nullable();
            $table->string('occupational_category', 80)->nullable();
            $table->date('hire_date')->nullable();
            $table->date('event_date');
            $table->time('event_time')->nullable();
            $table->string('event_location')->nullable();
            $table->longText('narrative');
            $table->text('witnesses')->nullable();
            $table->json('details')->nullable();
            $table->timestamps();
        });

        Schema::create('case_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('occupational_case_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->string('original_name');
            $table->string('path');
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('size');
            $table->timestamps();
        });

        Schema::create('investigations', function (Blueprint $table) {
            $table->id();
            $table->string('folio')->unique();
            $table->foreignId('occupational_case_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->string('status', 30)->default('Borrador')->index();
            $table->longText('final_story')->nullable();
            $table->string('ai_model')->nullable();
            $table->timestamp('ai_analyzed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('investigation_interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investigation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('questionnaire_type', 30)->default('adapted');
            $table->string('interviewee_name');
            $table->string('interviewee_role')->nullable();
            $table->string('witness_type', 80)->nullable();
            $table->date('interview_date')->nullable();
            $table->string('status', 30)->default('Completada');
            $table->timestamps();
        });

        Schema::create('interview_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investigation_interview_id')->constrained()->cascadeOnDelete();
            $table->string('question_key');
            $table->string('answer_type', 20)->default('text');
            $table->json('value');
            $table->timestamps();
            $table->unique(['investigation_interview_id', 'question_key']);
        });

        Schema::create('investigation_facts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investigation_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position');
            $table->text('description');
            $table->boolean('ai_generated')->default(false);
            $table->timestamps();
        });

        Schema::create('causal_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investigation_id')->constrained()->cascadeOnDelete();
            $table->string('node_type', 30);
            $table->unsignedSmallInteger('position');
            $table->text('description');
            $table->boolean('ai_generated')->default(false);
            $table->timestamps();
        });

        Schema::create('corrective_measures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investigation_id')->constrained()->cascadeOnDelete();
            $table->text('description');
            $table->string('category', 80);
            $table->string('responsible');
            $table->date('due_date')->nullable();
            $table->string('status', 30)->default('Pendiente')->index();
            $table->unsignedTinyInteger('progress')->default(0);
            $table->boolean('ai_generated')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        if (OriginalGiatepSchema::isActive()) {
            return;
        }

        Schema::dropIfExists('corrective_measures');
        Schema::dropIfExists('causal_nodes');
        Schema::dropIfExists('investigation_facts');
        Schema::dropIfExists('interview_answers');
        Schema::dropIfExists('investigation_interviews');
        Schema::dropIfExists('investigations');
        Schema::dropIfExists('case_evidences');
        Schema::dropIfExists('occupational_cases');
    }
};
