<?php

declare(strict_types=1);

use App\Modules\Field\FieldController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(static function (): void {
    Route::get('/fields/{fieldId}/selections', [FieldController::class, 'selections'])->whereNumber('fieldId');
    Route::put('/fields/{fieldId}/selections', [FieldController::class, 'updateSelections'])->whereNumber('fieldId');
    Route::get('/fields/{fieldId}/sequences', [FieldController::class, 'sequence'])->whereNumber('fieldId');
    Route::put('/fields/{fieldId}/sequences', [FieldController::class, 'updateSequence'])->whereNumber('fieldId');
    Route::get('/fields/{fieldId}/links/search', [FieldController::class, 'searchLinks'])->whereNumber('fieldId');

    Route::get('/schemas/{schemaId}/fields', [FieldController::class, 'index'])->whereNumber('schemaId');
    Route::post('/schemas/{schemaId}/fields', [FieldController::class, 'store'])->whereNumber('schemaId');
    Route::get('/schemas/{schemaId}/fields/{fieldId}', [FieldController::class, 'show'])->whereNumber('schemaId')->whereNumber('fieldId');
    Route::get('/schemas/{schemaId}/fields/{fieldId}/configs', [FieldController::class, 'configs'])->whereNumber('schemaId')->whereNumber('fieldId');
    Route::put('/schemas/{schemaId}/fields/{fieldId}', [FieldController::class, 'update'])->whereNumber('schemaId')->whereNumber('fieldId');
    Route::put('/schemas/{schemaId}/fields/{fieldId}/configs', [FieldController::class, 'updateConfigs'])->whereNumber('schemaId')->whereNumber('fieldId');
    Route::delete('/schemas/{schemaId}/fields/{fieldId}', [FieldController::class, 'destroy'])->whereNumber('schemaId')->whereNumber('fieldId');
    Route::put('/schemas/{schemaId}/fields/sort', [FieldController::class, 'sort'])->whereNumber('schemaId');
});

