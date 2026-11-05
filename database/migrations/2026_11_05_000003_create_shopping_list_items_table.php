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
        Schema::create('shopping_list_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->uuid('recipe_id')->nullable();
            $table->string('name');
            $table->decimal('amount_numeric', 10, 2)->nullable();
            $table->string('unit', 50)->nullable();
            $table->string('amount_text', 100)->nullable();
            $table->string('category', 50)->nullable(); // z.B. Gemüse, Milchprodukte, etc.
            $table->boolean('is_completed')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('recipe_id')->references('id')->on('recipes')->nullOnDelete();
            $table->index(['user_id', 'is_completed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopping_list_items');
    }
};
