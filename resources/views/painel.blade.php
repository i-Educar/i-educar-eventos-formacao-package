@extends('eventos::layout')
@section('titulo', 'Início — Eventos e formação')
@section('migalha', 'Início')
@section('icone-tela', 'fa-calendar')
@section('titulo-tela', 'Eventos e formação')
@section('lead')
    Cadastre cursos e palestras, controle vagas, presença e certificados.
@endsection
@section('acoes-topo')
    <a class="botao" href="{{ url('/eventos-formacao/eventos') }}"><i class="fa fa-plus"></i> Novo evento</a>
    <a class="botao claro" href="{{ url('/eventos-formacao/inscricoes') }}"><i class="fa fa-ticket"></i> Inscrições</a>
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
