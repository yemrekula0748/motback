<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MeController extends Controller
{
    use RespondsWithApi;

    public function show(Request $request): JsonResponse
    {
        return $this->success($request->user()->toApiArray());
    }

    public function updateFaction(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'faction' => ['required', 'string', Rule::in(config('motonline.allowed_factions'))],
        ]);

        if ($user->faction && $user->faction !== $validated['faction']) {
            return $this->error('FACTION_ALREADY_LOCKED', 'Beylik secimi degistirilemez.', 409);
        }

        $user->forceFill([
            'faction' => $validated['faction'],
        ])->save();

        return $this->success($user->toApiArray());
    }
}
