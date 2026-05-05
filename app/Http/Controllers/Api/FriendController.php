<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\Request;

class FriendController extends Controller
{
    // GET /api/friends — kabul edilmiş arkadaş listesi
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $friends = Friendship::where('status', 'accepted')
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('friend_id', $userId);
            })
            ->with(['sender:id,username', 'receiver:id,username'])
            ->get()
            ->map(function ($friendship) use ($userId) {
                $friend = $friendship->user_id === $userId
                    ? $friendship->receiver
                    : $friendship->sender;

                return [
                    'friendship_id' => $friendship->id,
                    'friend'        => $friend,
                ];
            });

        return response()->json(['success' => true, 'friends' => $friends]);
    }

    // GET /api/friends/requests — gelen bekleyen istekler
    public function requests(Request $request)
    {
        $pending = $request->user()
            ->receivedFriendRequests()
            ->where('status', 'pending')
            ->with('sender:id,username')
            ->get()
            ->map(fn($f) => [
                'friendship_id' => $f->id,
                'from'          => $f->sender,
                'sent_at'       => $f->created_at,
            ]);

        return response()->json(['success' => true, 'requests' => $pending]);
    }

    // POST /api/friends/request — arkadaş isteği gönder
    public function sendRequest(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
        ]);

        $me = $request->user();
        $target = User::where('username', $request->username)->first();

        if (!$target) {
            return response()->json(['success' => false, 'message' => 'Kullanıcı bulunamadı.'], 404);
        }

        if ($target->id === $me->id) {
            return response()->json(['success' => false, 'message' => 'Kendinize istek gönderemezsiniz.'], 422);
        }

        $exists = Friendship::where(function ($q) use ($me, $target) {
            $q->where('user_id', $me->id)->where('friend_id', $target->id);
        })->orWhere(function ($q) use ($me, $target) {
            $q->where('user_id', $target->id)->where('friend_id', $me->id);
        })->first();

        if ($exists) {
            $msg = match ($exists->status) {
                'accepted' => 'Zaten arkadaşsınız.',
                'pending'  => 'Zaten bekleyen bir istek var.',
                'blocked'  => 'Bu kullanıcıya istek gönderemezsiniz.',
            };
            return response()->json(['success' => false, 'message' => $msg], 422);
        }

        $friendship = Friendship::create([
            'user_id'   => $me->id,
            'friend_id' => $target->id,
            'status'    => 'pending',
        ]);

        return response()->json(['success' => true, 'friendship' => $friendship], 201);
    }

    // PUT /api/friends/{id}/accept — isteği kabul et
    public function accept(Request $request, int $id)
    {
        $friendship = Friendship::where('id', $id)
            ->where('friend_id', $request->user()->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $friendship->update(['status' => 'accepted']);

        return response()->json(['success' => true, 'friendship' => $friendship]);
    }

    // DELETE /api/friends/{id}/decline — isteği reddet (pending)
    public function decline(Request $request, int $id)
    {
        $friendship = Friendship::where('id', $id)
            ->where('friend_id', $request->user()->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $friendship->delete();

        return response()->json(['success' => true, 'message' => 'İstek reddedildi.']);
    }

    // DELETE /api/friends/{id} — arkadaşlıktan çıkar
    public function remove(Request $request, int $id)
    {
        $userId = $request->user()->id;

        $friendship = Friendship::where('id', $id)
            ->where('status', 'accepted')
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('friend_id', $userId);
            })
            ->firstOrFail();

        $friendship->delete();

        return response()->json(['success' => true, 'message' => 'Arkadaşlıktan çıkarıldı.']);
    }
}
