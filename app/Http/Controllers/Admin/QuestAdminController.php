<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quest;
use Illuminate\Http\Request;

class QuestAdminController extends Controller
{
    public function index()
    {
        $quests = Quest::latest()->paginate(20);
        return view('admin.quests.index', compact('quests'));
    }

    public function create()
    {
        return view('admin.quests.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'          => 'required|string|max:100',
            'description'    => 'nullable|string|max:1000',
            'target_enemy'   => 'required|string|max:100',
            'required_kills' => 'required|integer|min:1',
            'min_level'      => 'required|integer|min:1|max:100',
            'reward_exp'     => 'required|integer|min:1',
        ]);

        Quest::create([
            'title'          => $request->title,
            'description'    => $request->description,
            'target_enemy'   => $request->target_enemy,
            'required_kills' => $request->required_kills,
            'min_level'      => $request->min_level,
            'reward_exp'     => $request->reward_exp,
            'is_active'      => $request->has('is_active'),
        ]);

        return redirect()->route('admin.quests.index')->with('success', 'Görev oluşturuldu.');
    }

    public function edit(int $id)
    {
        $quest = Quest::findOrFail($id);
        return view('admin.quests.edit', compact('quest'));
    }

    public function update(Request $request, int $id)
    {
        $quest = Quest::findOrFail($id);

        $request->validate([
            'title'          => 'required|string|max:100',
            'description'    => 'nullable|string|max:1000',
            'target_enemy'   => 'required|string|max:100',
            'required_kills' => 'required|integer|min:1',
            'min_level'      => 'required|integer|min:1|max:100',
            'reward_exp'     => 'required|integer|min:1',
        ]);

        $quest->update([
            'title'          => $request->title,
            'description'    => $request->description,
            'target_enemy'   => $request->target_enemy,
            'required_kills' => $request->required_kills,
            'min_level'      => $request->min_level,
            'reward_exp'     => $request->reward_exp,
            'is_active'      => $request->has('is_active'),
        ]);

        return redirect()->route('admin.quests.index')->with('success', 'Görev güncellendi.');
    }

    public function destroy(int $id)
    {
        Quest::findOrFail($id)->delete();
        return redirect()->route('admin.quests.index')->with('success', 'Görev silindi.');
    }
}
