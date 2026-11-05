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
        Schema::create('meal_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->uuid('recipe_id')->nullable();
            $table->string('custom_title')->nullable();
            $table->date('date');
            $table->string('meal_type', 20)->default('dinner'); // lunch, dinner
            $table->integer('portions')->default(4);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('recipe_id')->references('id')->on('recipes')->nullOnDelete();
            $table->index(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_plans');
    }
};

