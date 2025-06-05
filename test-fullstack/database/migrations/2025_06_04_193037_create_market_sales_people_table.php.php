<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration per la tabella market_sales_people
 *
 * Gestisce i commerciali disponibili per l'assunzione sul mercato.
 * Questa è una tabella "template" da cui vengono creati i sales people effettivi
 * quando vengono assunti nelle partite.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('market_sales_people', function (Blueprint $table) {
            $table->id();

            // Informazioni base
            $table->string('name')->comment('Nome del commerciale disponibile');
            $table->integer('experience')->comment('Livello esperienza (1-5)');

            // Costi di assunzione e gestione
            $table->decimal('hiring_cost', 8, 2)->comment('Costo per assumerlo');
            $table->decimal('monthly_cost', 8, 2)->comment('Costo mensile una volta assunto');

            // Caratteristiche speciali
            $table->string('specialization')->nullable()->comment('Specializzazione (enterprise, startup, international, etc)');
            $table->text('description')->nullable()->comment('Descrizione delle competenze commerciali');

            // Disponibilità e rarità
            $table->boolean('is_available')->default(true)->comment('Se è disponibile per assunzione');
            $table->enum('rarity', ['common', 'uncommon', 'rare', 'epic', 'legendary'])->default('common');
            $table->integer('availability_weight')->default(100)->comment('Peso per randomizzazione (più alto = più comune)');

            // Bonus e abilità speciali
            $table->decimal('value_multiplier', 4, 2)->default(1.0)->comment('Moltiplicatore valore progetti generati');
            $table->decimal('speed_bonus', 4, 2)->default(0)->comment('Bonus velocità generazione (riduzione tempi)');
            $table->decimal('quality_bonus', 4, 2)->default(0)->comment('Bonus qualità progetti (complessità più alta)');

            // Requisiti di assunzione
            $table->integer('min_company_level')->default(1)->comment('Livello minimo azienda per assumerlo');
            $table->decimal('min_money_required', 10, 2)->default(0)->comment('Denaro minimo richiesto per assumerlo');
            $table->integer('min_projects_completed')->default(0)->comment('Progetti minimi completati richiesti');

            // Network e connessioni
            $table->json('market_connections')->nullable()->comment('Connessioni di mercato (settori, clienti)');
            $table->decimal('network_bonus', 4, 2)->default(0)->comment('Bonus da network professionale');

            // Metadati e configurazione
            $table->json('skills')->nullable()->comment('Skills specifiche (negoziazione, presentazioni, etc)');
            $table->json('metadata')->nullable()->comment('Dati aggiuntivi configurabili');

            $table->timestamps();

            // Indici per performance
            $table->index(['is_available', 'experience'], 'idx_available_by_experience');
            $table->index(['rarity', 'availability_weight'], 'idx_market_sales_rarity_weight');
            $table->index('specialization', 'idx_market_sales_specialization');
            $table->index(['min_company_level', 'min_money_required'], 'idx_market_sales_requirements');
            $table->index('value_multiplier', 'idx_value_multiplier');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_sales_people');
    }
};