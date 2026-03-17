<?php

declare(strict_types=1);

namespace App\Http;

use App\Enums\RoleKey;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Base API controller for all feature controllers.
 *
 * Provides standardized JSON response formatting, tenant context resolution,
 * and role-based authorization. Every API controller in the application
 * should extend this class.
 */
abstract class AbstractApiController extends Controller
{
    use AuthorizesRequests;
    use ValidatesRequests;

    // ── Response Helpers ─────────────────────────────────────────────

    /**
     * Return a standard success response.
     *
     * @param mixed  $data    Response payload (will be JSON-encoded).
     * @param string $message Human-readable status message.
     * @return JsonResponse HTTP 200 with `{success, message, data}` envelope.
     */
    protected function respondSuccess(mixed $data = null, string $message = 'OK'): JsonResponse
    {
        return new JsonResponse(
            data: [
                'success' => true,
                'message' => $message,
                'data' => $data,
            ],
            status: Response::HTTP_OK,
        );
    }

    /**
     * Return a resource-created response.
     *
     * @param mixed  $data    The newly created resource.
     * @param string $message Human-readable status message.
     * @return JsonResponse HTTP 201 with `{success, message, data}` envelope.
     */
    protected function respondCreated(mixed $data = null, string $message = 'Created'): JsonResponse
    {
        return new JsonResponse(
            data: [
                'success' => true,
                'message' => $message,
                'data' => $data,
            ],
            status: Response::HTTP_CREATED,
        );
    }

    /**
     * Return an empty no-content response.
     *
     * @return JsonResponse HTTP 204 with no body.
     */
    protected function respondNoContent(): JsonResponse
    {
        return new JsonResponse(data: null, status: Response::HTTP_NO_CONTENT);
    }

    /**
     * Return an error response.
     *
     * @param string     $message Human-readable error description.
     * @param int        $status  HTTP status code (default 400).
     * @param array<string, array<string>>|null $errors Optional field-level error details.
     * @return JsonResponse HTTP 4xx/5xx with `{success, message, errors?}` envelope.
     */
    protected function respondError(
        string $message,
        int $status = Response::HTTP_BAD_REQUEST,
        ?array $errors = null,
    ): JsonResponse {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return new JsonResponse(data: $payload, status: $status);
    }

    /**
     * Return a paginated list response with metadata.
     *
     * @param LengthAwarePaginator $paginator The paginator instance from Eloquent.
     * @return JsonResponse HTTP 200 with `{success, message, data, meta}` envelope.
     */
    protected function respondPaginated(LengthAwarePaginator $paginator): JsonResponse
    {
        return new JsonResponse(
            data: [
                'success' => true,
                'message' => 'OK',
                'data' => $paginator->items(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                ],
            ],
            status: Response::HTTP_OK,
        );
    }

    // ── Tenant Context ───────────────────────────────────────────────

    /**
     * Get the tenant ID for the current request.
     *
     * Reads the `tenant_id` attribute from the authenticated user.
     * Auth middleware is expected to have already authenticated the request.
     *
     * @return string The tenant database identifier.
     * @throws \RuntimeException If the tenant ID is not available on the user.
     * @throws AuthenticationException If the user is not authenticated.
     */
    protected function tenantId(): string
    {
        $user = $this->currentUser();

        /** @var string|null $tenantId */
        $tenantId = $user->tenant_id ?? null; // @phpstan-ignore-line

        if ($tenantId === null || $tenantId === '') {
            throw new \RuntimeException('Tenant ID is not available on the authenticated user.');
        }

        return $tenantId;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return Authenticatable The authenticated user model.
     * @throws AuthenticationException If no user is authenticated.
     */
    protected function currentUser(): Authenticatable
    {
        $user = request()->user();

        if ($user === null) {
            throw new AuthenticationException('Unauthenticated.');
        }

        return $user;
    }

    /**
     * Get the primary key of the currently authenticated user.
     *
     * @return int The user's ID.
     * @throws AuthenticationException If no user is authenticated.
     */
    protected function currentUserId(): int
    {
        return (int) $this->currentUser()->getAuthIdentifier();
    }

    // ── Authorization ────────────────────────────────────────────────

    /**
     * Verify that the authenticated user holds one of the given roles.
     *
     * Usage: `$this->authorizeRole(RoleKey::Admin, RoleKey::Manager);`
     *
     * @param RoleKey ...$roles One or more acceptable roles.
     * @return void
     * @throws AccessDeniedHttpException If the user's role does not match any of the given roles.
     * @throws AuthenticationException   If no user is authenticated.
     */
    protected function authorizeRole(RoleKey ...$roles): void
    {
        $user = $this->currentUser();

        $allowed = array_map(
            static fn(RoleKey $role): string => $role->value,
            $roles,
        );

        /** @var string $userRole */
        $userRole = $user->role ?? ''; // @phpstan-ignore-line

        if (!in_array($userRole, $allowed, true)) {
            throw new AccessDeniedHttpException('Insufficient permissions.');
        }
    }
}
