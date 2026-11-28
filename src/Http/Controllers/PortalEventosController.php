<?php

namespace iEducar\Packages\BuritiEventosFormacao\Http\Controllers;

use iEducar\Packages\BuritiEventosFormacao\Services\CertificadoPresenca;
use iEducar\Packages\BuritiEventosFormacao\Services\EventoInscricao;
use iEducar\Packages\BuritiEventosFormacao\Support\EventoPublico;
use iEducar\Packages\BuritiEventosFormacao\Support\Identidade;
use iEducar\Packages\BuritiEventosFormacao\Support\PocRegistro;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class PortalEventosController extends Controller
{
    public function inicio(): View|RedirectResponse
    {
        if (! EventoPublico::portalModuloHabilitado()) {
            return redirect('/login')->with('erro', 'O portal externo de eventos está desabilitado na configuração municipal.');
        }

        $abertos = array_values(array_filter(
            PocRegistro::listar('evento'),
            static fn (array $e): bool => EventoPublico::aceitaExterna($e) && EventoPublico::inscricoesAbertas($e)
        ));

        return view('eventos::portal.inicio', [
            'eventos' => $abertos,
            'identidade' => Identidade::tokens(),
            'municipio' => config('legacy.app.entity.name') ?: 'Rede municipal',
        ]);
    }

    public function evento(string $token): View|RedirectResponse
    {
        $evento = EventoPublico::buscarPorToken($token);
        if ($evento === null || ! EventoPublico::aceitaExterna($evento)) {
            return redirect('/eventos-formacao/portal')->with('erro', 'Evento não encontrado ou sem inscrição externa.');
        }

        return view('eventos::portal.evento', [
            'evento' => $evento,
            'token' => $token,
            'abertas' => EventoPublico::inscricoesAbertas($evento),
            'vagasRestantes' => EventoPublico::vagasRestantes($evento),
            'urlPresenca' => EventoPublico::urlPresenca($evento),
            'urlCertificado' => EventoPublico::urlCertificado($evento),
            'identidade' => Identidade::tokens(),
            'municipio' => config('legacy.app.entity.name') ?: 'Rede municipal',
        ]);
    }

    public function inscrever(Request $request, string $token): RedirectResponse
    {
        $evento = EventoPublico::buscarPorToken($token);
        if ($evento === null || ! EventoPublico::aceitaExterna($evento)) {
            return redirect('/eventos-formacao/portal')->with('erro', 'Evento indisponível.');
        }
        if (! EventoPublico::inscricoesAbertas($evento)) {
            return back()->with('erro', 'Inscrições encerradas para este evento.');
        }

        $dados = $request->validate([
            'nome' => 'required|string|max:180',
            'cpf' => 'required|string|max:14',
            'email' => 'nullable|email|max:120',
            'telefone' => 'nullable|string|max:20',
        ]);

        $cpf = EventoPublico::limparCpf($dados['cpf']);
        if (strlen($cpf) !== 11) {
            return back()->withInput()->with('erro', 'Informe um CPF válido.');
        }

        $eventoId = (int) $evento['id'];
        foreach (PocRegistro::listar('inscricao') as $i) {
            if ((int) ($i['payload']['evento_id'] ?? 0) !== $eventoId) {
                continue;
            }
            if (EventoPublico::limparCpf($i['payload']['cpf'] ?? '') === $cpf) {
                return back()->withInput()->with('erro', 'Já existe inscrição com este CPF neste evento.');
            }
        }

        $vagas = (int) ($evento['payload']['vagas'] ?? 0);
        $ja = 0;
        foreach (PocRegistro::listar('inscricao') as $i) {
            if ((int) ($i['payload']['evento_id'] ?? 0) === $eventoId) {
                $ja++;
            }
        }
        try {
            (new EventoInscricao($vagas > 0 ? $vagas : PHP_INT_MAX, $ja))->inscrever();
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('erro', $e->getMessage());
        }

        $id = PocRegistro::salvar('inscricao', (string) $dados['nome'], $evento['titulo'], [
            'evento_id' => $eventoId,
            'evento' => $evento['titulo'],
            'cpf' => $cpf,
            'email' => $dados['email'] ?? null,
            'telefone' => $dados['telefone'] ?? null,
            'tipo' => 'externo',
            'origem' => 'portal',
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

        return redirect('/eventos-formacao/portal/comprovante/'.$id)
            ->with('ok', 'Inscrição confirmada. Guarde o protocolo.');
    }

    public function comprovante(int $id): View|RedirectResponse
    {
        $insc = PocRegistro::buscar($id);
        if ($insc === null || $insc['tipo'] !== 'inscricao') {
            return redirect('/eventos-formacao/portal')->with('erro', 'Comprovante não encontrado.');
        }
        $evento = PocRegistro::buscar((int) ($insc['payload']['evento_id'] ?? 0));

        return view('eventos::portal.comprovante', [
            'inscricao' => $insc,
            'evento' => $evento,
            'protocolo' => $insc['payload']['protocolo'] ?? EventoPublico::protocolo($id),
            'identidade' => Identidade::tokens(),
            'municipio' => config('legacy.app.entity.name') ?: 'Rede municipal',
        ]);
    }

    public function presenca(string $token): View|RedirectResponse
    {
        $evento = EventoPublico::buscarPorToken($token);
        if ($evento === null || ! EventoPublico::aceitaExterna($evento)) {
            return redirect('/eventos-formacao/portal')->with('erro', 'Evento não encontrado.');
        }

        return view('eventos::portal.presenca', [
            'evento' => $evento,
            'token' => $token,
            'identidade' => Identidade::tokens(),
            'municipio' => config('legacy.app.entity.name') ?: 'Rede municipal',
        ]);
    }

    public function salvarPresenca(Request $request, string $token): RedirectResponse
    {
        $evento = EventoPublico::buscarPorToken($token);
        if ($evento === null || ! EventoPublico::aceitaExterna($evento)) {
            return redirect('/eventos-formacao/portal')->with('erro', 'Evento indisponível.');
        }

        $dados = $request->validate([
            'cpf' => 'required|string|max:14',
            'situacao' => 'nullable|in:presente,parcial,ausente',
        ]);
        $cpf = EventoPublico::limparCpf($dados['cpf']);
        $eventoId = (int) $evento['id'];

        $insc = null;
        foreach (PocRegistro::listar('inscricao') as $i) {
            if ((int) ($i['payload']['evento_id'] ?? 0) !== $eventoId) {
                continue;
            }
            if (EventoPublico::limparCpf($i['payload']['cpf'] ?? '') === $cpf) {
                $insc = $i;
                break;
            }
        }
        if ($insc === null) {
            return back()->withInput()->with('erro', 'Não há inscrição com este CPF neste evento.');
        }

        $situacao = $dados['situacao'] ?? 'presente';
        $pct = match ($situacao) {
            'presente' => 100.0,
            'parcial' => 50.0,
            default => 0.0,
        };

        foreach (PocRegistro::listar('presenca') as $p) {
            if ((int) ($p['payload']['inscricao_id'] ?? 0) === (int) $insc['id']) {
                $payload = $p['payload'];
                $payload['percentual'] = $pct;
                $payload['situacao'] = $situacao;
                $payload['origem'] = 'portal';
                $payload['lancado_em'] = now()->toDateTimeString();
                PocRegistro::atualizar((int) $p['id'], $payload);

                return back()->with('ok', 'Presença atualizada para '.$insc['titulo'].'.');
            }
        }

        PocRegistro::salvar('presenca', $insc['titulo'], $evento['titulo'], [
            'inscricao_id' => (int) $insc['id'],
            'evento_id' => $eventoId,
            'evento' => $evento['titulo'],
            'percentual' => $pct,
            'situacao' => $situacao,
            'origem' => 'portal',
            'lancado_em' => now()->toDateTimeString(),
        ]);

        return back()->with('ok', 'Presença registrada para '.$insc['titulo'].'.');
    }

    public function certificado(string $token): View|RedirectResponse
    {
        $evento = EventoPublico::buscarPorToken($token);
        if ($evento === null || ! EventoPublico::aceitaExterna($evento)) {
            return redirect('/eventos-formacao/portal')->with('erro', 'Evento não encontrado.');
        }
        if (! ($evento['payload']['certificado'] ?? true)) {
            return redirect(EventoPublico::urlInscricao($evento) ?? '/eventos-formacao/portal')
                ->with('erro', 'Este evento não emite certificado.');
        }

        return view('eventos::portal.certificado', [
            'evento' => $evento,
            'token' => $token,
            'minimo' => (float) config('eventos.frequencia_minima_certificado', 75),
            'identidade' => Identidade::tokens(),
            'municipio' => config('legacy.app.entity.name') ?: 'Rede municipal',
        ]);
    }

    public function emitirCertificadoPublico(Request $request, string $token): RedirectResponse|View
    {
        $evento = EventoPublico::buscarPorToken($token);
        if ($evento === null || ! EventoPublico::aceitaExterna($evento)) {
            return redirect('/eventos-formacao/portal')->with('erro', 'Evento indisponível.');
        }
        if (! ($evento['payload']['certificado'] ?? true)) {
            return back()->with('erro', 'Este evento não emite certificado.');
        }

        $dados = $request->validate([
            'cpf' => 'required|string|max:14',
        ]);
        $cpf = EventoPublico::limparCpf($dados['cpf']);
        $eventoId = (int) $evento['id'];
        $min = (float) config('eventos.frequencia_minima_certificado', 75);

        $insc = null;
        foreach (PocRegistro::listar('inscricao') as $i) {
            if ((int) ($i['payload']['evento_id'] ?? 0) !== $eventoId) {
                continue;
            }
            if (EventoPublico::limparCpf($i['payload']['cpf'] ?? '') === $cpf) {
                $insc = $i;
                break;
            }
        }
        if ($insc === null) {
            return back()->withInput()->with('erro', 'Inscrição não encontrada para este CPF.');
        }

        $pres = null;
        foreach (PocRegistro::listar('presenca') as $p) {
            if ((int) ($p['payload']['inscricao_id'] ?? 0) === (int) $insc['id']) {
                $pres = $p;
                break;
            }
        }
        if ($pres === null) {
            return back()->withInput()->with('erro', 'Presença ainda não lançada. Marque presença antes de emitir o certificado.');
        }

        $pct = (float) ($pres['payload']['percentual'] ?? 0);
        if (! CertificadoPresenca::elegivel($pct, $min)) {
            return back()->withInput()->with('erro', "Frequência {$pct}% abaixo do mínimo {$min}%.");
        }

        $codigo = CertificadoPresenca::codigo($eventoId, (int) $insc['id']);
        foreach (PocRegistro::listar('certificado') as $c) {
            if (($c['payload']['codigo'] ?? '') === $codigo) {
                return view('eventos::portal.certificado-emitido', [
                    'evento' => $evento,
                    'inscricao' => $insc,
                    'certificado' => $c,
                    'codigo' => $codigo,
                    'percentual' => $pct,
                    'identidade' => Identidade::tokens(),
                    'municipio' => config('legacy.app.entity.name') ?: 'Rede municipal',
                ]);
            }
        }

        $id = PocRegistro::salvar('certificado', $insc['titulo'], $codigo, [
            'presenca_id' => (int) $pres['id'],
            'inscricao_id' => (int) $insc['id'],
            'evento_id' => $eventoId,
            'evento' => $evento['titulo'],
            'percentual' => $pct,
            'codigo' => $codigo,
            'origem' => 'portal',
            'emitido_em' => now()->toDateTimeString(),
        ]);

        return view('eventos::portal.certificado-emitido', [
            'evento' => $evento,
            'inscricao' => $insc,
            'certificado' => PocRegistro::buscar($id),
            'codigo' => $codigo,
            'percentual' => $pct,
            'identidade' => Identidade::tokens(),
            'municipio' => config('legacy.app.entity.name') ?: 'Rede municipal',
        ]);
    }
}
