<?php

namespace iEducar\Packages\BuritiEventosFormacao\Support;

final class MenuFluxo
{
    /**
     * @param  array<string, bool>  $permite
     * @return list<array{nome:string,itens:list<array{rotulo:string,url:string,icone:string,chave:string}>}>
     */
    public static function grupos(array $permite): array
    {
        $item = static function (string $chave, string $rotulo, string $url, string $icone) use ($permite): ?array {
            if (! ($permite[$chave] ?? true)) {
                return null;
            }

            return compact('chave', 'rotulo', 'url', 'icone');
        };
        $filtrar = static fn (array $itens): array => array_values(array_filter($itens));

        return array_values(array_filter([
            [
                'nome' => 'Operação',
                'itens' => $filtrar([
                    $item('painel', 'Início', '/eventos-formacao', 'fa-th-large'),
                    $item('inscricoes', 'Inscrições', '/eventos-formacao/inscricoes', 'fa-ticket'),
                    $item('presenca', 'Presença', '/eventos-formacao/presenca', 'fa-check-square-o'),
                ]),
            ],
            [
                'nome' => 'Cadastros',
                'itens' => $filtrar([
                    $item('eventos', 'Eventos', '/eventos-formacao/eventos', 'fa-calendar'),
                ]),
            ],
            [
                'nome' => 'Relatórios',
                'itens' => $filtrar([
                    $item('frequencia', 'Frequência', '/eventos-formacao/presenca', 'fa-bar-chart'),
                ]),
            ],
            [
                'nome' => 'Auditoria',
                'itens' => $filtrar([
                    $item('certificados', 'Certificados', '/eventos-formacao/certificados', 'fa-certificate'),
                ]),
            ],
        ], static fn (array $g): bool => $g['itens'] !== []));
    }
}
