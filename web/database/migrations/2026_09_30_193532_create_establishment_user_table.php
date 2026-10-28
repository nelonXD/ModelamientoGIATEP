<?php

use App\Support\OriginalGiatepSchema;
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
        if (OriginalGiatepSchema::isActive()) {
            return;
        }

        Schema::create('establishment_user', function (Blueprint $table) {
            $table->foreignId('establishment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['establishment_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (OriginalGiatepSchema::isActive()) {
            return;
        }

        Schema::dropIfExists('establishment_user');
    }
};
