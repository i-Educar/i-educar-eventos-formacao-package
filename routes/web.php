<?php

use iEducar\Packages\BuritiEventosFormacao\Http\Controllers\EventosController;
use Illuminate\Support\Facades\Route;

Route::prefix('eventos-formacao')->group(function (): void {
    Route::get('/', [EventosController::class, 'index']);
    Route::get('/eventos', [EventosController::class, 'eventos']);
    Route::post('/eventos', [EventosController::class, 'salvarEvento']);
    Route::get('/inscricoes', [EventosController::class, 'inscricoes']);
    Route::post('/inscricoes', [EventosController::class, 'salvarInscricao']);
    Route::get('/presenca', [EventosController::class, 'presenca']);
    Route::post('/presenca', [EventosController::class, 'salvarPresenca']);
    Route::get('/certificados', [EventosController::class, 'certificados']);
    Route::post('/certificados', [EventosController::class, 'emitirCertificado']);
});
