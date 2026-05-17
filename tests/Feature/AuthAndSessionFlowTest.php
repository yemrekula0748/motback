<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use Database\Seeders\RealmSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthAndSessionFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('motonline.server_shared_key', 'test-shared-key');
        config()->set('motonline.game_session_ttl_seconds', 30);

        $this->seed(RealmSeeder::class);
    }

    public function test_user_can_register_and_login(): void
    {
        $registerResponse = $this->postJson('/api/v1/auth/register', [
            'username' => 'alp_han',
            'email' => 'alp.han@example.com',
            'password' => 'Password123!',
        ]);

        $registerResponse
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.username', 'alp_han');

        $this->assertNotEmpty($registerResponse->json('data.access_token'));

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'username' => 'alp_han',
            'password' => 'Password123!',
        ]);

        $loginResponse
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', 'alp.han@example.com');

        $this->assertNotEmpty($loginResponse->json('data.access_token'));
    }

    public function test_faction_can_only_be_set_once(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $firstResponse = $this->patchJson('/api/v1/me/faction', [
            'faction' => 'gokboru',
        ]);

        $firstResponse
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.faction', 'gokboru');

        $secondResponse = $this->patchJson('/api/v1/me/faction', [
            'faction' => 'bozkurt',
        ]);

        $secondResponse
            ->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'FACTION_ALREADY_LOCKED');
    }

    public function test_user_cannot_fetch_another_users_character(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $character = Character::query()->create([
            'user_id' => $owner->id,
            'name' => 'OwnerKnight',
            'class' => 'savasco',
            'level' => 1,
            'experience' => 0,
            'strength' => 12,
            'agility' => 8,
            'intelligence' => 6,
            'endurance' => 12,
            'base_strength' => 12,
            'base_agility' => 8,
            'base_intelligence' => 6,
            'base_endurance' => 12,
            'unspent_stat_points' => 0,
            'max_health' => 340,
            'current_health' => 340,
            'max_mana' => 90,
            'current_mana' => 90,
            'gold' => 0,
            'attack_power' => 30,
            'defense' => 14,
            'current_map' => '/Game/Maps/Gokboru',
            'pos_x' => 0,
            'pos_y' => 0,
            'pos_z' => 0,
        ]);

        Sanctum::actingAs($intruder);

        $response = $this->getJson("/api/v1/characters/{$character->id}");

        $response
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'CHARACTER_NOT_FOUND');
    }

    public function test_game_session_is_consumed_once_by_server(): void
    {
        $user = User::factory()->create([
            'faction' => 'gokboru',
        ]);

        $character = Character::query()->create([
            'user_id' => $user->id,
            'name' => 'SessionHero',
            'class' => 'savasco',
            'level' => 1,
            'experience' => 0,
            'strength' => 12,
            'agility' => 8,
            'intelligence' => 6,
            'endurance' => 12,
            'base_strength' => 12,
            'base_agility' => 8,
            'base_intelligence' => 6,
            'base_endurance' => 12,
            'unspent_stat_points' => 0,
            'max_health' => 340,
            'current_health' => 340,
            'max_mana' => 90,
            'current_mana' => 90,
            'gold' => 0,
            'attack_power' => 30,
            'defense' => 14,
            'current_map' => '/Game/Maps/Gokboru',
            'pos_x' => 0,
            'pos_y' => 0,
            'pos_z' => 0,
        ]);

        Sanctum::actingAs($user);

        $createSessionResponse = $this->postJson('/api/v1/game/session', [
            'character_id' => $character->id,
        ]);

        $createSessionResponse
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.character_id', $character->id);

        $sessionToken = $createSessionResponse->json('data.session_token');
        $realmSlug = $createSessionResponse->json('data.realm.slug');

        $consumeResponse = $this->withHeaders([
            'X-Server-Key' => 'test-shared-key',
        ])->postJson('/api/v1/server/session/consume', [
            'session_token' => $sessionToken,
            'realm_slug' => $realmSlug,
        ]);

        $consumeResponse
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.character.id', $character->id)
            ->assertJsonPath('data.user.id', $user->id);

        $secondConsumeResponse = $this->withHeaders([
            'X-Server-Key' => 'test-shared-key',
        ])->postJson('/api/v1/server/session/consume', [
            'session_token' => $sessionToken,
            'realm_slug' => $realmSlug,
        ]);

        $secondConsumeResponse
            ->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'SESSION_ALREADY_CONSUMED');
    }
}
