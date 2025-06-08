<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Game>
 */
class GameFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Game::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Nomi di software house creativi
        $companyNames = [
            'TechVision Solutions',
            'CodeCraft Studio', 
            'InnovateLab',
            'DigitalForge',
            'ByteWorks',
            'NextGen Software',
            'CloudSprint',
            'AgileMinds',
            'DevStorm',
            'PixelForge',
            'DataDriven Co.',
            'SmartCode Labs',
            'FutureWeb Systems',
            'RapidDev Studio',
            'EliteCode Solutions'
        ];

        // Note strategiche casuali
        $strategicNotes = [
            'Focus su progetti ad alto valore',
            'Crescita graduale del team',
            'Specializzazione in web development',
            'Puntare su clienti enterprise',
            'Investire in formazione del team',
            'Diversificare i servizi offerti',
            'Ottimizzare i processi interni',
            'Espansione nel mobile development',
            'Partnership strategiche',
            'Innovazione tecnologica continua'
        ];

        return [
            'user_id' => User::factory(),
            'name' => $this->faker->randomElement($companyNames),
            'money' => $this->faker->randomFloat(2, 2000, 15000), // Da 2k a 15k euro
            'status' => $this->faker->randomElement([
                Game::STATUS_ACTIVE,
                Game::STATUS_PAUSED, 
                Game::STATUS_GAME_OVER
            ]),
            'notes' => $this->faker->optional(0.7)->randomElement($strategicNotes), // 70% probabilità
            'initial_setup_completed' => true,
            'paused_at' => null,
            'resumed_at' => null,
            'offline_duration_seconds' => $this->faker->numberBetween(0, 7200), // Max 2 ore
            'created_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ];
    }

    /**
     * Indica che il gioco è attivo
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Game::STATUS_ACTIVE,
            'paused_at' => null,
        ]);
    }

    /**
     * Indica che il gioco è in pausa
     */
    public function paused(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Game::STATUS_PAUSED,
            'paused_at' => $this->faker->dateTimeBetween('-2 hours', 'now'),
        ]);
    }

    /**
     * Indica che il gioco è finito (game over)
     */
    public function gameOver(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Game::STATUS_GAME_OVER,
            'money' => $this->faker->randomFloat(2, -2000, 500), // Soldi finiti
            'notes' => $attributes['notes'] . "\nGame Over: bankruptcy",
        ]);
    }

    /**
     * Partita per principianti (molti soldi)
     */
    public function beginner(): static
    {
        return $this->state(fn (array $attributes) => [
            'money' => $this->faker->randomFloat(2, 10000, 25000),
            'status' => Game::STATUS_ACTIVE,
            'notes' => 'Modalità principiante - patrimonio elevato',
        ]);
    }

    /**
     * Partita difficile (pochi soldi)
     */
    public function hardcore(): static
    {
        return $this->state(fn (array $attributes) => [
            'money' => $this->faker->randomFloat(2, 1000, 3000),
            'status' => Game::STATUS_ACTIVE,
            'notes' => 'Modalità hardcore - basso budget iniziale',
        ]);
    }

    /**
     * Partita di successo (molto profitto)
     */
    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'money' => $this->faker->randomFloat(2, 20000, 50000),
            'status' => Game::STATUS_ACTIVE,
            'offline_duration_seconds' => $this->faker->numberBetween(0, 1000),
            'notes' => 'Azienda di successo - alta crescita',
        ]);
    }

    /**
     * Partita nuova (appena iniziata)
     */
    public function fresh(): static
    {
        return $this->state(fn (array $attributes) => [
            'money' => Game::INITIAL_MONEY,
            'status' => Game::STATUS_ACTIVE,
            'initial_setup_completed' => false,
            'offline_duration_seconds' => 0,
            'notes' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Associa la partita a un utente specifico
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Crea una partita con team e progetti iniziali
     */
    public function withInitialSetup(): static
    {
        return $this->afterCreating(function (Game $game) {
            // Non chiamare initializeWithStartingTeam() se è già stato fatto
            if (!$game->initial_setup_completed) {
                $game->initializeWithStartingTeam();
            }
        });
    }

    /**
     * Partita con tempo offline lungo
     */
    public function longOffline(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Game::STATUS_PAUSED,
            'paused_at' => $this->faker->dateTimeBetween('-1 week', '-1 day'),
            'offline_duration_seconds' => $this->faker->numberBetween(86400, 604800), // 1-7 giorni
        ]);
    }

    /**
     * Partita con note personalizzate
     */
    public function withNotes(string $notes): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => $notes,
        ]);
    }

    /**
     * Partita con soldi specifici
     */
    public function withMoney(float $money): static
    {
        return $this->state(fn (array $attributes) => [
            'money' => $money,
        ]);
    }
}