<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('color', 20)->default('#8ea92c');
            $table->string('icon', 50)->nullable();
            $table->unsignedBigInteger('server_version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'name']);
            $table->index(['user_id', 'updated_at']);
        });

        Schema::create('recipe_tag', function (Blueprint $table) {
            $table->foreignUuid('recipe_id')->constrained('recipes')->cascadeOnDelete();
            $table->foreignUuid('tag_id')->constrained('tags')->cascadeOnDelete();
            $table->primary(['recipe_id', 'tag_id']);
            $table->index('tag_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_tag');
        Schema::dropIfExists('tags');
    }
};
