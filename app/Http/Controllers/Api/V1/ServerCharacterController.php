<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Models\Character;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServerCharacterController extends Controller
{
    use RespondsWithApi;

    public function updateProgress(Request $request, Character $character): JsonResponse
    {
        if (! $this->isServerAuthorized($request)) {
            return $this->error('SERVER_UNAUTHORIZED', 'Server yetkilendirmesi basarisiz.', 401);
        }

        $validated = $request->validate([
            'level' => ['nullable', 'integer', 'min:1', 'max:999'],
            'experience' => ['nullable', 'integer', 'min:0'],
            'strength' => ['nullable', 'integer', 'min:1'],
            'agility' => ['nullable', 'integer', 'min:1'],
            'intelligence' => ['nullable', 'integer', 'min:1'],
            'endurance' => ['nullable', 'integer', 'min:1'],
            'base_strength' => ['nullable', 'integer', 'min:1'],
            'base_agility' => ['nullable', 'integer', 'min:1'],
            'base_intelligence' => ['nullable', 'integer', 'min:1'],
            'base_endurance' => ['nullable', 'integer', 'min:1'],
            'unspent_stat_points' => ['nullable', 'integer', 'min:0'],
            'max_health' => ['nullable', 'integer', 'min:1'],
            'current_health' => ['nullable', 'integer', 'min:0'],
            'max_mana' => ['nullable', 'integer', 'min:0'],
            'current_mana' => ['nullable', 'integer', 'min:0'],
            'gold' => ['nullable', 'integer', 'min:0'],
            'attack_power' => ['nullable', 'integer', 'min:1'],
            'defense' => ['nullable', 'integer', 'min:0'],
            'current_map' => ['nullable', 'string', 'max:255'],
            'pos_x' => ['nullable', 'numeric'],
            'pos_y' => ['nullable', 'numeric'],
            'pos_z' => ['nullable', 'numeric'],
        ]);

        $character->fill($validated);
        $character->save();

        return $this->success([
            'saved' => true,
            'character' => $character->fresh()->toApiArray(),
        ]);
    }

    private function isServerAuthorized(Request $request): bool
    {
        $configuredKey = (string) config('motonline.server_shared_key');
        $headerKey = (string) $request->header('X-Server-Key', '');

        return $configuredKey !== '' && hash_equals($configuredKey, $headerKey);
    }
}
