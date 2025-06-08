<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration per la tabella developers
 *
 * Gestisce gli sviluppatori che appartengono a una partita.
 * Ogni developer ha un livello di seniority che influenza i tempi di completamento.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('developers', function (Blueprint $table) {
            $table->id();

            // Foreign key verso games
            $table->foreignId('game_id')->constrained()->onDelete('cascade');

            // Informazioni base del developer
            $table->string('name')->comment('Nome del developer');
            $table->integer('seniority')->default(1)->comment('Livello seniority (1-5)');

            // Status operativo
            $table->boolean('is_busy')->default(false)->comment('Se è occupato in un progetto');
            $table->boolean('is_market_hire')->default(false)->comment('Se è stato assunto dal mercato');

            // Costi e caratteristiche economiche
            $table->decimal('monthly_salary', 8, 2)->default(2000.00)->comment('Costo mensile in euro');
            $table->decimal('hiring_cost', 8, 2)->nullable()->comment('Costo di assunzione (se dal mercato)');

            // Statistiche e progressione
            $table->integer('projects_completed')->default(0)->comment('Progetti completati dal developer');
            $table->timestamp('last_project_completed_at')->nullable();
            $table->timestamp('hire_date')->nullable();

            // Metadati aggiuntivi
            $table->json('metadata')->nullable()->comment('Dati aggiuntivi (specializzazioni, bonus, etc)');

            $table->timestamps();

            // Indici per performance
            $table->index(['game_id', 'is_busy'], 'idx_game_available_devs');
            $table->index(['game_id', 'seniority'], 'idx_game_dev_seniority');
            $table->index('is_market_hire', 'idx_market_developers');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('developers');
    }
};