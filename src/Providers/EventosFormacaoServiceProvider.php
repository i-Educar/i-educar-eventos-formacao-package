<?php

namespace iEducar\Packages\BuritiEventosFormacao\Providers;

use iEducar\Packages\BuritiEventosFormacao\Console\EventosDemoCommand;
use iEducar\Packages\BuritiEventosFormacao\Support\Identidade;
use iEducar\Packages\BuritiEventosFormacao\Support\MenuFluxo;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class EventosFormacaoServiceProvider extends ServiceProvider
{
    public const string PACKAGE = 'buriti/i-educar-eventos-formacao-package';

    public function register(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__, 2).'/config/eventos.php', 'eventos');
    }

    public function boot(): void
    {
        if (class_exists(\iEducar\Packages\BuritiLagunaAmbiente\Support\BuritiUi::class)) {
            \iEducar\Packages\BuritiLagunaAmbiente\Support\BuritiUi::registrar();
        }

        $root = dirname(__DIR__, 2);
        $this->loadViewsFrom($root.'/resources/views', 'eventos');

        if ($this->app->runningInConsole()) {
            if (\App\Support\Tenancy\PackageTenant::allows(self::PACKAGE)) {
                $this->loadMigrationsFrom($root.'/database/migrations');
            }
            $this->commands([EventosDemoCommand::class]);
        }

        Route::middleware([
            'web',
            'ieducar.navigation',
            'ieducar.footer',
            'ieducar.xssbypass',
            'ieducar.suspended',
            'auth',
            'ieducar.checkresetpassword',
        ])->group($root.'/routes/web.php');

        View::composer('eventos::*', function ($view): void {
            $view->with('identidade', Identidade::tokens());
            $view->with('menuGrupos', MenuFluxo::grupos([
                'painel' => true,
                'eventos' => true,
                'inscricoes' => true,
                'presenca' => true,
                'frequencia' => true,
                'certificados' => true,
            ]));
        });
    }
}
