<?php

declare(strict_types=1);

use App\Modules\Schema\SchemaController;
use App\Modules\Schema\SchemaGroupController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(static function (): void {
    // Schema
    Route::get('/schemas', [SchemaController::class, 'index']);
    Route::post('/schemas', [SchemaController::class, 'store']);
    Route::post('/schemas/{id}/copy', [SchemaController::class, 'copy'])->whereNumber('id');
    Route::get('/schemas/{id}', [SchemaController::class, 'show'])->whereNumber('id');
    Route::put('/schemas/{id}', [SchemaController::class, 'update'])->whereNumber('id');
    Route::put('/schemas/sort', [SchemaController::class, 'sort']);

    // Schema group
    Route::get('/schema-groups', [SchemaGroupController::class, 'index']);
    Route::post('/schema-groups', [SchemaGroupController::class, 'store']);
    Route::get('/schema-groups/{id}', [SchemaGroupController::class, 'show'])->whereNumber('id');
    Route::put('/schema-groups/{id}', [SchemaGroupController::class, 'update'])->whereNumber('id');
    Route::delete('/schema-groups/{id}', [SchemaGroupController::class, 'destroy'])->whereNumber('id');
    Route::put('/schema-groups/sort', [SchemaGroupController::class, 'sort']);
});

