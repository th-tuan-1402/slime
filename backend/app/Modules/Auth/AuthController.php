<?php

declare(strict_types=1);

namespace App\Modules\Auth;

use App\Http\AbstractApiController;
use App\Modules\Auth\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Session and token authentication for API clients (Sanctum).
 */
final class AuthController extends AbstractApiController
{
    public function __construct(
        private readonly AuthEditor $authEditor,
    ) {
    }

    /**
     * Authenticate with login ID and password; returns user and bearer token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authEditor->login(
            loginId: $request->string('login_id')->toString(),
            password: $request->string('password')->toString(),
        );

        if ($result->success === false) {
            return $this->respondError($result->message, Response::HTTP_UNAUTHORIZED);
        }

        return $this->respondSuccess([
            'user' => $result->user,
            'token' => $result->token,
        ]);
    }

    /**
     * Revoke the current access token and clear the session when present.
     */
    public function logout(Request $request): JsonResponse
    {
        /** @var AuthUser $user */
        $user = $request->user('sanctum');

        $token = $user->currentAccessToken();
        if ($token !== null) {
            $token->delete();
        }

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return $this->respondNoContent();
    }

    /**
     * Return the authenticated user as a stable API payload.
     */
    public function me(Request $request): JsonResponse
    {
        /** @var AuthUser $user */
        $user = $request->user('sanctum');

        return $this->respondSuccess([
            'user' => $this->authEditor->toUserPayload($user),
        ]);
    }

    /**
     * Rotate the bearer token: issue a new token and invalidate the old one.
     */
    public function refreshToken(Request $request): JsonResponse
    {
        /** @var AuthUser $user */
        $user = $request->user('sanctum');

        $newToken = $this->authEditor->refreshToken($user);

        return $this->respondSuccess([
            'token' => $newToken,
        ]);
    }
}

