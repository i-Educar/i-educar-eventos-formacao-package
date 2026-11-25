<?php

use App\Menu;
use App\Services\MenuCacheService;
use iEducar\Packages\BuritiEventosFormacao\Support\Processos;
use iEducar\Packages\BuritiLagunaAmbiente\Support\PermissaoMatriz;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Corrige instalações que já rodaram o menu flat:
 * - reagrupa em Cadastros / Operação / Relatórios / Auditoria
 * - cria perfil Operador Eventos (cod 13)
 * - reaplica ACL só nos tipos que devem gerir o módulo
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->garantirPerfilOperador();
        $this->reagruparMenus();
        $this->aplicarAcl();

        if (class_exists(MenuCacheService::class)) {
            app(MenuCacheService::class)->flushAll();
        }
    }

    public function down(): void
    {
        // Mantém estrutura; remove só o perfil dedicado se não houver usuários.
        $temUsuario = DB::table('pmieducar.usuario')
            ->where('ref_cod_tipo_usuario', 13)
            ->where('ativo', 1)
            ->exists();
        if (! $temUsuario) {
            DB::table('pmieducar.menu_tipo_usuario')->where('ref_cod_tipo_usuario', 13)->delete();
            DB::table('pmieducar.tipo_usuario')->where('cod_tipo_usuario', 13)->delete();
        }
    }

    private function garantirPerfilOperador(): void
    {
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
    }

    private function reagruparMenus(): void
    {
        $raiz = Menu::query()->where('process', Processos::RAIZ)->first();
        if ($raiz === null) {
            return;
        }

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

        // Limpa folhas órfãs antigas que ficaram na raiz.
        Menu::query()
            ->where('parent_id', $raiz->getKey())
            ->whereNotNull('process')
            ->whereIn('process', Processos::todos())
            ->delete();
    }

    private function aplicarAcl(): void
    {
        $tiposComAcesso = [1, 2, 3, 4, 5, 13];
        PermissaoMatriz::revogarForaDe(Processos::todos(), $tiposComAcesso);
        PermissaoMatriz::conceder(
            Processos::todos(),
            $tiposComAcesso,
            processosSomenteVisualiza: [Processos::REL_FREQUENCIA],
        );
    }
};
