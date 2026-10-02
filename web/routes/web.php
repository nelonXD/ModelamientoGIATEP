<?php

use App\Http\Controllers\Admin\Establishments\EstablishmentController;
use App\Http\Controllers\Admin\RegistrationRequestController as AdminRegistrationRequestController;
use App\Http\Controllers\Admin\Roles\RoleController;
use App\Http\Controllers\Admin\Settings\SettingController;
use App\Http\Controllers\Admin\Users\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Modules\Cases\CaseController;
use App\Http\Controllers\Modules\Investigations\InvestigationController;
use App\Http\Controllers\Modules\Measures\MeasureController;
use App\Http\Controllers\Modules\Observations\ObservationController;
use App\Http\Controllers\Modules\Profile\ProfileController;
use App\Http\Controllers\Modules\Reports\ReportController;
use App\Http\Controllers\Modules\Reviews\ReviewController;
use App\Http\Controllers\Modules\Statistics\StatisticController;
use App\Http\Controllers\RegistrationRequestController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login/verificar-rut', [AuthenticatedSessionController::class, 'checkRut'])->middleware('throttle:10,1')->name('login.check-rut');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
    Route::get('/solicitar-acceso', [RegistrationRequestController::class, 'create'])->name('register');
    Route::post('/solicitar-acceso', [RegistrationRequestController::class, 'store'])->middleware('throttle:5,1')->name('register.store');
    Route::get('/solicitud-enviada', [RegistrationRequestController::class, 'success'])->name('register.success');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/inicio', DashboardController::class)->name('dashboard');
    Route::post('/cerrar-sesion', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::prefix('modulos')->name('modules.')->group(function (): void {
        Route::get('/casos', [CaseController::class, 'index'])->name('cases.index');
        Route::get('/casos/crear', [CaseController::class, 'create'])->name('cases.create');
        Route::get('/investigaciones', InvestigationController::class)->name('investigations.index');
        Route::get('/revisiones', ReviewController::class)->name('reviews.index');
        Route::get('/medidas', MeasureController::class)->name('measures.index');
        Route::get('/observaciones', ObservationController::class)->name('observations.index');
        Route::get('/estadisticas', StatisticController::class)->name('statistics.index');
        Route::get('/reportes', ReportController::class)->name('reports.index');
        Route::get('/perfil', ProfileController::class)->name('profile.index');
    });

    Route::prefix('administracion')->name('admin.')->group(function (): void {
        Route::middleware('can:review-registration-requests')->group(function (): void {
            Route::get('/solicitudes', [AdminRegistrationRequestController::class, 'index'])->name('registration-requests.index');
            Route::patch('/solicitudes/{registrationRequest}', [AdminRegistrationRequestController::class, 'update'])->name('registration-requests.update');
        });

        Route::get('/usuarios', UserController::class)->name('users.index');
        Route::get('/roles', RoleController::class)->name('roles.index');
        Route::get('/establecimientos', EstablishmentController::class)->name('establishments.index');
        Route::get('/parametros', SettingController::class)->name('settings.index');
    });
});
