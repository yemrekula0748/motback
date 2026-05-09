<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CharacterStatPersistenceTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function actingAsNewUser(): User
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        return $user;
    }

    // -------------------------------------------------------------------------
    // 1. GET /characters returns all required stat fields
    // -------------------------------------------------------------------------

    public function test_character_list_returns_all_stat_fields(): void
    {
        $user = $this->actingAsNewUser();
        Character::factory()->create(['user_id' => $user->id, 'class' => 'savasco']);

        $response = $this->getJson('/api/characters');

        $response->assertOk()->assertJsonStructure([
            'success',
            'characters' => [
                '*' => [
                    'id', 'user_id', 'name', 'class', 'level', 'experience',
                    'strength', 'agility', 'intelligence', 'endurance',
                    'base_strength', 'base_agility', 'base_intelligence', 'base_endurance',
                    'vitality', 'dexterity',
                    'unspent_stat_points',
                    'max_health', 'current_health',
                    'max_mana', 'current_mana',
                    'attack_power', 'defense',
                    'gold', 'silver',
                    'current_map', 'pos_x', 'pos_y', 'pos_z',
                ],
            ],
        ]);
    }

    // -------------------------------------------------------------------------
    // 2. GET /characters/{id} returns all required stat fields
    // -------------------------------------------------------------------------

    public function test_character_show_returns_all_stat_fields(): void
    {
        $user      = $this->actingAsNewUser();
        $character = Character::factory()->create(['user_id' => $user->id, 'class' => 'okcu']);

        $response = $this->getJson("/api/characters/{$character->id}");

        $response->assertOk()->assertJsonStructure([
            'success',
            'character' => [
                'id', 'user_id', 'name', 'class', 'level', 'experience',
                'strength', 'agility', 'intelligence', 'endurance',
                'base_strength', 'base_agility', 'base_intelligence', 'base_endurance',
                'vitality', 'dexterity',
                'unspent_stat_points',
                'max_health', 'current_health',
                'max_mana', 'current_mana',
                'attack_power', 'defense',
                'gold', 'silver',
                'current_map', 'pos_x', 'pos_y', 'pos_z',
            ],
        ]);
    }

    // -------------------------------------------------------------------------
    // 3. PATCH /characters/{id} persists current stats
    // -------------------------------------------------------------------------

    public function test_patch_updates_current_stats(): void
    {
        $user      = $this->actingAsNewUser();
        $character = Character::factory()->create(['user_id' => $user->id, 'class' => 'savasco']);

        $response = $this->patchJson("/api/characters/{$character->id}", [
            'strength'  => 20,
            'agility'   => 12,
            'endurance' => 18,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('character.strength', 20)
            ->assertJsonPath('character.agility', 12)
            ->assertJsonPath('character.endurance', 18);

        $this->assertDatabaseHas('characters', [
            'id'       => $character->id,
            'strength' => 20,
            'agility'  => 12,
            'endurance'=> 18,
        ]);
    }

    // -------------------------------------------------------------------------
    // 4. PATCH /characters/{id} persists base_* and unspent_stat_points
    // -------------------------------------------------------------------------

    public function test_patch_updates_base_stats_and_unspent_points(): void
    {
        $user      = $this->actingAsNewUser();
        $character = Character::factory()->create(['user_id' => $user->id, 'class' => 'okcu']);

        $response = $this->patchJson("/api/characters/{$character->id}", [
            'strength'            => 18,
            'base_strength'       => 10,
            'base_agility'        => 18,
            'base_intelligence'   => 8,
            'base_endurance'      => 10,
            'unspent_stat_points' => 5,
        ]);

        $response->assertOk()
            ->assertJsonPath('character.strength', 18)
            ->assertJsonPath('character.base_strength', 10)
            ->assertJsonPath('character.base_agility', 18)
            ->assertJsonPath('character.unspent_stat_points', 5);

        $this->assertDatabaseHas('characters', [
            'id'                  => $character->id,
            'strength'            => 18,
            'base_strength'       => 10,
            'unspent_stat_points' => 5,
        ]);
    }

    // -------------------------------------------------------------------------
    // 5. vitality fallback: if null in DB, response returns endurance value
    // -------------------------------------------------------------------------

    public function test_vitality_falls_back_to_endurance_when_null(): void
    {
        $user      = $this->actingAsNewUser();
        $character = Character::factory()->create([
            'user_id'  => $user->id,
            'class'    => 'savasco',
            'endurance'=> 20,
            'vitality' => null,
        ]);

        $response = $this->getJson("/api/characters/{$character->id}");

        $response->assertOk()->assertJsonPath('character.vitality', 20);
    }

    // -------------------------------------------------------------------------
    // 6. dexterity fallback: if null in DB, response returns agility value
    // -------------------------------------------------------------------------

    public function test_dexterity_falls_back_to_agility_when_null(): void
    {
        $user      = $this->actingAsNewUser();
        $character = Character::factory()->create([
            'user_id'   => $user->id,
            'class'     => 'okcu',
            'agility'   => 22,
            'dexterity' => null,
        ]);

        $response = $this->getJson("/api/characters/{$character->id}");

        $response->assertOk()->assertJsonPath('character.dexterity', 22);
    }

    // -------------------------------------------------------------------------
    // 7. base_* fallback: if stored as 0 (pre-migration row), response returns
    //    the current stat value instead
    // -------------------------------------------------------------------------

    public function test_base_stat_falls_back_to_current_stat_when_zero(): void
    {
        $user      = $this->actingAsNewUser();
        $character = Character::factory()->create([
            'user_id'       => $user->id,
            'class'         => 'saman',
            'strength'      => 5,
            'base_strength' => 0, // simulates pre-migration row
        ]);

        $response = $this->getJson("/api/characters/{$character->id}");

        // base_strength = 0 (falsy) -> resource returns strength (5)
        $response->assertOk()->assertJsonPath('character.base_strength', 5);
    }

    // -------------------------------------------------------------------------
    // 8. POST /characters sets correct starting stats per class;
    //    base_* == current stats; unspent_stat_points == 0 at creation
    // -------------------------------------------------------------------------

    public function test_create_sets_starting_stats_for_savasco(): void
    {
        $this->actingAsNewUser();

        $response = $this->postJson('/api/characters', [
            'name'  => 'Savasco1',
            'class' => 'savasco',
        ]);

        $response->assertCreated();
        $char = $response->json('character');

        // base_* mirrors current at creation
        $this->assertSame($char['base_strength'],     $char['strength']);
        $this->assertSame($char['base_agility'],      $char['agility']);
        $this->assertSame($char['base_intelligence'], $char['intelligence']);
        $this->assertSame($char['base_endurance'],    $char['endurance']);
        $this->assertSame(0, $char['unspent_stat_points']);

        // savasco: strength > agility
        $this->assertGreaterThan($char['agility'], $char['strength']);
    }

    public function test_create_sets_starting_stats_for_okcu(): void
    {
        $this->actingAsNewUser();

        $response = $this->postJson('/api/characters', [
            'name'  => 'Okcu0001',
            'class' => 'okcu',
        ]);

        $response->assertCreated();
        $char = $response->json('character');

        // okcu: agility > strength
        $this->assertGreaterThan($char['strength'], $char['agility']);
        $this->assertSame(0, $char['unspent_stat_points']);
    }

    public function test_create_sets_starting_stats_for_saman(): void
    {
        $this->actingAsNewUser();

        $response = $this->postJson('/api/characters', [
            'name'  => 'Saman001',
            'class' => 'saman',
        ]);

        $response->assertCreated();
        $char = $response->json('character');

        // saman: intelligence > strength
        $this->assertGreaterThan($char['strength'], $char['intelligence']);
        $this->assertSame(0, $char['unspent_stat_points']);
    }

    // -------------------------------------------------------------------------
    // 9. POST /characters/{id}/respec resets current stats to base and refunds
    //    the spent point delta back into unspent_stat_points
    // -------------------------------------------------------------------------

    public function test_respec_resets_stats_to_base_and_refunds_spent_points(): void
    {
        $user      = $this->actingAsNewUser();
        $character = Character::factory()->create([
            'user_id'             => $user->id,
            'class'               => 'savasco',
            'strength'            => 25,
            'base_strength'       => 15,
            'agility'             => 14,
            'base_agility'        => 10,
            'intelligence'        => 7,
            'base_intelligence'   => 5,
            'endurance'           => 20,
            'base_endurance'      => 15,
            'unspent_stat_points' => 0,
        ]);

        $response = $this->postJson("/api/characters/{$character->id}/respec");

        $response->assertOk()->assertJsonPath('success', true);
        $char = $response->json('character');

        // Stats reset to base
        $this->assertSame(15, $char['strength']);
        $this->assertSame(10, $char['agility']);
        $this->assertSame(5,  $char['intelligence']);
        $this->assertSame(15, $char['endurance']);

        // Refunded: (25-15)+(14-10)+(7-5)+(20-15) = 10+4+2+5 = 21
        $this->assertSame(21, $char['unspent_stat_points']);
    }

    public function test_respec_fails_when_base_stats_not_initialised(): void
    {
        $user      = $this->actingAsNewUser();
        $character = Character::factory()->create([
            'user_id'           => $user->id,
            'class'             => 'savasco',
            'base_strength'     => 0,
            'base_agility'      => 0,
            'base_intelligence' => 0,
            'base_endurance'    => 0,
        ]);

        $response = $this->postJson("/api/characters/{$character->id}/respec");

        $response->assertStatus(422)->assertJsonPath('success', false);
    }

    // -------------------------------------------------------------------------
    // 10. PATCH with vitality/dexterity stores them and response reflects values
    // -------------------------------------------------------------------------

    public function test_patch_stores_vitality_and_dexterity_explicitly(): void
    {
        $user      = $this->actingAsNewUser();
        $character = Character::factory()->create(['user_id' => $user->id, 'class' => 'savasco']);

        $response = $this->patchJson("/api/characters/{$character->id}", [
            'vitality'  => 25,
            'dexterity' => 14,
        ]);

        $response->assertOk()
            ->assertJsonPath('character.vitality', 25)
            ->assertJsonPath('character.dexterity', 14);

        $this->assertDatabaseHas('characters', [
            'id'        => $character->id,
            'vitality'  => 25,
            'dexterity' => 14,
        ]);
    }
}
