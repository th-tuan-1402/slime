<?php

declare(strict_types=1);

use App\Modules\Field\FieldController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(static function (): void {
    Route::get('/schemas/{schemaId}/fields', [FieldController::class, 'index'])->whereNumber('schemaId');
    Route::post('/schemas/{schemaId}/fields', [FieldController::class, 'store'])->whereNumber('schemaId');
    Route::get('/schemas/{schemaId}/fields/{fieldId}', [FieldController::class, 'show'])->whereNumber('schemaId')->whereNumber('fieldId');
    Route::put('/schemas/{schemaId}/fields/{fieldId}', [FieldController::class, 'update'])->whereNumber('schemaId')->whereNumber('fieldId');
    Route::delete('/schemas/{schemaId}/fields/{fieldId}', [FieldController::class, 'destroy'])->whereNumber('schemaId')->whereNumber('fieldId');
    Route::put('/schemas/{schemaId}/fields/sort', [FieldController::class, 'sort'])->whereNumber('schemaId');
});

