<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql' || ! Schema::hasTable('usuario') || ! Schema::hasTable('investigacion')) {
            return;
        }

        Schema::table('investigacion', function (Blueprint $table): void {
            if (! Schema::hasColumn('investigacion', 'ai_model')) {
                $table->string('ai_model')->nullable();
            }
            if (! Schema::hasColumn('investigacion', 'ai_analyzed_at')) {
                $table->timestamp('ai_analyzed_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql' || ! Schema::hasTable('usuario') || ! Schema::hasTable('investigacion')) {
            return;
        }

        Schema::table('investigacion', function (Blueprint $table): void {
            $columns = array_values(array_filter(['ai_model', 'ai_analyzed_at'], fn (string $column): bool => Schema::hasColumn('investigacion', $column)));
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
