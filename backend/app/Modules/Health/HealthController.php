<?php

declare(strict_types=1);

namespace App\Modules\Health;

use App\Http\AbstractApiController;
use Illuminate\Http\JsonResponse;

/**
 * Health check endpoint for monitoring and load-balancer probes.
 */
class HealthController extends AbstractApiController
{
    /**
     * Return the application health status.
     *
     * @return JsonResponse HTTP 200 with status and timestamp.
     */
    public function index(): JsonResponse
    {
        return $this->respondSuccess([
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
