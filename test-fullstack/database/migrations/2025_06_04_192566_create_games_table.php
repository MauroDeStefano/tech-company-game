<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration per la tabella games
 * 
 * Gestisce le partite del videogioco. Ogni partita appartiene a un utente
 * e contiene informazioni su patrimonio, stato e timing.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            
            // Foreign key verso users table (deve esistere prima)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Informazioni base partita
            $table->string('name')->nullable()->comment('Nome personalizzato della partita');
            $table->decimal('money', 15, 2)->default(5000.00)->comment('Patrimonio dell azienda in euro');
            
            // Status management
            $table->enum('status', ['active', 'paused', 'game_over'])->default('active');
            
            // Timing management per pause/resume
            $table->timestamp('paused_at')->nullable()->comment('Quando la partita è stata messa in pausa');
            $table->timestamp('resumed_at')->nullable()->comment('Quando la partita è stata ripresa');
            $table->integer('offline_duration_seconds')->default(0)->comment('Tempo totale offline in secondi');
            
            // Setup e configurazione
            $table->boolean('initial_setup_completed')->default(false)->comment('Setup iniziale completato');
            $table->text('notes')->nullable()->comment('Note del giocatore');
            
            // Timestamps standard Laravel
            $table->timestamps();
            
            // Indici per performance
            $table->index(['user_id', 'status'], 'idx_user_active_games');
            $table->index('status', 'idx_game_status');
            $table->index('updated_at', 'idx_last_played');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};