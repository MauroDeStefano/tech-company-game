<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration per la tabella project_generations
 * 
 * Gestisce i processi di generazione progetti da parte dei commerciali.
 * Ogni commerciale può generare un solo progetto alla volta.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('project_generations', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->foreignId('sales_person_id')->constrained()->onDelete('cascade');
            $table->foreignId('generated_project_id')->nullable()->constrained('projects')->onDelete('set null');
            
            // Status e timing
            $table->enum('status', ['in_progress', 'completed', 'cancelled'])->default('in_progress');
            $table->timestamp('started_at')->comment('Quando è iniziata la generazione');
            $table->timestamp('estimated_completion_at')->comment('Stima completamento generazione');
            $table->timestamp('completed_at')->nullable()->comment('Quando è stata completata');
            
            // Parametri di generazione
            $table->integer('estimated_duration_minutes')->comment('Durata stimata generazione in minuti');
            $table->integer('actual_duration_minutes')->nullable()->comment('Durata effettiva in minuti');
            
            // Preview del progetto che verrà generato
            $table->integer('target_complexity')->nullable()->comment('Complessità target del progetto');
            $table->decimal('target_value', 10, 2)->nullable()->comment('Valore target del progetto');
            $table->string('target_name')->nullable()->comment('Nome target del progetto');
            
            // Bonus e moltiplicatori applicati
            $table->decimal('experience_multiplier', 4, 2)->default(1.0)->comment('Moltiplicatore esperienza commerciale');
            $table->decimal('market_conditions', 4, 2)->default(1.0)->comment('Condizioni di mercato');
            
            // Metadati
            $table->json('generation_parameters')->nullable()->comment('Parametri di generazione');
            
            $table->timestamps();
            
            // Indici per performance
            $table->index(['game_id', 'status'], 'idx_game_generation_status');
            $table->index(['sales_person_id', 'status'], 'idx_sales_active_generations');
            $table->index(['status', 'estimated_completion_at'], 'idx_generation_completion_queue');
            $table->index(['game_id', 'completed_at'], 'idx_completed_generations');
            
            // Vincoli di business logic
            $table->check('estimated_duration_minutes > 0', 'chk_positive_duration');
            $table->check('experience_multiplier > 0', 'chk_positive_experience_multiplier');
            $table->check('market_conditions > 0', 'chk_positive_market_conditions');
            
            // Vincolo: un commerciale può avere solo una generazione attiva
            $table->unique(['sales_person_id', 'status'], 'unq_one_active_generation_per_sales');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_generations');
    }
};