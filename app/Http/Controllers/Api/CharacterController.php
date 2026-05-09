<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCharacterRequest;
use App\Http\Resources\CharacterResource;
use App\Models\Character;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CharacterController extends Controller
{
    // -------------------------------------------------------------------------
    // GET /characters
    // -------------------------------------------------------------------------

    public function index(Request $request): JsonResponse
    {
        $characters = $request->user()
            ->characters()
            ->orderByDesc('last_played_at')
            ->get();

        return response()->json([
            'success'    => true,
            'characters' => CharacterResource::collection($characters),
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /characters
    // -------------------------------------------------------------------------

    public function store(Request $request): JsonResponse
    {
        if ($request->user()->characters()->count() >= 3) {
            return response()->json([
                'success' => false,
                'message' => 'En fazla 3 karakter olusturabilirsiniz.',
            ], 422);
        }

        $request->validate([
            'name'  => 'required|alpha_num|min:3|max:30|unique:characters,name',
            'class' => 'required|in:savasco,okcu,saman',
        ]);

        // getStartingStats now includes base_* and unspent_stat_points = 0
        $stats = Character::getStartingStats($request->class);

        $character = $request->user()->characters()->create([
            'name'  => $request->name,
            'class' => $request->class,
            ...$stats,
        ]);

        return response()->json([
            'success'   => true,
            'character' => new CharacterResource($character),
        ], 201);
    }

    // -------------------------------------------------------------------------
    // GET /characters/{id}
    // -------------------------------------------------------------------------

    public function show(Request $request, int $id): JsonResponse
    {
        $character = $request->user()->characters()->findOrFail($id);

        return response()->json([
            'success'   => true,
            'character' => new CharacterResource($character),
        ]);
    }

    // -------------------------------------------------------------------------
    // PUT /characters/{id}/save  (legacy endpoint, kept for backward compat)
    // Server-side experience->level processing retained for legacy callers.
    // New Unreal integration should prefer PATCH /characters/{id}.
    // -------------------------------------------------------------------------

    public function save(Request $request, int $id): JsonResponse
    {
        $character = $request->user()->characters()->findOrFail($id);

        $request->validate([
            'experience'          => 'sometimes|integer|min:0',
            'strength'            => 'sometimes|integer|min:0',
            'agility'             => 'sometimes|integer|min:0',
            'intelligence'        => 'sometimes|integer|min:0',
            'endurance'           => 'sometimes|integer|min:0',
            'base_strength'       => 'sometimes|integer|min:0',
            'base_agility'        => 'sometimes|integer|min:0',
            'base_intelligence'   => 'sometimes|integer|min:0',
            'base_endurance'      => 'sometimes|integer|min:0',
            'vitality'            => 'sometimes|integer|min:0',
            'dexterity'           => 'sometimes|integer|min:0',
            'unspent_stat_points' => 'sometimes|integer|min:0',
            'attack_power'        => 'sometimes|integer|min:0',
            'defense'             => 'sometimes|integer|min:0',
            'current_health'      => 'sometimes|integer|min:0',
            'current_mana'        => 'sometimes|integer|min:0',
            'gold'                => 'sometimes|integer|min:0',
            'silver'              => 'sometimes|integer|min:0',
            'current_map'         => 'sometimes|string|max:100',
            'pos_x'               => 'sometimes|numeric',
            'pos_y'               => 'sometimes|numeric',
            'pos_z'               => 'sometimes|numeric',
        ]);

        $data = $request->only([
            'strength', 'agility', 'intelligence', 'endurance',
            'base_strength', 'base_agility', 'base_intelligence', 'base_endurance',
            'vitality', 'dexterity', 'unspent_stat_points',
            'attack_power', 'defense',
            'current_health', 'current_mana',
            'gold', 'silver',
            'current_map', 'pos_x', 'pos_y', 'pos_z',
        ]);

        if (isset($data['current_health'])) {
            $data['current_health'] = min($data['current_health'], $character->max_health);
        }
        if (isset($data['current_mana'])) {
            $data['current_mana'] = min($data['current_mana'], $character->max_mana);
        }

        // Legacy server-side level processing
        if ($request->has('experience')) {
            $character->experience = $request->integer('experience');

            while ($character->experience >= $this->calculateExpToNextLevel($character->level)) {
                $character->experience -= $this->calculateExpToNextLevel($character->level);
                $character->applyLevelUp();
            }
        }

        $character->fill($data);
        $character->last_played_at = now();
        $character->save();

        return response()->json([
            'success'   => true,
            'character' => new CharacterResource($character),
        ]);
    }

    // -------------------------------------------------------------------------
    // PATCH /characters/{id}  (primary Unreal save endpoint)
    // Pure persistence: Unreal is server-authoritative; accept and store as-is.
    // -------------------------------------------------------------------------

    public function updateProgress(UpdateCharacterRequest $request, int $id): JsonResponse
    {
        $character = $request->user()->characters()->findOrFail($id);

        $character->fill($request->only([
            'level',
            'experience',
            'strength',
            'agility',
            'intelligence',
            'endurance',
            'base_strength',
            'base_agility',
            'base_intelligence',
            'base_endurance',
            'vitality',
            'dexterity',
            'unspent_stat_points',
            'max_health',
            'current_health',
            'max_mana',
            'current_mana',
            'attack_power',
            'defense',
            'gold',
            'silver',
            'current_map',
            'pos_x',
            'pos_y',
            'pos_z',
        ]));

        $character->last_played_at = now();
        $character->save();

        return response()->json([
            'success'   => true,
            'character' => new CharacterResource($character),
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /characters/{id}/respec  (optional admin/tooling endpoint)
    // Resets current stats to base_* and refunds spent points as unspent.
    // Unreal can also handle respec entirely and push results via PATCH.
    // -------------------------------------------------------------------------

    public function respec(Request $request, int $id): JsonResponse
    {
        $character = $request->user()->characters()->findOrFail($id);

        if (
            $character->base_strength === 0
            && $character->base_agility === 0
            && $character->base_intelligence === 0
            && $character->base_endurance === 0
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Temel statlar henuz belirlenmemis, respec yapilamaz.',
            ], 422);
        }

        $spent = max(0, $character->strength     - $character->base_strength)
               + max(0, $character->agility      - $character->base_agility)
               + max(0, $character->intelligence - $character->base_intelligence)
               + max(0, $character->endurance    - $character->base_endurance);

        $character->strength     = $character->base_strength;
        $character->agility      = $character->base_agility;
        $character->intelligence = $character->base_intelligence;
        $character->endurance    = $character->base_endurance;
        $character->unspent_stat_points += $spent;

        $character->last_played_at = now();
        $character->save();

        return response()->json([
            'success'   => true,
            'character' => new CharacterResource($character),
        ]);
    }

    // -------------------------------------------------------------------------
    // PUT /characters/{id}/exp
    // -------------------------------------------------------------------------

    public function updateExp(Request $request, int $id): JsonResponse
    {
        $character = $request->user()->characters()->findOrFail($id);

        $request->validate([
            'experience' => 'required|integer|min:0',
        ]);

        $character->experience = $request->integer('experience');

        while ($character->experience >= $this->calculateExpToNextLevel($character->level)) {
            $character->experience -= $this->calculateExpToNextLevel($character->level);
            $character->applyLevelUp();
        }

        $character->last_played_at = now();
        $character->save();

        return response()->json([
            'success'   => true,
            'character' => new CharacterResource($character),
        ]);
    }

    // -------------------------------------------------------------------------
    // PUT /characters/{id}/level
    // -------------------------------------------------------------------------

    public function updateLevel(Request $request, int $id): JsonResponse
    {
        $character = $request->user()->characters()->findOrFail($id);

        $request->validate([
            'level' => 'required|integer|min:1|max:100',
        ]);

        $targetLevel = $request->integer('level');

        if ($targetLevel <= $character->level) {
            return response()->json([
                'success' => false,
                'message' => 'Yeni level mevcut levelden buyuk olmalidir.',
            ], 422);
        }

        while ($character->level < $targetLevel) {
            $character->applyLevelUp();
        }

        $character->experience     = 0;
        $character->last_played_at = now();
        $character->save();

        return response()->json([
            'success'   => true,
            'character' => new CharacterResource($character),
        ]);
    }

    // -------------------------------------------------------------------------
    // DELETE /characters/{id}
    // -------------------------------------------------------------------------

    public function destroy(Request $request, int $id): JsonResponse
    {
        $character = $request->user()->characters()->findOrFail($id);
        $character->delete();

        return response()->json(['success' => true, 'message' => 'Karakter silindi.']);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function calculateExpToNextLevel(int $level): int
    {
        return (int) (1000 * pow(1.5, $level - 1));
    }
}
