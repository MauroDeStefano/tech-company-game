<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration per la tabella market_developers
 * 
 * Gestisce i developer disponibili per l'assunzione sul mercato.
 * Questa è una tabella "template" da cui vengono creati i developer effettivi
 * quando vengono assunti nelle partite.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('market_developers', function (Blueprint $table) {
            $table->id();
            
            // Informazioni base
            $table->string('name')->comment('Nome del developer disponibile');
            $table->integer('seniority')->comment('Livello seniority (1-5)');
            
            // Costi di assunzione e gestione
            $table->decimal('hiring_cost', 8, 2)->comment('Costo per assumerlo');
            $table->decimal('monthly_cost', 8, 2)->comment('Costo mensile una volta assunto');
            
            // Caratteristiche speciali
            $table->string('specialization')->nullable()->comment('Specializzazione (frontend, backend, fullstack, etc)');
            $table->text('description')->nullable()->comment('Descrizione delle skills');
            
            // Disponibilità e rarità
            $table->boolean('is_available')->default(true)->comment('Se è disponibile per assunzione');
            $table->enum('rarity', ['common', 'uncommon', 'rare', 'epic', 'legendary'])->default('common');
            $table->integer('availability_weight')->default(100)->comment('Peso per randomizzazione (più alto = più comune)');
            
            // Bonus e abilità speciali
            $table->decimal('productivity_bonus', 4, 2)->default(0)->comment('Bonus produttività (riduzione tempi)');
            $table->decimal('quality_bonus', 4, 2)->default(0)->comment('Bonus qualità (aumento valore progetti)');
            
            // Requisiti di assunzione
            $table->integer('min_company_level')->default(1)->comment('Livello minimo azienda per assumerlo');
            $table->decimal('min_money_required', 10, 2)->default(0)->comment('Denaro minimo richiesto per assumerlo');
            
            // Metadati e configurazione
            $table->json('skills')->nullable()->comment('Skills specifiche (array di tecnologie)');
            $table->json('metadata')->nullable()->comment('Dati aggiuntivi configurabili');
            
            $table->timestamps();
            
            // Indici per performance
            $table->index(['is_available', 'seniority'], 'idx_available_by_seniority');
            $table->index(['rarity', 'availability_weight'], 'idx_rarity_weight');
            $table->index('specialization', 'idx_specialization');
            $table->index(['min_company_level', 'min_money_required'], 'idx_requirements');
            
            // Vincoli di business logic
            $table->check('seniority >= 1 AND seniority <= 5', 'chk_valid_seniority');
            $table->check('hiring_cost >= 0', 'chk_positive_hiring_cost');
            $table->check('monthly_cost >= 0', 'chk_positive_monthly_cost');
            $table->check('availability_weight >= 0', 'chk_positive_weight');
            $table->check('min_company_level >= 1', 'chk_valid_min_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_developers');
    }
};