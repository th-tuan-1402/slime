<?php

declare(strict_types=1);

use App\Modules\Health\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('/health', [HealthController::class, 'index'])
    ->withoutMiddleware('api');
