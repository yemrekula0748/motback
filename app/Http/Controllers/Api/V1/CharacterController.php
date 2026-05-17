<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Models\Character;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CharacterController extends Controller
{
    use RespondsWithApi;

    public function index(Request $request): JsonResponse
    {
        $characters = $request->user()
            ->characters()
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (Character $character) => $character->toApiArray())
            ->values()
            ->all();

        return $this->success($characters);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->characters()->count() >= (int) config('motonline.max_characters_per_user')) {
            return $this->error('CHARACTER_LIMIT_REACHED', 'Maksimum karakter limitine ulastiniz.', 422);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:24', 'regex:/^[A-Za-z0-9_]+$/', 'unique:characters,name'],
            'class' => ['required', 'string', Rule::in(config('motonline.allowed_classes'))],
        ]);

        $character = Character::query()->create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'class' => $validated['class'],
            ...$this->starterStatsForClass($validated['class']),
        ]);

        return $this->success($character->toApiArray(), 201);
    }

    public function show(Request $request, Character $character): JsonResponse
    {
        if ($character->user_id !== $request->user()->id) {
            return $this->error('CHARACTER_NOT_FOUND', 'Karakter bulunamadi.', 404);
        }

        return $this->success($character->toApiArray());
    }

    public function destroy(Request $request, Character $character): JsonResponse
    {
        if ($character->user_id !== $request->user()->id) {
            return $this->error('CHARACTER_NOT_FOUND', 'Karakter bulunamadi.', 404);
        }

        $characterId = $character->id;
        $character->delete();

        return $this->success([
            'deleted' => true,
            'character_id' => $characterId,
        ]);
    }

    private function starterStatsForClass(string $class): array
    {
        return match ($class) {
            'savasco' => [
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
                'current_map' => config('motonline.default_map_path'),
                'pos_x' => 0,
                'pos_y' => 0,
                'pos_z' => 0,
            ],
            'okcu' => [
                'level' => 1,
                'experience' => 0,
                'strength' => 8,
                'agility' => 12,
                'intelligence' => 7,
                'endurance' => 9,
                'base_strength' => 8,
                'base_agility' => 12,
                'base_intelligence' => 7,
                'base_endurance' => 9,
                'unspent_stat_points' => 0,
                'max_health' => 280,
                'current_health' => 280,
                'max_mana' => 100,
                'current_mana' => 100,
                'gold' => 0,
                'attack_power' => 26,
                'defense' => 11,
                'current_map' => config('motonline.default_map_path'),
                'pos_x' => 0,
                'pos_y' => 0,
                'pos_z' => 0,
            ],
            default => [
                'level' => 1,
                'experience' => 0,
                'strength' => 6,
                'agility' => 8,
                'intelligence' => 12,
                'endurance' => 8,
                'base_strength' => 6,
                'base_agility' => 8,
                'base_intelligence' => 12,
                'base_endurance' => 8,
                'unspent_stat_points' => 0,
                'max_health' => 250,
                'current_health' => 250,
                'max_mana' => 130,
                'current_mana' => 130,
                'gold' => 0,
                'attack_power' => 22,
                'defense' => 9,
                'current_map' => config('motonline.default_map_path'),
                'pos_x' => 0,
                'pos_y' => 0,
                'pos_z' => 0,
            ],
        };
    }
}
