<?php

namespace iEducar\Packages\BuritiEventosFormacao\Console;

use iEducar\Packages\BuritiEventosFormacao\Services\EventosDemoService;
use Illuminate\Console\Command;

class EventosDemoCommand extends Command
{
    protected $signature = 'eventos:demo {--force : Recria eventos/inscrições demo}';

    protected $description = 'PoC — povoa eventos, inscrições, presença e certificados';

    public function handle(EventosDemoService $demo): int
    {
        $this->info('Eventos e formação · carga demo');
        $r = $demo->executar((bool) $this->option('force'));
        if ($r['pulado']) {
            $this->warn('  '.$r['mensagem']);

            return self::SUCCESS;
        }
        if (str_contains($r['mensagem'], 'ausente')) {
            $this->error('  '.$r['mensagem']);

            return self::FAILURE;
        }
        $this->line('  '.$r['mensagem']);

        return self::SUCCESS;
    }
}
