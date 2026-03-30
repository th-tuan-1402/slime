<?php

declare(strict_types=1);

use App\Modules\Schema\SchemaController;
use App\Modules\Schema\SchemaGroupController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(static function (): void {
    // Schema
    Route::get('/schemas', [SchemaController::class, 'index']);
    Route::post('/schemas', [SchemaController::class, 'store']);
<<<<<<< HEAD
=======
    Route::post('/schemas/{id}/copy', [SchemaController::class, 'copy'])->whereNumber('id');
>>>>>>> a52ccdb (feat(schema): スキーマコピーAPIを追加)
    Route::get('/schemas/{id}/delete-confirm', [SchemaController::class, 'deleteConfirm'])->whereNumber('id');
    Route::delete('/schemas/batch', [SchemaController::class, 'batchDestroy']);
    Route::get('/schemas/{id}', [SchemaController::class, 'show'])->whereNumber('id');
    Route::put('/schemas/{id}', [SchemaController::class, 'update'])->whereNumber('id');
    Route::delete('/schemas/{id}', [SchemaController::class, 'destroy'])->whereNumber('id');
    Route::put('/schemas/sort', [SchemaController::class, 'sort']);

    // Schema group
    Route::get('/schema-groups', [SchemaGroupController::class, 'index']);
    Route::post('/schema-groups', [SchemaGroupController::class, 'store']);
    Route::get('/schema-groups/{id}', [SchemaGroupController::class, 'show'])->whereNumber('id');
    Route::put('/schema-groups/{id}', [SchemaGroupController::class, 'update'])->whereNumber('id');
    Route::delete('/schema-groups/{id}', [SchemaGroupController::class, 'destroy'])->whereNumber('id');
    Route::put('/schema-groups/sort', [SchemaGroupController::class, 'sort']);
});

