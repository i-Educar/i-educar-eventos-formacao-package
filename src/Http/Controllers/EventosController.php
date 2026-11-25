<?php

namespace iEducar\Packages\BuritiEventosFormacao\Http\Controllers;

use iEducar\Packages\BuritiEventosFormacao\Services\CertificadoPresenca;
use iEducar\Packages\BuritiEventosFormacao\Services\EventoInscricao;
use iEducar\Packages\BuritiEventosFormacao\Support\PocRegistro;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class EventosController extends Controller
{
    public function index(): View
    {
        $eventos = PocRegistro::listar('evento');
        $inscricoes = PocRegistro::listar('inscricao');
        $presencas = PocRegistro::listar('presenca');
        $certs = PocRegistro::listar('certificado');

        $vagas = 0;
        $inscritos = count($inscricoes);
        foreach ($eventos as $e) {
            $vagas += (int) ($e['payload']['vagas'] ?? 0);
        }

        return view('eventos::painel', [
            'kpis' => [
                ['rotulo' => 'Eventos', 'valor' => count($eventos), 'icone' => 'fa-calendar', 'tom' => 'var(--cor)'],
                ['rotulo' => 'Vagas', 'valor' => $vagas, 'icone' => 'fa-users', 'tom' => 'var(--destaque)'],
                ['rotulo' => 'Inscrições', 'valor' => $inscritos, 'icone' => 'fa-ticket', 'tom' => '#0e7490'],
                ['rotulo' => 'Presenças', 'valor' => count($presencas), 'icone' => 'fa-check-square-o', 'tom' => 'var(--ok)'],
                ['rotulo' => 'Certificados', 'valor' => count($certs), 'icone' => 'fa-certificate', 'tom' => 'var(--ambar)'],
            ],
            'atalhos' => [
                ['rotulo' => 'Novo evento', 'url' => '/eventos-formacao/eventos', 'icone' => 'fa-plus', 'meta' => 'Cadastro'],
                ['rotulo' => 'Inscrições', 'url' => '/eventos-formacao/inscricoes', 'icone' => 'fa-ticket', 'meta' => 'Vagas e fila'],
                ['rotulo' => 'Presença', 'url' => '/eventos-formacao/presenca', 'icone' => 'fa-check', 'meta' => 'Frequência'],
                ['rotulo' => 'Certificados', 'url' => '/eventos-formacao/certificados', 'icone' => 'fa-certificate', 'meta' => 'Emissão'],
            ],
            'destaques' => [
                [
                    'titulo' => count($eventos) ? 'Agenda ativa' : 'Comece cadastrando um evento',
                    'meta' => $inscritos.' inscrição(ões) · '.count($certs).' certificado(s)',
                    'icone' => 'fa-calendar',
                    'tom' => 'var(--cor)',
                    'corpo' => 'Período, local, carga horária, vagas e liberação de certificado.',
                ],
            ],
            'analise' => array_map(static function (array $e): array {
                $p = $e['payload'];

                return [
                    'titulo' => $e['titulo'],
                    'meta' => ($p['local'] ?? '—').' · '.($p['inicio'] ?? '—'),
                    'corpo' => (int) ($p['vagas'] ?? 0).' vagas · CH '.($p['carga_horaria'] ?? '—'),
                    'badge' => (string) ($p['status'] ?? 'rascunho'),
                    'badge_tom' => ($p['status'] ?? '') === 'aberto' ? 'ok' : 'warn',
                    'url' => '/eventos-formacao/eventos',
                ];
            }, array_slice($eventos, 0, 6)),
            'analiseTitulo' => 'Últimos eventos',
            'fluxoTitulo' => 'Fluxo do módulo',
            'resumo' => 'Cadastre o evento → abra inscrições → registre presença → emita certificados.',
        ]);
    }

    public function eventos(): View
    {
        return view('eventos::eventos', [
            'registros' => PocRegistro::listar('evento'),
        ]);
    }

    public function salvarEvento(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'titulo' => 'required|string|max:180',
            'local' => 'nullable|string|max:180',
            'inicio' => 'nullable|date',
            'fim' => 'nullable|date',
            'inscricao_inicio' => 'nullable|date',
            'carga_horaria' => 'nullable|numeric|min:0',
            'carga_ead' => 'nullable|numeric|min:0',
            'vagas' => 'nullable|integer|min:0',
            'status' => 'nullable|in:rascunho,aberto,encerrado',
            'publico' => 'nullable|in:servidor,externo,ambos',
            'certificado' => 'nullable|boolean',
        ]);

        $titulo = (string) $dados['titulo'];
        $payload = [
            'local' => $dados['local'] ?? null,
            'inicio' => $dados['inicio'] ?? null,
            'fim' => $dados['fim'] ?? null,
            'inscricao_inicio' => $dados['inscricao_inicio'] ?? null,
            'carga_horaria' => $dados['carga_horaria'] ?? null,
            'carga_ead' => $dados['carga_ead'] ?? null,
            'vagas' => (int) ($dados['vagas'] ?? 0),
            'status' => $dados['status'] ?? 'rascunho',
            'publico' => $dados['publico'] ?? 'ambos',
            'certificado' => (bool) ($dados['certificado'] ?? true),
        ];

        PocRegistro::salvar('evento', $titulo, $payload['local'], $payload);

        return redirect('/eventos-formacao/eventos')->with('ok', 'Evento cadastrado.');
    }

    public function inscricoes(): View
    {
        return view('eventos::inscricoes', [
            'eventos' => PocRegistro::listar('evento'),
            'registros' => PocRegistro::listar('inscricao'),
        ]);
    }

    public function salvarInscricao(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'evento_id' => 'required|integer',
            'nome' => 'required|string|max:180',
            'cpf' => 'nullable|string|max:14',
            'tipo' => 'nullable|in:servidor,externo',
        ]);

        $evento = PocRegistro::buscar((int) $dados['evento_id']);
        if ($evento === null || $evento['tipo'] !== 'evento') {
            return back()->with('erro', 'Evento não encontrado.');
        }

        $vagas = (int) ($evento['payload']['vagas'] ?? 0);
        $ja = collect(PocRegistro::listar('inscricao'))
            ->filter(static fn (array $i): bool => (int) ($i['payload']['evento_id'] ?? 0) === (int) $evento['id'])
            ->count();

        try {
            $ctrl = new EventoInscricao($vagas > 0 ? $vagas : PHP_INT_MAX, $ja);
            $ctrl->inscrever();
        } catch (\RuntimeException $e) {
            return back()->with('erro', $e->getMessage());
        }

        PocRegistro::salvar('inscricao', (string) $dados['nome'], $evento['titulo'], [
            'evento_id' => (int) $evento['id'],
            'evento' => $evento['titulo'],
            'cpf' => $dados['cpf'] ?? null,
            'tipo' => $dados['tipo'] ?? 'servidor',
            'status' => 'confirmada',
        ]);

        return redirect('/eventos-formacao/inscricoes')->with('ok', 'Inscrição registrada.');
    }

    public function presenca(): View
    {
        return view('eventos::presenca', [
            'eventos' => PocRegistro::listar('evento'),
            'inscricoes' => PocRegistro::listar('inscricao'),
            'registros' => PocRegistro::listar('presenca'),
        ]);
    }

    public function salvarPresenca(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'inscricao_id' => 'required|integer',
            'percentual' => 'required|numeric|min:0|max:100',
            'situacao' => 'nullable|in:presente,ausente,parcial',
        ]);

        $insc = PocRegistro::buscar((int) $dados['inscricao_id']);
        if ($insc === null || $insc['tipo'] !== 'inscricao') {
            return back()->with('erro', 'Inscrição não encontrada.');
        }

        $pct = (float) $dados['percentual'];
        PocRegistro::salvar('presenca', $insc['titulo'], $insc['payload']['evento'] ?? null, [
            'inscricao_id' => (int) $insc['id'],
            'evento_id' => (int) ($insc['payload']['evento_id'] ?? 0),
            'evento' => $insc['payload']['evento'] ?? null,
            'percentual' => $pct,
            'situacao' => $dados['situacao'] ?? ($pct >= 75 ? 'presente' : 'parcial'),
        ]);

        return redirect('/eventos-formacao/presenca')->with('ok', 'Presença lançada.');
    }

    public function certificados(): View
    {
        $min = (float) config('eventos.frequencia_minima_certificado', 75);

        return view('eventos::certificados', [
            'minimo' => $min,
            'presencas' => PocRegistro::listar('presenca'),
            'registros' => PocRegistro::listar('certificado'),
        ]);
    }

    public function emitirCertificado(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'presenca_id' => 'required|integer',
        ]);
        $pres = PocRegistro::buscar((int) $dados['presenca_id']);
        if ($pres === null || $pres['tipo'] !== 'presenca') {
            return back()->with('erro', 'Presença não encontrada.');
        }

        $pct = (float) ($pres['payload']['percentual'] ?? 0);
        $min = (float) config('eventos.frequencia_minima_certificado', 75);
        if (! CertificadoPresenca::elegivel($pct, $min)) {
            return back()->with('erro', "Frequência {$pct}% abaixo do mínimo {$min}%.");
        }

        $eventoId = (int) ($pres['payload']['evento_id'] ?? 0);
        $pessoaId = (int) ($pres['payload']['inscricao_id'] ?? 0);
        $codigo = CertificadoPresenca::codigo($eventoId, $pessoaId);

        PocRegistro::salvar('certificado', $pres['titulo'], $codigo, [
            'presenca_id' => (int) $pres['id'],
            'evento_id' => $eventoId,
            'evento' => $pres['payload']['evento'] ?? null,
            'percentual' => $pct,
            'codigo' => $codigo,
            'emitido_em' => now()->toDateTimeString(),
        ]);

        return redirect('/eventos-formacao/certificados')->with('ok', 'Certificado '.$codigo.' emitido.');
    }
}
