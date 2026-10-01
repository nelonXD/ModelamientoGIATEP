<?php

use App\Http\Controllers\Admin\RegistrationRequestController as AdminRegistrationRequestController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PendingModuleController;
use App\Http\Controllers\RegistrationRequestController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
    Route::get('/solicitar-acceso', [RegistrationRequestController::class, 'create'])->name('register');
    Route::post('/solicitar-acceso', [RegistrationRequestController::class, 'store'])->middleware('throttle:5,1')->name('register.store');
    Route::get('/solicitud-enviada', [RegistrationRequestController::class, 'success'])->name('register.success');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/inicio', DashboardController::class)->name('dashboard');
    Route::post('/cerrar-sesion', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/modulos/{module}', PendingModuleController::class)->name('modules.show');

    Route::middleware('can:review-registration-requests')->prefix('administracion')->name('admin.')->group(function (): void {
        Route::get('/solicitudes', [AdminRegistrationRequestController::class, 'index'])->name('registration-requests.index');
        Route::patch('/solicitudes/{registrationRequest}', [AdminRegistrationRequestController::class, 'update'])->name('registration-requests.update');
    });
});
