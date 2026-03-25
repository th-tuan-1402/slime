<?php

declare(strict_types=1);

use App\Modules\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(static function (): void {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(static function (): void {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/token', [AuthController::class, 'refreshToken']);
    });
});

