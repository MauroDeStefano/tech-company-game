<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration per la tabella users
 *
 * IMPORTANTE: Questa deve essere la PRIMA migration (000000) perché
 * tutte le altre tabelle hanno foreign key verso users.
 *
 * Gestisce gli utenti che possono registrarsi e giocare.
 * Ogni utente può avere più partite (games).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // Informazioni base utente
            $table->string('name')->comment('Nome completo dell\'utente');
            $table->string('email')->unique()->comment('Email per login e comunicazioni');
            $table->timestamp('email_verified_at')->nullable()->comment('Quando l\'email è stata verificata');
            $table->string('password')->comment('Password hasata con bcrypt');

            // Profilo e preferenze giocatore
            $table->string('avatar_url')->nullable()->comment('URL avatar personalizzato');
            $table->json('preferences')->nullable()->comment('Preferenze di gioco (tema, notifiche, etc)');
            $table->text('bio')->nullable()->comment('Biografia/descrizione opzionale');

            // Statistiche globali cross-game
            $table->integer('total_games_played')->default(0)->comment('Totale partite giocate');
            $table->integer('total_games_won')->default(0)->comment('Partite vinte (non fallite)');
            $table->decimal('best_money_achieved', 15, 2)->default(0)->comment('Miglior patrimonio raggiunto');
            $table->integer('total_projects_completed')->default(0)->comment('Progetti completati in tutte le partite');

            // Livello e progressione utente
            $table->integer('user_level')->default(1)->comment('Livello giocatore (sblocca contenuti)');
            $table->integer('experience_points')->default(0)->comment('Punti esperienza totali');
            $table->json('achievements')->nullable()->comment('Achievement sbloccati');

            // Timing e sessioni
            $table->timestamp('last_login_at')->nullable()->comment('Ultimo accesso');
            $table->timestamp('last_game_played_at')->nullable()->comment('Ultima volta che ha giocato');
            $table->integer('total_play_time_minutes')->default(0)->comment('Tempo totale di gioco in minuti');

            // Account management
            $table->boolean('is_active')->default(true)->comment('Account attivo');
            $table->boolean('email_notifications')->default(true)->comment('Riceve notifiche email');
            $table->string('locale', 5)->default('it')->comment('Lingua preferita (it, en, etc)');

            // Laravel standard
            $table->rememberToken()->comment('Token "ricordami" per login persistente');
            $table->timestamps();

            // Indici per performance
            $table->index('email'); // Già unique, ma esplicito per chiarezza
            $table->index(['user_level', 'experience_points'], 'idx_user_progression');
            $table->index(['total_games_played', 'total_games_won'], 'idx_user_stats');
            $table->index(['is_active', 'last_login_at'], 'idx_active_users');
            $table->index('last_game_played_at', 'idx_recent_players');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};