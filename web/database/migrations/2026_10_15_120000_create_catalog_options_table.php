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

        Schema::create('catalog_options', function (Blueprint $table) {
            $table->id();
            $table->string('category', 80);
            $table->string('value', 120);
            $table->string('label', 160);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->unique(['category', 'value']);
            $table->index(['category', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        if (OriginalGiatepSchema::isActive()) {
            return;
        }

        Schema::dropIfExists('catalog_options');
    }
};
