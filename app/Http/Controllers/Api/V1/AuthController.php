<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    use RespondsWithApi;

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:24', 'regex:/^[A-Za-z0-9_]+$/', 'unique:users,username'],
            'email' => ['required', 'email:rfc', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(10)->letters()->mixedCase()->numbers()->symbols()],
        ]);

        $user = User::query()->create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'faction' => null,
            'is_admin' => false,
        ]);

        $token = $user->createToken('client')->plainTextToken;

        return $this->success([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->toApiArray(),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:24'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $user = User::query()->where('username', $validated['username'])->first();
        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return $this->error('AUTH_INVALID_CREDENTIALS', 'Kullanici adi veya sifre hatali.', 401);
        }

        $user->tokens()->where('name', 'client')->delete();
        $token = $user->createToken('client')->plainTextToken;

        return $this->success([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->toApiArray(),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();
        if ($token) {
            $token->delete();
        }

        return $this->success([
            'logged_out' => true,
        ]);
    }
}
