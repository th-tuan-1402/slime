<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
 * API routes entrypoint.
 *
 * Modules register their own routes via ModuleServiceProvider.
 * This file exists to satisfy Laravel's routing bootstrap configuration.
 */

Route::middleware('api')->group(static function (): void {
    // Intentionally left blank.
});

