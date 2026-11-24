<?php

namespace iEducar\Packages\BuritiEventosFormacao\Providers;

use iEducar\Packages\BuritiEventosFormacao\Services\EventoInscricao;
use iEducar\Packages\BuritiEventosFormacao\Services\CertificadoPresenca;
use Illuminate\Support\ServiceProvider;

class EventosFormacaoServiceProvider extends ServiceProvider
{
    public const string PACKAGE = 'buriti/i-educar-eventos-formacao-package';

    public function register(): void
    {
        $this->app->singleton(EventoInscricao::class);
        $this->app->singleton(CertificadoPresenca::class);
    }

    public function boot(): void
    {
    }
}
