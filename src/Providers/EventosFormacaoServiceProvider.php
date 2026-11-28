<?php

namespace iEducar\Packages\BuritiEventosFormacao\Providers;

use App\Support\PublicModuleRegistry;
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

        PublicModuleRegistry::register('eventos-portal', [
            'package' => self::PACKAGE,
            'titulo' => (string) config('eventos.public.titulo', Identidade::NOME),
            'descricao' => 'Portal público — inscrição, presença e certificados de eventos da rede.',
            'url' => static function (): ?string {
                if (! class_exists(\iEducar\Packages\BuritiLagunaAmbiente\Support\ModuloConfiguracao::class)) {
                    return (string) config('eventos.public.url', '/eventos-formacao/portal');
                }
                $cfg = \iEducar\Packages\BuritiLagunaAmbiente\Support\ModuloConfiguracao::class;
                if (! $cfg::ativo('eventos') || ! $cfg::portalExterno('eventos')) {
                    return null;
                }

                return (string) config('eventos.public.url', '/eventos-formacao/portal');
            },
            'icone' => Identidade::ICONE,
            'css_class' => 'public-module eventos',
            'aparecer_na_home' => static function (): bool {
                if (! class_exists(\iEducar\Packages\BuritiLagunaAmbiente\Support\ModuloConfiguracao::class)) {
                    return (bool) config('eventos.public.aparecer_na_home', true);
                }

                return \iEducar\Packages\BuritiLagunaAmbiente\Support\ModuloConfiguracao::aparecerNaHome('eventos');
            },
            'ordem' => (int) config('eventos.public.ordem', 58),
            'cor' => Identidade::COR,
            'periodo' => 'Sob evento aberto',
            'regra' => 'Público externo · sem conta i-Educar',
        ]);

        if ($this->app->runningInConsole()) {
            if (\App\Support\Tenancy\PackageTenant::allows(self::PACKAGE)) {
                $this->loadMigrationsFrom($root.'/database/migrations');
            }
            $this->commands([EventosDemoCommand::class]);
        }

        Route::middleware(['web'])->group($root.'/routes/portal.php');

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
            $portal = request()->is('eventos-formacao/portal*') || ! auth()->check();

            if (! $portal
                && class_exists(\iEducar\Packages\BuritiRhEducacional\Support\RhAgregacao::class)
                && \iEducar\Packages\BuritiRhEducacional\Support\RhAgregacao::ativa()) {
                $view->with('identidade', \iEducar\Packages\BuritiRhEducacional\Support\RhAgregacao::identidade());
                $view->with('menuGrupos', \iEducar\Packages\BuritiRhEducacional\Support\RhAgregacao::menuGrupos());
                $view->with('homeModulo', url(\iEducar\Packages\BuritiRhEducacional\Support\RhAgregacao::homeModulo()));
                $view->with('nomeModulo', 'RH educacional');

                return;
            }
            $view->with('identidade', Identidade::tokens());
            $view->with('menuGrupos', MenuFluxo::grupos([
                'painel' => true,
                'eventos' => true,
                'inscricoes' => true,
                'presenca' => true,
                'frequencia' => true,
                'certificados' => true,
                'portal' => true,
            ]));
        });
    }
}
