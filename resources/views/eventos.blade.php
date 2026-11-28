@extends('eventos::layout')
@section('titulo', 'Eventos')
@section('migalha', 'Eventos')
@section('icone-tela', 'fa-calendar')
@section('titulo-tela', 'Gestão de eventos')
@section('lead')Cadastre, abra inscrição interna/externa e acompanhe presença e certificados.@endsection

@section('acoes-topo')
    @include('exportacao::partials.botoes-exportar', [
        'action' => url('/eventos-formacao/eventos/exportar'),
        'formatos' => ['pdf', 'xlsx', 'csv'],
        'opcoes' => [
            ['value' => 'lista', 'label' => 'Lista de eventos'],
        ],
        'tituloModal' => 'Download — Eventos',
    ])
    <a class="botao claro icone" href="{{ $portalUrl ?? url('/eventos-formacao/portal') }}" target="_blank" rel="noopener" title="Portal público" aria-label="Portal público"><i aria-hidden="true" class="fa fa-external-link"></i></a>
    <button type="button" class="botao primaria icone" data-buriti-modal="modal-novo-evento" title="Novo evento" aria-label="Novo evento"><i aria-hidden="true" class="fa fa-plus"></i></button>
@endsection

@section('foco')
<section class="buriti-foco">
    <p class="buriti-foco-kicker">Operação</p>
    <h2 class="buriti-foco-titulo">{{ count($registros ?? []) }} evento{{ count($registros ?? []) === 1 ? '' : 's' }}</h2>
    <p class="buriti-foco-texto">Com inscrição externa, o sistema gera links de inscrição, presença e certificado para compartilhar.</p>
</section>
@endsection

@section('conteudo')

<section class="bloco">
    <h2><i class="fa fa-list"></i> Eventos cadastrados</h2>
    @if(($registros ?? []) === [])
        <p style="margin:0;color:var(--suave)">Nenhum evento ainda — use <strong>Novo evento</strong>.</p>
    @else
        <div style="overflow-x:auto">
            <table>
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Período</th>
                        <th>Vagas</th>
                        <th>Status</th>
                        <th>Externa</th>
                        <th>Links públicos</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($registros as $r)
                    @php
                        $p = $r['payload'];
                        $links = $r['links_publicos'] ?? [];
                        $externa = !empty($p['inscricao_externa']);
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $r['titulo'] }}</strong>
                            <div style="color:var(--suave);font-size:.8rem">{{ $p['local'] ?? '—' }}</div>
                        </td>
                        <td>{{ $p['inicio'] ?? '—' }} → {{ $p['fim'] ?? '—' }}</td>
                        <td>
                            {{ $p['vagas'] ?? 0 }}
                            @if(($r['vagas_restantes'] ?? null) !== null)
                                <div style="color:var(--suave);font-size:.78rem">{{ $r['vagas_restantes'] }} livres</div>
                            @endif
                        </td>
                        <td><span class="badge {{ ($p['status'] ?? '') === 'aberto' ? 'ok' : 'warn' }}">{{ $p['status'] ?? '—' }}</span></td>
                        <td>
                            @if($externa)
                                <span class="badge ok">sim</span>
                            @else
                                <span class="badge warn">não</span>
                            @endif
                        </td>
                        <td>
                            @if($externa && !empty($links['inscricao']))
                                <div style="display:flex;flex-wrap:wrap;gap:6px">
                                    <a class="botao claro" style="padding:4px 8px;font-size:.78rem" href="{{ $links['inscricao'] }}" target="_blank" rel="noopener" title="Inscrição">
                                        <i class="fa fa-ticket"></i>
                                    </a>
                                    <a class="botao claro" style="padding:4px 8px;font-size:.78rem" href="{{ $links['presenca'] }}" target="_blank" rel="noopener" title="Presença">
                                        <i class="fa fa-check-square-o"></i>
                                    </a>
                                    @if($p['certificado'] ?? true)
                                        <a class="botao claro" style="padding:4px 8px;font-size:.78rem" href="{{ $links['certificado'] }}" target="_blank" rel="noopener" title="Certificado">
                                            <i class="fa fa-certificate"></i>
                                        </a>
                                    @endif
                                </div>
                            @else
                                <span style="color:var(--suave)">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>

@include('buriti-ui::operacao-modal', [
    'id' => 'modal-novo-evento',
    'titulo' => 'Novo evento',
    'lead' => 'Defina período, vagas e se haverá inscrição externa no portal público.',
    'corpo' => view('eventos::partials.evento-form')->render(),
])
@endsection
