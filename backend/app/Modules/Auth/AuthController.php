<?php

declare(strict_types=1);

namespace App\Modules\Auth;

use App\Http\AbstractApiController;
use App\Modules\Auth\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AuthController extends AbstractApiController
{
    public function __construct(
        private readonly AuthEditor $authEditor,
    ) {
    }

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

    public function logout(Request $request): JsonResponse
    {
        /** @var AuthUser $user */
        $user = $request->user();

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

    public function me(Request $request): JsonResponse
    {
        /** @var AuthUser $user */
        $user = $request->user();

        return $this->respondSuccess([
            'user' => $this->authEditor->toUserPayload($user),
        ]);
    }

    public function refreshToken(Request $request): JsonResponse
    {
        /** @var AuthUser $user */
        $user = $request->user();

        $newToken = $this->authEditor->refreshToken($user);

        return $this->respondSuccess([
            'token' => $newToken,
        ]);
    }
}

