<?php

use App\Http\Controllers\Api\AuditoriaController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MultaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Rotas públicas
Route::post('/login', [AuthController::class, 'login'])->name('login');

// Rotas protegidas por autenticação (sem middleware por enquanto para testes)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/me', [AuthController::class, 'me'])->name('me');

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

// Auditoria
Route::prefix('auditoria')->name('auditoria.')->group(function () {
    Route::get('/', [AuditoriaController::class, 'index'])->name('index');
    Route::get('/{id}', [AuditoriaController::class, 'show'])->name('show');
});
