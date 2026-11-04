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
        // 1. Freigabe-Feld für Familienmitglieder
        Schema::table('recipes', function (Blueprint $table) {
            $table->boolean('is_shared')->default(true)->after('is_favorite');
            $table->index(['user_id', 'is_shared']);
        });

        // 2. Benutzerbezogene Favoritentabelle
        Schema::create('recipe_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('recipe_id')->constrained('recipes')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'recipe_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_favorites');
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_shared']);
            $table->dropColumn('is_shared');
        });
    }
};

