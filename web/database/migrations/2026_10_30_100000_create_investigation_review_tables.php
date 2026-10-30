<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('investigation_observations')) {
            Schema::create('investigation_observations', function (Blueprint $table): void {
                $table->id();
                $table->string('investigation_folio', 40)->index();
                $table->foreignId('parent_id')->nullable()->constrained('investigation_observations')->nullOnDelete();
                $table->string('author_rut', 20)->index();
                $table->string('author_name');
                $table->string('author_role', 120)->nullable();
                $table->text('body');
                $table->string('status', 20)->default('pendiente')->index();
                $table->timestamp('responded_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('investigation_events')) {
            Schema::create('investigation_events', function (Blueprint $table): void {
                $table->id();
                $table->string('investigation_folio', 40)->index();
                $table->string('actor_rut', 20)->index();
                $table->string('actor_name');
                $table->string('actor_role', 120)->nullable();
                $table->string('event', 120);
                $table->json('details')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (! Schema::hasTable('investigation_drafts')) {
            Schema::create('investigation_drafts', function (Blueprint $table): void {
                $table->string('investigation_folio', 40)->primary();
                $table->json('payload');
                $table->string('updated_by_rut', 20)->index();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('investigations') && Schema::hasColumn('investigations', 'status')) {
            DB::table('investigations')->whereIn('status', ['Borrador', 'En investigación', 'En curso'])->update(['status' => 'En desarrollo']);
            DB::table('investigations')->where('status', 'Concluida')->update(['status' => 'Completada']);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('investigation_events');
        Schema::dropIfExists('investigation_observations');
        Schema::dropIfExists('investigation_drafts');
    }
};
