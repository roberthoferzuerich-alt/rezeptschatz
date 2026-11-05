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
        Schema::table('recipes', function (Blueprint $table) {
            $table->unsignedInteger('calories')->nullable()->after('rest_time_minutes');
            $table->decimal('carbohydrates', 6, 1)->nullable()->after('calories');
            $table->decimal('fat', 6, 1)->nullable()->after('carbohydrates');
            $table->decimal('protein', 6, 1)->nullable()->after('fat');
            $table->text('nutrition_raw')->nullable()->after('protein');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropColumn(['calories', 'carbohydrates', 'fat', 'protein', 'nutrition_raw']);
        });
    }
};
