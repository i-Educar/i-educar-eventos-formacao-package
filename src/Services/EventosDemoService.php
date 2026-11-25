<?php

namespace iEducar\Packages\BuritiEventosFormacao\Services;

use iEducar\Packages\BuritiEventosFormacao\Support\PocRegistro;
use Illuminate\Support\Facades\DB;

/**
 * Carga demo: eventos, inscrições, presença e certificados.
 */
final class EventosDemoService
{
    public const DEMO_TAG = 'laguna-carga-v1';

    /**
     * @return array{eventos:int,inscricoes:int,presencas:int,certificados:int,pulado:bool,mensagem:string}
     */
    public function executar(bool $force = false): array
    {
        $vazio = [
            'eventos' => 0,
            'inscricoes' => 0,
            'presencas' => 0,
            'certificados' => 0,
            'pulado' => false,
            'mensagem' => '',
        ];

        if (! PocRegistro::pronto()) {
            $vazio['mensagem'] = 'schema eventos_formacao ausente';

            return $vazio;
        }

        if (! $force && $this->jaPovoado()) {
            $vazio['pulado'] = true;
            $vazio['mensagem'] = 'já povoado (use --force)';

            return $vazio;
        }

        if ($force) {
            $this->limparDemo();
        }

        $eventos = $this->semearEventos();
        $inscricoes = $this->semearInscricoes($eventos);
        $presencas = $this->semearPresencas($inscricoes);
        $certs = $this->semearCertificados($inscricoes, $presencas);

        return [
            'eventos' => count($eventos),
            'inscricoes' => count($inscricoes),
            'presencas' => $presencas,
            'certificados' => $certs,
            'pulado' => false,
            'mensagem' => sprintf(
                'eventos=%d inscricoes=%d presencas=%d certificados=%d',
                count($eventos),
                count($inscricoes),
                $presencas,
                $certs
            ),
        ];
    }

    private function jaPovoado(): bool
    {
        foreach (PocRegistro::listar('evento') as $r) {
            if (($r['payload']['demo'] ?? null) === self::DEMO_TAG) {
                return true;
            }
        }

        return false;
    }

    private function limparDemo(): void
    {
        foreach (['evento', 'inscricao', 'presenca', 'certificado'] as $tipo) {
            foreach (PocRegistro::listar($tipo) as $r) {
                if (($r['payload']['demo'] ?? null) === self::DEMO_TAG) {
                    DB::table('eventos_formacao.poc_registro')->where('id', $r['id'])->delete();
                }
            }
        }
    }

    /**
     * @return list<array{id:int,titulo:string,vagas:int,status:string}>
     */
    private function semearEventos(): array
    {
        $cenarios = [
            [
                'titulo' => 'Formação BNCC — Anos iniciais',
                'local' => 'CEFOR · Auditório',
                'inicio' => now()->addDays(12)->format('Y-m-d'),
                'fim' => now()->addDays(13)->format('Y-m-d'),
                'carga_horaria' => 16,
                'vagas' => 40,
                'status' => 'aberto',
            ],
            [
                'titulo' => 'Oficina de busca ativa escolar',
                'local' => 'SME Laguna · Sala 2',
                'inicio' => now()->addDays(5)->format('Y-m-d'),
                'fim' => now()->addDays(5)->format('Y-m-d'),
                'carga_horaria' => 8,
                'vagas' => 25,
                'status' => 'aberto',
            ],
            [
                'titulo' => 'Palestra saúde mental nas escolas',
                'local' => 'EMEF Anita Garibaldi',
                'inicio' => now()->subDays(20)->format('Y-m-d'),
                'fim' => now()->subDays(20)->format('Y-m-d'),
                'carga_horaria' => 4,
                'vagas' => 60,
                'status' => 'encerrado',
            ],
            [
                'titulo' => 'Curso de gestão de merenda',
                'local' => 'Online (Teams)',
                'inicio' => now()->addDays(30)->format('Y-m-d'),
                'fim' => now()->addDays(45)->format('Y-m-d'),
                'carga_horaria' => 20,
                'vagas' => 80,
                'status' => 'rascunho',
            ],
        ];

        $out = [];
        foreach ($cenarios as $c) {
            $id = PocRegistro::salvar('evento', $c['titulo'], $c['local'], array_merge($c, [
                'demo' => self::DEMO_TAG,
                'organizador' => 'SME Laguna · Formação continuada',
            ]));
            $out[] = [
                'id' => $id,
                'titulo' => $c['titulo'],
                'vagas' => $c['vagas'],
                'status' => $c['status'],
            ];
        }

        return $out;
    }

    /**
     * @param  list<array{id:int,titulo:string,vagas:int,status:string}>  $eventos
     * @return list<array{id:int,evento_id:int,evento:string,nome:string}>
     */
    private function semearInscricoes(array $eventos): array
    {
        $pessoas = [
            ['nome' => 'Ana Paula Costa', 'cpf' => '11122233344', 'tipo' => 'servidor'],
            ['nome' => 'Bruno Ferreira Lima', 'cpf' => '22233344455', 'tipo' => 'servidor'],
            ['nome' => 'Carla Mendes Souza', 'cpf' => '33344455566', 'tipo' => 'servidor'],
            ['nome' => 'Diego Nunes Rocha', 'cpf' => '44455566677', 'tipo' => 'externo'],
            ['nome' => 'Elisa Barbosa Pinto', 'cpf' => '55566677788', 'tipo' => 'servidor'],
            ['nome' => 'Fábio Alves Martins', 'cpf' => '66677788899', 'tipo' => 'servidor'],
            ['nome' => 'Gisele Ramos Dias', 'cpf' => '77788899900', 'tipo' => 'externo'],
            ['nome' => 'Henrique Castro', 'cpf' => '88899900011', 'tipo' => 'servidor'],
        ];

        $abertos = array_values(array_filter(
            $eventos,
            static fn (array $e): bool => in_array($e['status'], ['aberto', 'encerrado'], true)
        ));
        if ($abertos === []) {
            return [];
        }

        $out = [];
        foreach ($pessoas as $i => $p) {
            $ev = $abertos[$i % count($abertos)];
            $id = PocRegistro::salvar('inscricao', $p['nome'], $ev['titulo'], [
                'evento_id' => $ev['id'],
                'evento' => $ev['titulo'],
                'cpf' => $p['cpf'],
                'tipo' => $p['tipo'],
                'status' => 'confirmada',
                'demo' => self::DEMO_TAG,
            ]);
            $out[] = [
                'id' => $id,
                'evento_id' => $ev['id'],
                'evento' => $ev['titulo'],
                'nome' => $p['nome'],
            ];
        }

        return $out;
    }

    /**
     * @param  list<array{id:int,evento_id:int,evento:string,nome:string}>  $inscricoes
     */
    private function semearPresencas(array $inscricoes): int
    {
        $n = 0;
        foreach ($inscricoes as $i => $ins) {
            // Metade com presença plena; algumas parciais; uma ausente.
            $pct = match ($i % 5) {
                0 => 100.0,
                1 => 90.0,
                2 => 70.0,
                3 => 50.0,
                default => 0.0,
            };
            $situacao = $pct >= 75 ? 'presente' : ($pct <= 0 ? 'ausente' : 'parcial');
            PocRegistro::salvar('presenca', $ins['nome'], $ins['evento'], [
                'inscricao_id' => $ins['id'],
                'evento_id' => $ins['evento_id'],
                'evento' => $ins['evento'],
                'percentual' => $pct,
                'situacao' => $situacao,
                'demo' => self::DEMO_TAG,
            ]);
            $n++;
        }

        return $n;
    }

    /**
     * @param  list<array{id:int,evento_id:int,evento:string,nome:string}>  $inscricoes
     */
    private function semearCertificados(array $inscricoes, int $presencas): int
    {
        unset($presencas);
        $n = 0;
        foreach ($inscricoes as $i => $ins) {
            if ($i % 5 === 3 || $i % 5 === 4) {
                continue; // parcial/ausente sem certificado
            }
            PocRegistro::salvar('certificado', $ins['nome'], $ins['evento'], [
                'inscricao_id' => $ins['id'],
                'evento_id' => $ins['evento_id'],
                'evento' => $ins['evento'],
                'emitido_em' => now()->toDateString(),
                'codigo' => 'EVT-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'demo' => self::DEMO_TAG,
            ]);
            $n++;
        }

        return $n;
    }
}
