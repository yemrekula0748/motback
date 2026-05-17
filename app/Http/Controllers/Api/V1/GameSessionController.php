<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Models\Character;
use App\Models\GameSession;
use App\Models\Realm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameSessionController extends Controller
{
    use RespondsWithApi;

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user->faction) {
            return $this->error('FACTION_REQUIRED', 'Oyuna girmeden once beylik secmelisiniz.', 409);
        }

        $validated = $request->validate([
            'character_id' => ['required', 'integer'],
            'realm_slug' => ['nullable', 'string', 'max:64'],
        ]);

        $character = Character::query()
            ->where('user_id', $user->id)
            ->whereKey($validated['character_id'])
            ->first();

        if (! $character) {
            return $this->error('CHARACTER_NOT_FOUND', 'Karakter bulunamadi.', 404);
        }

        $realm = Realm::query()
            ->where('is_active', true)
            ->when(
                $validated['realm_slug'] ?? null,
                fn ($query, $slug) => $query->where('slug', $slug),
                fn ($query) => $query->where('faction', $user->faction)->where('is_default', true)
            )
            ->first();

        if (! $realm) {
            return $this->error('REALM_NOT_AVAILABLE', 'Bu karakter icin aktif realm bulunamadi.', 409);
        }

        $rawToken = bin2hex(random_bytes(32));
        $session = GameSession::query()->create([
            'user_id' => $user->id,
            'character_id' => $character->id,
            'realm_id' => $realm->id,
            'token_hash' => hash('sha256', $rawToken),
            'client_ip' => $request->ip(),
            'metadata' => [
                'user_agent' => (string) $request->userAgent(),
            ],
            'expires_at' => now()->addSeconds((int) config('motonline.game_session_ttl_seconds')),
        ]);

        return $this->success([
            'session_token' => $rawToken,
            'expires_in' => (int) config('motonline.game_session_ttl_seconds'),
            'character_id' => $character->id,
            'faction' => $user->faction,
            'realm' => $realm->toApiArray(),
            'session_id' => $session->id,
        ], 201);
    }
}
