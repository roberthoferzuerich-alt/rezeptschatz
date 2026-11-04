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
        Schema::create('sync_mutations_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->char('mutation_id', 36);
            $table->string('entity_type', 50);
            $table->char('entity_id', 36);
            $table->string('action', 20);
            $table->unsignedBigInteger('applied_version');
            $table->json('response_payload')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'mutation_id']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_mutations_log');
    }
};

