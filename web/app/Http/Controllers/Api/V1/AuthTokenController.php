<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthTokenController extends Controller
{
    public function store(LoginRequest $request): JsonResponse
    {
        $user = User::query()
            ->with(['roles', 'establishments'])
            ->where('rut', $request->string('rut'))
            ->where('status', 'active')
            ->first();

        if (! $user || ! Hash::check($request->string('password')->toString(), $user->password)) {
            throw ValidationException::withMessages([
                'rut' => ['Las credenciales no coinciden o la cuenta aún no está habilitada.'],
            ]);
        }

        $tokenName = $request->string('device_name')->trim()->toString() ?: 'api';

        return response()->json([
            'access_token' => $user->createToken($tokenName)->plainTextToken,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ]);
    }

    public function show(Request $request): UserResource
    {
        return new UserResource($request->user()->loadMissing(['roles', 'establishments']));
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(null, 204);
    }
}
