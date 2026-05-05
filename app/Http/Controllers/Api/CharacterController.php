<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Character;
use Illuminate\Http\Request;

class CharacterController extends Controller
{
    public function index(Request $request)
    {
        $characters = $request->user()
            ->characters()
            ->orderByDesc('last_played_at')
            ->get();

        return response()->json(['success' => true, 'characters' => $characters]);
    }

    public function store(Request $request)
    {
        if ($request->user()->characters()->count() >= 3) {
            return response()->json([
                'success' => false,
                'message' => 'En fazla 3 karakter oluşturabilirsiniz.',
            ], 422);
        }

        $request->validate([
            'name'  => 'required|alpha_num|min:3|max:30|unique:characters,name',
            'class' => 'required|in:savasco,okcu,saman',
        ]);

        $stats = Character::getStartingStats($request->class);

        $character = $request->user()->characters()->create([
            'name'  => $request->name,
            'class' => $request->class,
            ...$stats,
        ]);

        return response()->json(['success' => true, 'character' => $character], 201);
    }

    public function show(Request $request, int $id)
    {
        $character = $request->user()->characters()->findOrFail($id);

        return response()->json(['success' => true, 'character' => $character]);
    }

    public function save(Request $request, int $id)
    {
        $character = $request->user()->characters()->findOrFail($id);

        $request->validate([
            'current_health' => 'sometimes|integer|min:0',
            'current_mana'   => 'sometimes|integer|min:0',
            'experience'     => 'sometimes|integer|min:0',
            'current_map'    => 'sometimes|string|max:100',
            'pos_x'          => 'sometimes|numeric',
            'pos_y'          => 'sometimes|numeric',
            'pos_z'          => 'sometimes|numeric',
            'gold'           => 'sometimes|integer|min:0',
            'silver'         => 'sometimes|integer|min:0',
        ]);

        $data = $request->only([
            'current_health', 'current_mana', 'experience',
            'current_map', 'pos_x', 'pos_y', 'pos_z', 'gold', 'silver',
        ]);

        // Server-side validation: cap health/mana
        if (isset($data['current_health'])) {
            $data['current_health'] = min($data['current_health'], $character->max_health);
        }
        if (isset($data['current_mana'])) {
            $data['current_mana'] = min($data['current_mana'], $character->max_mana);
        }

        // Process level ups
        if (isset($data['experience'])) {
            $character->experience = $data['experience'];
            unset($data['experience']);

            while ($character->experience >= $this->calculateExpToNextLevel($character->level)) {
                $character->experience -= $this->calculateExpToNextLevel($character->level);
                $character->applyLevelUp();
            }
        }

        $character->fill($data);
        $character->last_played_at = now();
        $character->save();

        return response()->json(['success' => true, 'character' => $character]);
    }

    public function updateExp(Request $request, int $id)
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

        return response()->json(['success' => true, 'character' => $character]);
    }

    public function updateLevel(Request $request, int $id)
    {
        $character = $request->user()->characters()->findOrFail($id);

        $request->validate([
            'level' => 'required|integer|min:1|max:100',
        ]);

        $targetLevel = $request->integer('level');

        if ($targetLevel <= $character->level) {
            return response()->json([
                'success' => false,
                'message' => 'Yeni level mevcut levelden büyük olmalıdır.',
            ], 422);
        }

        while ($character->level < $targetLevel) {
            $character->applyLevelUp();
        }

        $character->experience = 0;
        $character->last_played_at = now();
        $character->save();

        return response()->json(['success' => true, 'character' => $character]);
    }

    public function destroy(Request $request, int $id)
    {
        $character = $request->user()->characters()->findOrFail($id);
        $character->delete();

        return response()->json(['success' => true, 'message' => 'Karakter silindi.']);
    }

    private function calculateExpToNextLevel(int $level): int
    {
        return (int)(1000 * pow(1.5, $level - 1));
    }
}
