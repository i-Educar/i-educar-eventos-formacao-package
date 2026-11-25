<?php

use App\Menu;
use App\Services\MenuCacheService;
use iEducar\Packages\BuritiEventosFormacao\Support\Processos;
use iEducar\Packages\BuritiLagunaAmbiente\Support\PermissaoMatriz;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Menu Eventos sob Servidores, com grupos Cadastros / Operação / Relatórios / Auditoria
 * para a tela Tipos de usuário (AccessLevel).
 */
return new class extends Migration
{
    public function up(): void
    {
        $servidores = Menu::query()
            ->whereNull('parent_id')
            ->where(function ($q) {
                $q->where('process', 71)->orWhere('title', 'Servidores');
            })
            ->first();

        if ($servidores === null) {
            return;
        }

        $raiz = Menu::query()->updateOrCreate(['process' => Processos::RAIZ], [
            'parent_id' => $servidores->getKey(),
            'title' => 'Eventos e formação',
            'description' => 'Cursos, palestras, inscrição e certificados',
            'link' => '/eventos-formacao',
            'icon' => 'fa-calendar',
            'order' => 5,
            'type' => 2,
            'active' => true,
        ]);

        $grupos = [
            'Cadastros' => 1,
            'Operação' => 2,
            'Relatórios' => 3,
            'Auditoria' => 4,
        ];
        $ids = [];
        foreach ($grupos as $titulo => $ordem) {
            $g = Menu::query()->updateOrCreate(
                ['parent_id' => $raiz->getKey(), 'title' => $titulo],
                [
                    'order' => $ordem,
                    'type' => 2,
                    'process' => null,
                    'active' => true,
                    'link' => null,
                    'description' => 'Grupo ACL — '.$titulo,
                ],
            );
            $ids[$titulo] = $g->getKey();
        }

        // Remove folhas soltas antigas (filhas diretas da raiz com process).
        Menu::query()
            ->where('parent_id', $raiz->getKey())
            ->whereNotNull('process')
            ->whereIn('process', Processos::todos())
            ->delete();

        $folhas = [
            [Processos::EVENTOS, $ids['Cadastros'], 'Eventos', '/eventos-formacao/eventos', 'Cadastro de eventos, local e vagas', 1],
            [Processos::PAINEL, $ids['Operação'], 'Painel', '/eventos-formacao', 'Visão geral', 1],
            [Processos::INSCRICOES, $ids['Operação'], 'Inscrições', '/eventos-formacao/inscricoes', 'Inscrições e controle de vagas', 2],
            [Processos::PRESENCA, $ids['Operação'], 'Presença', '/eventos-formacao/presenca', 'Lançamento de frequência', 3],
            [Processos::REL_FREQUENCIA, $ids['Relatórios'], 'Frequência', '/eventos-formacao/presenca', 'Relatório de comparecimento', 1],
            [Processos::CERTIFICADOS, $ids['Auditoria'], 'Certificados', '/eventos-formacao/certificados', 'Emissão e códigos', 1],
        ];

        foreach ($folhas as [$processo, $pai, $titulo, $link, $descricao, $ordem]) {
            Menu::query()->updateOrCreate(['process' => $processo], [
                'parent_id' => $pai,
                'title' => $titulo,
                'description' => $descricao,
                'link' => $link,
                'order' => $ordem,
                'type' => 3,
                'active' => true,
            ]);
        }

        // Perfil dedicado (ajustável em Configurações → Tipos de usuário).
        DB::table('pmieducar.tipo_usuario')->updateOrInsert(
            ['cod_tipo_usuario' => 13],
            [
                'nm_tipo' => 'Operador Eventos',
                'descricao' => 'Eventos, formação, inscrição e certificados',
                'nivel' => 1,
                'ref_funcionario_cad' => 1,
                'data_cadastro' => now(),
                'ativo' => 1,
            ],
        );

        // Tipos com acesso padrão ao módulo (ajustáveis na tela de permissões).
        $tiposComAcesso = [1, 2, 3, 4, 5, 13];
        PermissaoMatriz::revogarForaDe(Processos::todos(), $tiposComAcesso);
        PermissaoMatriz::conceder(
            Processos::todos(),
            $tiposComAcesso,
            processosSomenteVisualiza: [Processos::REL_FREQUENCIA],
        );

        if (class_exists(MenuCacheService::class)) {
            app(MenuCacheService::class)->flushAll();
        }
    }

    public function down(): void
    {
        $raiz = Menu::query()->where('process', Processos::RAIZ)->first();
        if ($raiz === null) {
            return;
        }

        $grupos = Menu::query()->where('parent_id', $raiz->getKey())->pluck('id');
        $folhas = Menu::query()->whereIn('parent_id', $grupos)->pluck('id');
        $ids = $folhas->merge($grupos)->push($raiz->getKey());

        DB::table('pmieducar.menu_tipo_usuario')->whereIn('menu_id', $ids)->delete();
        Menu::query()->whereIn('id', $folhas)->delete();
        Menu::query()->whereIn('id', $grupos)->delete();
        $raiz->delete();

        if (class_exists(MenuCacheService::class)) {
            app(MenuCacheService::class)->flushAll();
        }
    }
};
