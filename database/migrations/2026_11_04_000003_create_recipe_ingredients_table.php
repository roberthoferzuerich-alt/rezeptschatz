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
        Schema::create('recipe_ingredients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('recipe_id')->constrained('recipes')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('group_name', 100)->nullable();
            $table->string('name', 255);
            $table->decimal('amount_numeric', 8, 3)->nullable();
            $table->string('unit', 50)->nullable();
            $table->string('amount_text', 100)->nullable();
            $table->timestamps();

            $table->index(['recipe_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_ingredients');
    }
};

