<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration per la tabella projects
 *
 * Gestisce i progetti di sviluppo. I progetti vengono creati dai commerciali
 * e poi assegnati agli sviluppatori per essere completati.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->foreignId('developer_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('generated_by_sales_person_id')->nullable()->constrained('sales_people')->onDelete('set null');

            // Informazioni progetto
            $table->string('name')->comment('Nome del progetto');
            $table->text('description')->nullable()->comment('Descrizione del progetto');
            $table->integer('complexity')->comment('Livello complessità (1-5)');
            $table->decimal('value', 10, 2)->comment('Valore economico del progetto in euro');

            // Status e timing
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->timestamp('assigned_at')->nullable()->comment('Quando è stato assegnato al developer');
            $table->timestamp('started_at')->nullable()->comment('Quando il developer ha iniziato');
            $table->timestamp('estimated_completion_at')->nullable()->comment('Stima completamento');
            $table->timestamp('completed_at')->nullable()->comment('Quando è stato completato');

            // Calcoli di tempo e performance
            $table->integer('estimated_duration_minutes')->nullable()->comment('Durata stimata in minuti');
            $table->integer('actual_duration_minutes')->nullable()->comment('Durata effettiva in minuti');

            // Bonus e moltiplicatori
            $table->decimal('difficulty_multiplier', 4, 2)->default(1.0)->comment('Moltiplicatore difficoltà');
            $table->decimal('rush_bonus', 4, 2)->default(0)->comment('Bonus per completamento veloce');

            // Metadati
            $table->json('metadata')->nullable()->comment('Dati aggiuntivi del progetto');

            $table->timestamps();

            // Indici per performance
            $table->index(['game_id', 'status'], 'idx_game_project_status');
            $table->index(['game_id', 'developer_id'], 'idx_game_developer_projects');
            $table->index(['status', 'estimated_completion_at'], 'idx_completion_queue');
            $table->index(['game_id', 'completed_at'], 'idx_completed_projects');
            $table->index('value', 'idx_project_value');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};