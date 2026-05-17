<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Models\GameSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServerSessionController extends Controller
{
    use RespondsWithApi;

    public function consume(Request $request): JsonResponse
    {
        if (! $this->isServerAuthorized($request)) {
            return $this->error('SERVER_UNAUTHORIZED', 'Server yetkilendirmesi basarisiz.', 401);
        }

        $validated = $request->validate([
            'session_token' => ['required', 'string', 'size:64'],
            'realm_slug' => ['nullable', 'string', 'max:64'],
        ]);

        $session = GameSession::query()
            ->with(['user', 'character', 'realm'])
            ->where('token_hash', hash('sha256', $validated['session_token']))
            ->when($validated['realm_slug'] ?? null, fn ($query, $slug) => $query->whereHas('realm', fn ($realmQuery) => $realmQuery->where('slug', $slug)))
            ->first();

        if (! $session || ! $session->character || ! $session->user || ! $session->realm) {
            return $this->error('SESSION_NOT_FOUND', 'Oyun session bulunamadi.', 404);
        }

        if ($session->consumed_at !== null) {
            return $this->error('SESSION_ALREADY_CONSUMED', 'Oyun session daha once kullanilmis.', 409);
        }

        if ($session->expires_at->isPast()) {
            return $this->error('SESSION_EXPIRED', 'Oyun session suresi dolmus.', 410);
        }

        $session->forceFill([
            'consumed_at' => now(),
        ])->save();

        return $this->success([
            'user' => $session->user->toApiArray(),
            'character' => $session->character->toApiArray(),
            'realm' => $session->realm->toApiArray(),
        ]);
    }

    private function isServerAuthorized(Request $request): bool
    {
        $configuredKey = (string) config('motonline.server_shared_key');
        $headerKey = (string) $request->header('X-Server-Key', '');

        return $configuredKey !== '' && hash_equals($configuredKey, $headerKey);
    }
}
