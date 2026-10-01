<?php

use App\Http\Controllers\Api\V1\AuthTokenController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::post('/auth/login', [AuthTokenController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('auth.login');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/me', [AuthTokenController::class, 'show'])->name('me');
        Route::delete('/auth/token', [AuthTokenController::class, 'destroy'])->name('auth.logout');
    });
});
