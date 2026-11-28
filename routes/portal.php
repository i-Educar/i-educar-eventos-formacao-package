<?php

use iEducar\Packages\BuritiEventosFormacao\Http\Controllers\PortalEventosController;
use Illuminate\Support\Facades\Route;

Route::prefix('eventos-formacao/portal')->group(function (): void {
    Route::get('/', [PortalEventosController::class, 'inicio'])->name('eventos.portal.inicio');
    Route::get('/evento/{token}', [PortalEventosController::class, 'evento'])->name('eventos.portal.evento');
    Route::post('/evento/{token}', [PortalEventosController::class, 'inscrever']);
    Route::get('/comprovante/{id}', [PortalEventosController::class, 'comprovante'])->whereNumber('id')->name('eventos.portal.comprovante');
    Route::get('/presenca/{token}', [PortalEventosController::class, 'presenca'])->name('eventos.portal.presenca');
    Route::post('/presenca/{token}', [PortalEventosController::class, 'salvarPresenca']);
    Route::get('/certificado/{token}', [PortalEventosController::class, 'certificado'])->name('eventos.portal.certificado');
    Route::post('/certificado/{token}', [PortalEventosController::class, 'emitirCertificadoPublico']);
});
