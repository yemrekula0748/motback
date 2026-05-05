<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CharacterQuest;
use App\Models\Quest;
use Illuminate\Http\Request;

class QuestController extends Controller
{
    // GET /api/characters/{id}/quests
    // Karakterin aktif görevleri + seviyesine uygun alınabilir görevler
    public function index(Request $request, int $id)
    {
        $character = $request->user()->characters()->findOrFail($id);

        // Aktif ve tamamlanmış görevler
        $characterQuests = $character->quests()
            ->with('quest')
            ->get();

        // Karakterin aldığı görev id'leri
        $takenQuestIds = $characterQuests->pluck('quest_id')->toArray();

        // Alınabilir görevler (seviyeye uygun, aktif, henüz alınmamış)
        $available = Quest::where('is_active', true)
            ->where('min_level', '<=', $character->level)
            ->whereNotIn('id', $takenQuestIds)
            ->get();

        return response()->json([
            'success'   => true,
            'active'    => $characterQuests->where('status', 'active')->values(),
            'completed' => $characterQuests->where('status', 'completed')->values(),
            'available' => $available,
        ]);
    }

    // POST /api/characters/{id}/quests/{questId}/start
    // Görevi başlat
    public function start(Request $request, int $id, int $questId)
    {
        $character = $request->user()->characters()->findOrFail($id);
        $quest = Quest::where('is_active', true)->findOrFail($questId);

        if ($character->level < $quest->min_level) {
            return response()->json([
                'success' => false,
                'message' => "Bu görev için en az level {$quest->min_level} gerekli.",
            ], 422);
        }

        $existing = CharacterQuest::where('character_id', $character->id)
            ->where('quest_id', $quest->id)
            ->first();

        if ($existing) {
            $msg = $existing->status === 'completed'
                ? 'Bu görevi zaten tamamladınız.'
                : 'Bu görev zaten aktif.';
            return response()->json(['success' => false, 'message' => $msg], 422);
        }

        $characterQuest = CharacterQuest::create([
            'character_id' => $character->id,
            'quest_id'     => $quest->id,
            'kill_count'   => 0,
            'status'       => 'active',
        ]);

        return response()->json([
            'success'        => true,
            'character_quest' => $characterQuest->load('quest'),
        ], 201);
    }

    // PUT /api/characters/{id}/quests/{questId}/progress
    // Öldürme sayısını güncelle
    public function progress(Request $request, int $id, int $questId)
    {
        $character = $request->user()->characters()->findOrFail($id);

        $request->validate([
            'kill_count' => 'required|integer|min:0',
        ]);

        $characterQuest = CharacterQuest::where('character_id', $character->id)
            ->where('quest_id', $questId)
            ->where('status', 'active')
            ->firstOrFail();

        // Sadece artışa izin ver, required_kills'i aşmasın
        $characterQuest->kill_count = min(
            max($request->integer('kill_count'), $characterQuest->kill_count),
            $characterQuest->quest->required_kills
        );
        $characterQuest->save();

        return response()->json([
            'success'        => true,
            'character_quest' => $characterQuest->load('quest'),
        ]);
    }

    // PUT /api/characters/{id}/quests/{questId}/complete
    // Görevi tamamla, exp ödülünü ver
    public function complete(Request $request, int $id, int $questId)
    {
        $character = $request->user()->characters()->findOrFail($id);

        $characterQuest = CharacterQuest::where('character_id', $character->id)
            ->where('quest_id', $questId)
            ->where('status', 'active')
            ->with('quest')
            ->firstOrFail();

        $quest = $characterQuest->quest;

        if ($characterQuest->kill_count < $quest->required_kills) {
            return response()->json([
                'success' => false,
                'message' => "Görev henüz tamamlanmadı. ({$characterQuest->kill_count}/{$quest->required_kills})",
            ], 422);
        }

        // Görevi tamamla
        $characterQuest->update([
            'status'       => 'completed',
            'completed_at' => now(),
        ]);

        // EXP ödülünü ver, level-up işle
        $character->experience += $quest->reward_exp;

        while ($character->experience >= $this->calculateExpToNextLevel($character->level)) {
            $character->experience -= $this->calculateExpToNextLevel($character->level);
            $character->applyLevelUp();
        }

        $character->last_played_at = now();
        $character->save();

        return response()->json([
            'success'        => true,
            'reward_exp'     => $quest->reward_exp,
            'character'      => $character,
            'character_quest' => $characterQuest,
        ]);
    }

    private function calculateExpToNextLevel(int $level): int
    {
        return (int)(1000 * pow(1.5, $level - 1));
    }
}
