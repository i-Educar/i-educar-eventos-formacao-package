<?php

namespace iEducar\Packages\BuritiEventosFormacao\Providers;

use Illuminate\Support\ServiceProvider;

class EventosFormacaoServiceProvider extends ServiceProvider
{
    public const string PACKAGE = 'buriti/i-educar-eventos-formacao-package';

    public function register(): void
    {
        // Services são value objects — instanciar com `new` nos callers (não bind no container).
    }

    public function boot(): void
    {
    }
}
