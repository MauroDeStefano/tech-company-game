<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration per la tabella sales_people
 * 
 * Gestisce i commerciali che appartengono a una partita.
 * Ogni commerciale ha un livello di esperienza che influenza:
 * - Tempo di generazione progetti
 * - Valore dei progetti generati
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sales_people', function (Blueprint $table) {
            $table->id();
            
            // Foreign key verso games
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            
            // Informazioni base del commerciale
            $table->string('name')->comment('Nome del commerciale');
            $table->integer('experience')->default(1)->comment('Livello esperienza (1-5)');
            
            // Status operativo
            $table->boolean('is_busy')->default(false)->comment('Se sta generando un progetto');
            $table->boolean('is_market_hire')->default(false)->comment('Se è stato assunto dal mercato');
            
            // Costi e caratteristiche economiche
            $table->decimal('monthly_cost', 8, 2)->default(1500.00)->comment('Costo mensile in euro');
            $table->decimal('hiring_cost', 8, 2)->nullable()->comment('Costo di assunzione (se dal mercato)');
            
            // Statistiche e performance
            $table->integer('projects_generated')->default(0)->comment('Progetti generati dal commerciale');
            $table->decimal('total_value_generated', 12, 2)->default(0)->comment('Valore totale progetti generati');
            $table->timestamp('last_project_generated_at')->nullable();
            
            // Moltiplicatori e bonus
            $table->decimal('value_multiplier', 4, 2)->default(1.0)->comment('Moltiplicatore valore progetti');
            
            // Metadati aggiuntivi
            $table->json('metadata')->nullable()->comment('Dati aggiuntivi (specializzazioni, bonus, etc)');
            
            $table->timestamps();
            
            // Indici per performance
            $table->index(['game_id', 'is_busy'], 'idx_game_available_sales');
            $table->index(['game_id', 'experience'], 'idx_game_sales_experience');
            $table->index('is_market_hire', 'idx_market_sales_people');
            $table->index(['total_value_generated', 'projects_generated'], 'idx_sales_performance');
            
            // Vincoli di business logic
            $table->check('experience >= 1 AND experience <= 5', 'chk_valid_experience');
            $table->check('monthly_cost >= 0', 'chk_positive_monthly_cost');
            $table->check('value_multiplier > 0', 'chk_positive_multiplier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_people');
    }
};