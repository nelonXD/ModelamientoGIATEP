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

        Schema::create('establishments', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30);
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
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

        Schema::dropIfExists('establishments');
    }
};
