<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql' || ! Schema::hasTable('usuario') || ! Schema::hasTable('caso')) {
            return;
        }

        if (! Schema::hasTable('cause_factors')) {
            Schema::create('cause_factors', function (Blueprint $table): void {
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
            });
        }

        if (! Schema::hasTable('investigation_cause_factor')) {
            Schema::create('investigation_cause_factor', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('investigation_id')->index();
                $table->foreignId('cause_factor_id')->constrained('cause_factors')->restrictOnDelete();
                $table->string('origen', 20)->default('manual');
                $table->text('fundamento')->nullable();
                $table->string('validated_by', 20)->nullable();
                $table->timestamp('validated_at')->nullable();
                $table->timestamps();
                $table->unique(['investigation_id', 'cause_factor_id'], 'inv_cause_factor_unique');
            });
        }

        if (! Schema::hasTable('investigation_facts')) {
            Schema::create('investigation_facts', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('investigation_id')->index();
                $table->unsignedSmallInteger('position');
                $table->text('description');
                $table->boolean('ai_generated')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('causal_nodes')) {
            Schema::create('causal_nodes', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('investigation_id')->index();
                $table->string('node_type', 30);
                $table->unsignedSmallInteger('position');
                $table->text('description');
                $table->boolean('ai_generated')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('corrective_measures')) {
            Schema::create('corrective_measures', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('investigation_id')->index();
                $table->text('description');
                $table->string('category', 80);
                $table->string('responsible', 255);
                $table->date('due_date')->nullable();
                $table->string('status', 30)->default('Pendiente')->index();
                $table->unsignedTinyInteger('progress')->default(0);
                $table->boolean('ai_generated')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ai_analysis_runs')) {
            Schema::create('ai_analysis_runs', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('investigation_id')->nullable()->index();
                $table->unsignedBigInteger('ai_prompt_id')->nullable();
                $table->string('requested_by', 20);
                $table->string('modelo');
                $table->longText('entrada');
                $table->json('salida')->nullable();
                $table->string('estado', 20);
                $table->text('error')->nullable();
                $table->unsignedInteger('duracion_ms')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql' || ! Schema::hasTable('usuario') || ! Schema::hasTable('caso')) {
            return;
        }

        Schema::dropIfExists('ai_analysis_runs');
        Schema::dropIfExists('corrective_measures');
        Schema::dropIfExists('causal_nodes');
        Schema::dropIfExists('investigation_facts');
        Schema::dropIfExists('investigation_cause_factor');
        Schema::dropIfExists('cause_factors');
    }
};
