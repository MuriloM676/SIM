<?php

use App\Http\Controllers\Api\AgenteController;
use App\Http\Controllers\Api\AuditoriaController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\InfracaoController;
use App\Http\Controllers\Api\MultaController;
use App\Http\Controllers\Api\MunicipioController;
use App\Http\Controllers\Api\RecursoController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\VeiculoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Rotas públicas
Route::post('/login', [AuthController::class, 'login'])->name('login');

// Rotas protegidas
Route::middleware(['auth.api'])->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/me', [AuthController::class, 'me'])->name('me');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/relatorio', [DashboardController::class, 'relatorio'])->name('relatorio');

    // Multas
    Route::prefix('multas')->name('multas.')->group(function () {
        Route::get('/', [MultaController::class, 'index'])->name('index');
        Route::post('/', [MultaController::class, 'store'])->name('store');
        Route::get('/statistics', [MultaController::class, 'statistics'])->name('statistics');
        Route::get('/{id}', [MultaController::class, 'show'])->name('show');
        Route::put('/{id}', [MultaController::class, 'update'])->name('update');
        Route::patch('/{id}/status', [MultaController::class, 'updateStatus'])->name('update-status');
        Route::post('/{id}/cancel', [MultaController::class, 'cancel'])->name('cancel');
        Route::post('/{id}/send-detran', [MultaController::class, 'sendToDetran'])->name('send-detran');
    });

    // Recursos
    Route::prefix('recursos')->name('recursos.')->group(function () {
        Route::get('/', [RecursoController::class, 'index'])->name('index');
        Route::post('/', [RecursoController::class, 'store'])->name('store');
        Route::get('/{id}', [RecursoController::class, 'show'])->name('show');
        Route::post('/{id}/julgar', [RecursoController::class, 'julgar'])
            ->middleware('role:administrador,gestor')
            ->name('julgar');
    });

    // Veículos
    Route::prefix('veiculos')->name('veiculos.')->group(function () {
        Route::get('/', [VeiculoController::class, 'index'])->name('index');
        Route::post('/', [VeiculoController::class, 'store'])->name('store');
        Route::get('/{id}', [VeiculoController::class, 'show'])->name('show');
        Route::put('/{id}', [VeiculoController::class, 'update'])->name('update');
    });

    // Agentes
    Route::prefix('agentes')->name('agentes.')->group(function () {
        Route::get('/', [AgenteController::class, 'index'])->name('index');
        Route::post('/', [AgenteController::class, 'store'])
            ->middleware('role:administrador,gestor')
            ->name('store');
        Route::put('/{id}', [AgenteController::class, 'update'])
            ->middleware('role:administrador,gestor')
            ->name('update');
    });

    // Infrações (somente leitura)
    Route::prefix('infracoes')->name('infracoes.')->group(function () {
        Route::get('/', [InfracaoController::class, 'index'])->name('index');
        Route::get('/{id}', [InfracaoController::class, 'show'])->name('show');
    });

    // Usuários (apenas admin/gestor)
    Route::prefix('usuarios')->middleware('role:administrador,gestor')->name('usuarios.')->group(function () {
        Route::get('/', [UsuarioController::class, 'index'])->name('index');
        Route::post('/', [UsuarioController::class, 'store'])->name('store');
        Route::put('/{id}', [UsuarioController::class, 'update'])->name('update');
        Route::post('/{id}/reset-password', [UsuarioController::class, 'resetPassword'])->name('reset-password');
    });

    // Municípios (apenas admin)
    Route::prefix('municipios')->middleware('role:administrador')->name('municipios.')->group(function () {
        Route::get('/', [MunicipioController::class, 'index'])->name('index');
        Route::post('/', [MunicipioController::class, 'store'])->name('store');
        Route::put('/{id}', [MunicipioController::class, 'update'])->name('update');
    });

    // Auditoria
    Route::prefix('auditoria')->name('auditoria.')->group(function () {
        Route::get('/', [AuditoriaController::class, 'index'])->name('index');
        Route::get('/export', [AuditoriaController::class, 'export'])->name('export');
    });
});
