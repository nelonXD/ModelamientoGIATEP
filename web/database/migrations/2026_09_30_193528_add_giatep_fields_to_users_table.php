<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('rut')->nullable()->unique()->after('id');
            $table->string('job_title')->nullable()->after('email');
            $table->string('phone', 30)->nullable()->after('job_title');
            $table->string('status', 20)->default('active')->index()->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['rut', 'job_title', 'phone', 'status']);
        });
    }
};
