<?php

use App\Http\Controllers\Web\Admin\EstablishmentController;
use App\Http\Controllers\Web\Admin\RegistrationRequestController as AdminRegistrationRequestController;
use App\Http\Controllers\Web\Admin\RoleController;
use App\Http\Controllers\Web\Admin\SettingController;
use App\Http\Controllers\Web\Admin\UserController;
use App\Http\Controllers\Web\AiAnalysisController;
use App\Http\Controllers\Web\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Web\Auth\FirstInstitutionalAccessController;
use App\Http\Controllers\Web\CaseController;
use App\Http\Controllers\Web\CauseFactorController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DemoWorkspaceController;
use App\Http\Controllers\Web\InvestigationController;
use App\Http\Controllers\Web\MeasureController;
use App\Http\Controllers\Web\ObservationController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\RegistrationRequestController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\ReviewController;
use App\Http\Controllers\Web\StatisticController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login/verificar-rut', [AuthenticatedSessionController::class, 'checkRut'])->middleware('throttle:10,1')->name('login.check-rut');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
    Route::post('/login/primer-ingreso', [FirstInstitutionalAccessController::class, 'store'])->middleware('throttle:5,1')->name('login.first-access');
    Route::get('/solicitar-acceso', [RegistrationRequestController::class, 'create'])->name('register');
    Route::post('/solicitar-acceso', [RegistrationRequestController::class, 'store'])->middleware('throttle:5,1')->name('register.store');
    Route::get('/solicitud-enviada', [RegistrationRequestController::class, 'success'])->name('register.success');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/inicio', DashboardController::class)->name('dashboard');
    Route::post('/cerrar-sesion', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/prototipo/{module}/{id?}', [DemoWorkspaceController::class, 'show'])->name('demo.show');
    Route::post('/prototipo/{module}', [DemoWorkspaceController::class, 'action'])->name('demo.action');
    Route::post('/prototipo-restablecer', [DemoWorkspaceController::class, 'reset'])->name('demo.reset');
    Route::post('/prototipo-cambiar-rol', [DemoWorkspaceController::class, 'switchRole'])->name('demo.switch-role');

    Route::prefix('modulos')->name('modules.')->group(function (): void {
        Route::get('/casos', [CaseController::class, 'index'])->name('cases.index');
        Route::get('/casos/crear', [CaseController::class, 'create'])->name('cases.create');
        Route::post('/casos', [CaseController::class, 'store'])->name('cases.store');
        Route::get('/casos/{case:folio}/editar', [CaseController::class, 'edit'])->name('cases.edit');
        Route::patch('/casos/{case:folio}', [CaseController::class, 'update'])->name('cases.update');
        Route::get('/casos/{case:folio}/evidencias', [CaseController::class, 'evidence'])->name('cases.evidence.index');
        Route::post('/casos/{case:folio}/evidencias', [CaseController::class, 'storeEvidence'])->name('cases.evidence.store');
        Route::get('/casos/{case:folio}/evidencias/{evidence}', [CaseController::class, 'downloadEvidence'])->name('cases.evidence.download');
        Route::get('/casos/{case:folio}/evidencias/{evidence}/previsualizar', [CaseController::class, 'previewEvidence'])->name('cases.evidence.preview');
        Route::delete('/casos/{case:folio}/evidencias/{evidence}', [CaseController::class, 'deleteEvidence'])->name('cases.evidence.delete');
        Route::get('/investigaciones', InvestigationController::class)->name('investigations.index');
        Route::post('/investigaciones/{case:folio}/iniciar', [InvestigationController::class, 'start'])->name('investigations.start');
        Route::post('/investigaciones/relato-final', [AiAnalysisController::class, 'storeFinalStory'])->name('investigations.story.store');
        Route::post('/investigaciones/relato-final/generar', [AiAnalysisController::class, 'generateFinalStory'])->middleware('throttle:5,1')->name('investigations.story.generate');
        Route::post('/investigaciones/asistente-local', [AiAnalysisController::class, 'askCaseAgent'])->middleware('throttle:10,1')->name('investigations.ai.agent');
        Route::post('/investigaciones/analizar', [AiAnalysisController::class, 'analyze'])->middleware('throttle:10,1')->name('investigations.ai.analyze');
        Route::get('/investigaciones/analizar/modelos', [AiAnalysisController::class, 'models'])->name('investigations.ai.models');
        Route::post('/investigaciones/analizar/hechos', [AiAnalysisController::class, 'analyzeFacts'])->middleware('throttle:10,1')->name('investigations.ai.facts');
        Route::post('/investigaciones/analizar/arbol', [AiAnalysisController::class, 'analyzeTree'])->middleware('throttle:10,1')->name('investigations.ai.tree');
        Route::post('/investigaciones/analizar/factores-lote', [AiAnalysisController::class, 'analyzeFactorBatch'])->middleware('throttle:20,1')->name('investigations.ai.factor-batch');
        Route::post('/investigaciones/analizar/medidas', [AiAnalysisController::class, 'analyzeMeasures'])->middleware('throttle:10,1')->name('investigations.ai.measures');
        Route::post('/investigaciones/propuesta', [AiAnalysisController::class, 'store'])->name('investigations.ai.store');
        Route::post('/investigaciones/entrevistas', [AiAnalysisController::class, 'storeInterview'])->name('investigations.interviews.store');
        Route::get('/factores-causales', CauseFactorController::class)->name('cause-factors.index');
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
