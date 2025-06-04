<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function authenticated_user_can_view_their_profile()
    {
        // Arrange
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Act
        $response = $this->getJson('/api/user');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'email_verified_at',
                    'created_at',
                    'updated_at'
                ],
                'meta' => [
                    'version',
                    'timestamp'
                ]
            ])
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_view_profile()
    {
        // Act
        $response = $this->getJson('/api/user');

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function user_can_update_their_name_and_email()
    {
        // Arrange
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $updateData = [
            'name' => 'New Name',
            'email' => 'newemail@example.com',
        ];

        // Act
        $response = $this->putJson('/api/user', $updateData);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Profilo aggiornato con successo',
                'data' => [
                    'name' => 'New Name',
                    'email' => 'newemail@example.com',
                ]
            ]);

        // Verifica che i dati siano stati aggiornati nel database
        $user->refresh();
        $this->assertEquals('New Name', $user->name);
        $this->assertEquals('newemail@example.com', $user->email);
    }

    /** @test */
    public function user_can_update_their_password()
    {
        // Arrange
        $user = User::factory()->create([
            'password' => Hash::make('old-password')
        ]);
        Sanctum::actingAs($user);

        $updateData = [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ];

        // Act
        $response = $this->putJson('/api/user', $updateData);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Profilo aggiornato con successo'
            ]);

        // Verifica che la password sia stata aggiornata
        $user->refresh();
        $this->assertTrue(Hash::check('new-password', $user->password));
    }

    /** @test */
    public function user_can_update_name_email_and_password_together()
    {
        // Arrange
        $user = User::factory()->create([
            'password' => Hash::make('old-password')
        ]);
        Sanctum::actingAs($user);

        $updateData = [
            'name' => 'Complete New Name',
            'email' => 'completenew@example.com',
            'current_password' => 'old-password',
            'password' => 'brand-new-password',
            'password_confirmation' => 'brand-new-password',
        ];

        // Act
        $response = $this->putJson('/api/user', $updateData);

        // Assert
        $response->assertStatus(200);

        // Verifica che tutti i dati siano stati aggiornati
        $user->refresh();
        $this->assertEquals('Complete New Name', $user->name);
        $this->assertEquals('completenew@example.com', $user->email);
        $this->assertTrue(Hash::check('brand-new-password', $user->password));
    }

    /** @test */
    public function user_cannot_update_profile_with_invalid_data()
    {
        // Arrange
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $invalidData = [
            'name' => '', // Nome vuoto
            'email' => 'invalid-email', // Email non valida
            'password' => '123', // Password troppo corta
        ];

        // Act
        $response = $this->putJson('/api/user', $invalidData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /** @test */
    public function user_cannot_update_profile_without_current_password_when_changing_password()
    {
        // Arrange
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $updateData = [
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
            // Manca current_password
        ];

        // Act
        $response = $this->putJson('/api/user', $updateData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);
    }

    /** @test */
    public function user_cannot_update_profile_with_wrong_current_password()
    {
        // Arrange
        $user = User::factory()->create([
            'password' => Hash::make('correct-password')
        ]);
        Sanctum::actingAs($user);

        $updateData = [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ];

        // Act
        $response = $this->putJson('/api/user', $updateData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);
    }

    /** @test */
    public function user_cannot_update_profile_with_mismatched_password_confirmation()
    {
        // Arrange
        $user = User::factory()->create([
            'password' => Hash::make('old-password')
        ]);
        Sanctum::actingAs($user);

        $updateData = [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'different-password', // Non combacia
        ];

        // Act
        $response = $this->putJson('/api/user', $updateData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function user_cannot_use_duplicate_email()
    {
        // Arrange
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $updateData = [
            'email' => 'existing@example.com', // Email già in uso
        ];

        // Act
        $response = $this->putJson('/api/user', $updateData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function user_can_keep_same_email_when_updating_other_fields()
    {
        // Arrange
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'user@example.com'
        ]);
        Sanctum::actingAs($user);

        $updateData = [
            'name' => 'New Name',
            'email' => 'user@example.com', // Stessa email
        ];

        // Act
        $response = $this->putJson('/api/user', $updateData);

        // Assert
        $response->assertStatus(200);
        
        $user->refresh();
        $this->assertEquals('New Name', $user->name);
        $this->assertEquals('user@example.com', $user->email);
    }

    /** @test */
    public function user_can_delete_their_account()
    {
        // Arrange
        $user = User::factory()->create();
        
        // Crea alcuni token per l'utente
        $token = $user->createToken('test-token');
        Sanctum::actingAs($user, ['*'], $token->plainTextToken);

        // Act
        $response = $this->deleteJson('/api/user');

        // Assert
        $response->assertStatus(204);

        // Verifica che l'utente sia stato eliminato
        $this->assertDatabaseMissing('users', ['id' => $user->id]);

        // Verifica che i token siano stati revocati
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class
        ]);
    }

    /** @test */
    public function only_authenticated_users_can_update_profile()
    {
        // Act
        $response = $this->putJson('/api/user', [
            'name' => 'Hacker Name'
        ]);

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function only_authenticated_users_can_delete_account()
    {
        // Act
        $response = $this->deleteJson('/api/user');

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function user_cannot_update_another_users_profile()
    {
        // Arrange
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        Sanctum::actingAs($user1);

        // Act - Prova ad aggiornare user2 (dovrebbe fallire con la nostra logica di autorizzazione)
        $response = $this->putJson('/api/user', [
            'name' => 'Hacked Name'
        ]);

        // Assert - L'aggiornamento dovrebbe funzionare solo per l'utente autenticato
        $response->assertStatus(200);
        
        // Verifica che sia stato aggiornato user1, non user2
        $user1->refresh();
        $user2->refresh();
        $this->assertEquals('Hacked Name', $user1->name);
        $this->assertNotEquals('Hacked Name', $user2->name);
    }

    /** @test */
    public function profile_update_handles_partial_data()
    {
        // Arrange
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com'
        ]);
        Sanctum::actingAs($user);

        // Act - Aggiorna solo il nome
        $response = $this->putJson('/api/user', [
            'name' => 'Only Name Changed'
        ]);

        // Assert
        $response->assertStatus(200);
        
        $user->refresh();
        $this->assertEquals('Only Name Changed', $user->name);
        $this->assertEquals('original@example.com', $user->email); // Email rimasta uguale
    }

    /** @test */
    public function user_resource_returns_correct_structure()
    {
        // Arrange
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'email_verified_at' => now()
        ]);
        Sanctum::actingAs($user);

        // Act
        $response = $this->getJson('/api/user');

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                ],
                'meta' => [
                    'version' => '1.0'
                ]
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name', 
                    'email',
                    'email_verified_at',
                    'created_at',
                    'updated_at'
                ],
                'meta' => [
                    'version',
                    'timestamp'
                ]
            ]);

        // Verifica che campi sensibili non siano presenti
        $response->assertJsonMissing(['password', 'remember_token']);
    }

    /** @test */
    public function name_validation_requires_minimum_length()
    {
        // Arrange
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Act
        $response = $this->putJson('/api/user', [
            'name' => 'A' // Solo 1 carattere
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function email_validation_requires_valid_format()
    {
        // Arrange
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $invalidEmails = [
            'not-an-email',
            '@example.com',
            'user@',
            'user@.com',
            'user space@example.com'
        ];

        foreach ($invalidEmails as $invalidEmail) {
            // Act
            $response = $this->putJson('/api/user', [
                'email' => $invalidEmail
            ]);

            // Assert
            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        }
    }
}