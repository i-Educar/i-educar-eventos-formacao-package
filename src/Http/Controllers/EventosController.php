<?php

namespace iEducar\Packages\BuritiEventosFormacao\Http\Controllers;

use iEducar\Packages\BuritiEventosFormacao\Services\CertificadoPresenca;
use iEducar\Packages\BuritiEventosFormacao\Services\EventoInscricao;
use iEducar\Packages\BuritiEventosFormacao\Support\EventoPublico;
use iEducar\Packages\BuritiEventosFormacao\Support\PocRegistro;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

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
                ['rotulo' => 'Novo evento', 'url' => '/eventos-formacao/eventos', 'icone' => 'fa-plus', 'meta' => 'Cadastro e portal'],
                ['rotulo' => 'Inscrições', 'url' => '/eventos-formacao/inscricoes', 'icone' => 'fa-ticket', 'meta' => 'Internas e externas'],
                ['rotulo' => 'Presença', 'url' => '/eventos-formacao/presenca', 'icone' => 'fa-check', 'meta' => 'Check-in / frequência'],
                ['rotulo' => 'Certificados', 'url' => '/eventos-formacao/certificados', 'icone' => 'fa-certificate', 'meta' => 'Emissão'],
                ['rotulo' => 'Portal público', 'url' => '/eventos-formacao/portal', 'icone' => 'fa-external-link', 'meta' => 'Sem login'],
            ],
            'destaques' => [
                [
                    'titulo' => count($eventos) ? 'Agenda ativa' : 'Comece cadastrando um evento',
                    'meta' => $inscritos.' inscrição(ões) · '.count($certs).' certificado(s)',
                    'icone' => 'fa-calendar',
                    'tom' => 'var(--cor)',
                    'corpo' => 'Marque «aceitar inscrições externas» para gerar páginas públicas de inscrição, presença e certificado.',
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
            'resumo' => 'Cadastre o evento → libere inscrição (interna e/ou externa) → presença → certificados.',
        ]);
    }

    public function eventos(): View
    {
        $registros = array_map(static function (array $e): array {
            $e['links_publicos'] = [
                'inscricao' => EventoPublico::urlInscricao($e),
                'presenca' => EventoPublico::urlPresenca($e),
                'certificado' => EventoPublico::urlCertificado($e),
            ];
            $e['vagas_restantes'] = EventoPublico::vagasRestantes($e);

            return $e;
        }, PocRegistro::listar('evento'));

        return view('eventos::eventos', [
            'registros' => $registros,
            'portalUrl' => url('/eventos-formacao/portal'),
        ]);
    }

    public function exportarEventos(Request $request): Response|RedirectResponse
    {
        $linhas = [];
        foreach (PocRegistro::listar('evento') as $r) {
            $p = $r['payload'];
            $linhas[] = [
                $r['id'] ?? '',
                (string) ($r['titulo'] ?? ''),
                (string) ($p['local'] ?? ''),
                (string) ($p['inicio'] ?? ''),
                (string) ($p['fim'] ?? ''),
                (int) ($p['vagas'] ?? 0),
                $p['carga_horaria'] ?? '',
                (string) ($p['status'] ?? ''),
                (string) ($p['publico'] ?? ''),
            ];
        }

        return \iEducar\Packages\BuritiExportacaoUnificada\Support\ExportacaoModulo::download(
            'Eventos de formação',
            ['ID', 'Título', 'Local', 'Início', 'Fim', 'Vagas', 'Carga horária', 'Status', 'Público'],
            $linhas,
            'Eventos e formação',
            strtolower(trim((string) $request->input('formato', 'pdf'))),
            null,
            null,
            'eventos-formacao',
        );
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
            'inscricao_externa' => 'nullable|boolean',
            'certificado' => 'nullable|boolean',
        ]);

        $titulo = (string) $dados['titulo'];
        $externa = $request->boolean('inscricao_externa');
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
            'inscricao_externa' => $externa,
            'publico_token' => $externa ? EventoPublico::tokenNovo() : null,
            'certificado' => $request->boolean('certificado', true),
        ];

        PocRegistro::salvar('evento', $titulo, $payload['local'], $payload);

        $msg = 'Evento cadastrado.';
        if ($externa) {
            $msg .= ' Portal externo liberado (inscrição, presença e certificado).';
        }

        return redirect('/eventos-formacao/eventos')->with('ok', $msg);
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

        $cpf = EventoPublico::limparCpf($dados['cpf'] ?? '');
        $id = PocRegistro::salvar('inscricao', (string) $dados['nome'], $evento['titulo'], [
            'evento_id' => (int) $evento['id'],
            'evento' => $evento['titulo'],
            'cpf' => $cpf !== '' ? $cpf : null,
            'tipo' => $dados['tipo'] ?? 'servidor',
            'origem' => 'admin',
            'status' => 'confirmada',
            'protocolo' => null,
        ]);
        $protocolo = EventoPublico::protocolo($id);
        $insc = PocRegistro::buscar($id);
        if ($insc !== null) {
            $payload = $insc['payload'];
            $payload['protocolo'] = $protocolo;
            PocRegistro::atualizar($id, $payload, $insc['titulo'], $protocolo);
        }

        return redirect('/eventos-formacao/inscricoes')->with('ok', 'Inscrição registrada ('.$protocolo.').');
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
