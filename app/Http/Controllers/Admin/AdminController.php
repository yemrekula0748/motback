<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $query = User::withCount('characters')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(int $id)
    {
        $user = User::withCount('characters')->with('characters')->findOrFail($id);

        return view('admin.users.show', compact('user'));
    }

    public function ban(Request $request, int $id)
    {
        $request->validate([
            'ban_reason' => 'nullable|string|max:500',
        ]);

        $user = User::findOrFail($id);

        if ($user->is_admin) {
            return back()->with('error', 'Admin hesabı yasaklanamaz.');
        }

        $user->update([
            'is_banned'  => true,
            'ban_reason' => $request->ban_reason ?? 'Sebep belirtilmedi.',
        ]);

        $user->tokens()->delete();

        return back()->with('success', $user->username . ' yasaklandı.');
    }

    public function unban(int $id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_banned' => false, 'ban_reason' => null]);

        return back()->with('success', $user->username . ' yasağı kaldırıldı.');
    }
}
