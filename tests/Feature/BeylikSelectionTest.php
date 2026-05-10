<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BeylikSelectionTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(array $attrs = []): User
    {
        return User::factory()->create($attrs);
    }

    // -------------------------------------------------------------------------
    // 1. Yeni register olan kullanicinin beylik'i null olarak doner
    // -------------------------------------------------------------------------

    public function test_register_response_includes_null_beylik(): void
    {
        $response = $this->postJson('/api/register', [
            'username' => 'testuser',
            'email'    => 'test@example.com',
            'password' => 'secret123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.beylik', null);
    }

    // -------------------------------------------------------------------------
    // 2. Login response'unda beylik null olarak doner (henuz secilmemisse)
    // -------------------------------------------------------------------------

    public function test_login_response_includes_null_beylik_when_not_selected(): void
    {
        $this->createUser([
            'username' => 'loginuser',
            'email'    => 'login@example.com',
            'beylik'   => null,
        ]);

        $response = $this->postJson('/api/login', [
            'username' => 'loginuser',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('user.beylik', null);
    }

    // -------------------------------------------------------------------------
    // 3. Kullanici beylik secer -> 200, beylik kayit edilir
    // -------------------------------------------------------------------------

    public function test_user_can_choose_beylik_once(): void
    {
        $user = $this->createUser(['beylik' => null]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/profile/beylik', ['beylik' => 'gokboru']);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('user.beylik', 'gokboru');

        $this->assertDatabaseHas('users', [
            'id'     => $user->id,
            'beylik' => 'gokboru',
        ]);
    }

    // -------------------------------------------------------------------------
    // 4. Ayni kullanici ikinci kez secim yapmaya calissir -> 409
    // -------------------------------------------------------------------------

    public function test_user_cannot_change_beylik_after_selection(): void
    {
        $user = $this->createUser(['beylik' => 'gokboru']);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/profile/beylik', ['beylik' => 'bozkurt']);

        $response->assertStatus(409)
            ->assertJsonPath('success', false);

        // DB'deki deger degismemeli
        $this->assertDatabaseHas('users', [
            'id'     => $user->id,
            'beylik' => 'gokboru',
        ]);
    }

    // -------------------------------------------------------------------------
    // 5. Beylik sectikten sonra login response'unda beylik doner
    // -------------------------------------------------------------------------

    public function test_login_response_includes_beylik_after_selection(): void
    {
        $this->createUser([
            'username' => 'bozuser',
            'email'    => 'boz@example.com',
            'beylik'   => 'bozkurt',
        ]);

        $response = $this->postJson('/api/login', [
            'username' => 'bozuser',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('user.beylik', 'bozkurt');
    }

    // -------------------------------------------------------------------------
    // 6. bozkurt secen kullanici icin de tek seferlik kilit dogru calisiyor
    // -------------------------------------------------------------------------

    public function test_bozkurt_selection_is_also_locked(): void
    {
        $user = $this->createUser(['beylik' => null]);
        Sanctum::actingAs($user);

        $this->postJson('/api/profile/beylik', ['beylik' => 'bozkurt'])->assertOk();

        $second = $this->postJson('/api/profile/beylik', ['beylik' => 'gokboru']);
        $second->assertStatus(409);

        $this->assertDatabaseHas('users', [
            'id'     => $user->id,
            'beylik' => 'bozkurt',
        ]);
    }

    // -------------------------------------------------------------------------
    // 7. GET /api/me response'unda beylik gorunuyor
    // -------------------------------------------------------------------------

    public function test_me_endpoint_includes_beylik(): void
    {
        $user = $this->createUser(['beylik' => 'gokboru']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/me');

        $response->assertOk()
            ->assertJsonPath('beylik', 'gokboru');
    }

    // -------------------------------------------------------------------------
    // 8. Gecersiz beylik degeri reddedilir
    // -------------------------------------------------------------------------

    public function test_invalid_beylik_value_is_rejected(): void
    {
        $user = $this->createUser(['beylik' => null]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/profile/beylik', ['beylik' => 'invalid']);

        $response->assertStatus(422);
    }

    // -------------------------------------------------------------------------
    // 9. beylik alani gonderilmezse validation hatasi
    // -------------------------------------------------------------------------

    public function test_missing_beylik_field_is_rejected(): void
    {
        $user = $this->createUser(['beylik' => null]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/profile/beylik', []);

        $response->assertStatus(422);
    }

    // -------------------------------------------------------------------------
    // 10. Auth olmadan beylik endpointi erisim reddedilir
    // -------------------------------------------------------------------------

    public function test_unauthenticated_user_cannot_choose_beylik(): void
    {
        $response = $this->postJson('/api/profile/beylik', ['beylik' => 'gokboru']);

        $response->assertStatus(401);
    }
}
