<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Models\Realm;
use Illuminate\Http\JsonResponse;

class RealmController extends Controller
{
    use RespondsWithApi;

    public function index(): JsonResponse
    {
        $realms = Realm::query()
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('weight')
            ->get()
            ->map(fn (Realm $realm) => $realm->toApiArray())
            ->values()
            ->all();

        return $this->success($realms);
    }
}
