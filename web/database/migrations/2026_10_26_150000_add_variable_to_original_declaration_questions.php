<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pregunta_declaracion') || Schema::hasColumn('pregunta_declaracion', 'Variable')) {
            return;
        }

        Schema::table('pregunta_declaracion', function (Blueprint $table): void {
            $table->string('Variable', 120)->nullable()->unique();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pregunta_declaracion') || ! Schema::hasColumn('pregunta_declaracion', 'Variable')) {
            return;
        }

        Schema::table('pregunta_declaracion', function (Blueprint $table): void {
            $table->dropUnique(['Variable']);
            $table->dropColumn('Variable');
        });
    }
};
