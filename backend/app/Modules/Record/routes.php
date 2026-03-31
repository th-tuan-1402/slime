<?php

declare(strict_types=1);

use App\Modules\Record\RecordController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(static function (): void {
    Route::get('/schemas/{schemaId}/records', [RecordController::class, 'index'])->whereNumber('schemaId');
    Route::get('/schemas/{schemaId}/records/export', [RecordController::class, 'export'])->whereNumber('schemaId');
    Route::post('/schemas/{schemaId}/records/import', [RecordController::class, 'import'])->whereNumber('schemaId');
});

