@extends('eventos::layout')
@section('titulo', 'Início — Eventos e formação')
@section('migalha', 'Início')
@section('icone-tela', 'fa-calendar')
@section('titulo-tela', 'Eventos e formação')
@section('lead')
    Cadastre cursos e palestras, controle vagas, presença e certificados.
@endsection
@section('acoes-topo')
    <a class="botao icone" href="{{ url('/eventos-formacao/eventos') }}" title="Novo evento" aria-label="Novo evento"><i aria-hidden="true" class="fa fa-plus"></i></a>
    <a class="botao claro icone" href="{{ url('/eventos-formacao/inscricoes') }}" title="Inscrições" aria-label="Inscrições"><i aria-hidden="true" class="fa fa-ticket"></i></a>
@endsection

@section('conteudo')
    @include('buriti-ui::painel-corpo', [
        'kpis' => $kpis ?? [],
        'atalhos' => $atalhos ?? [],
        'destaques' => $destaques ?? [],
        'analise' => $analise ?? [],
        'analiseTitulo' => $analiseTitulo ?? 'Últimos eventos',
        'resumo' => $resumo ?? null,
        'fluxoTitulo' => $fluxoTitulo ?? 'Fluxo do módulo',
    ])
@endsection
