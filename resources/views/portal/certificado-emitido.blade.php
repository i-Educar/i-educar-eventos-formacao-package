@extends('eventos::portal.layout')
@section('titulo', 'Certificado emitido')

@section('conteudo')
    <h1>Certificado</h1>
    <p class="lead">Documento de participação no evento municipal.</p>

    <section class="cartao">
        <h2><i class="fa fa-certificate"></i> {{ $evento['titulo'] }}</h2>
        <p class="meta-linha">Participante: <strong>{{ $inscricao['titulo'] }}</strong></p>
        <p class="meta-linha">Frequência: {{ $percentual }}%</p>
        <p class="protocolo-grande">{{ $codigo }}</p>
        <p class="meta-linha">
            Emitido em {{ ($certificado['payload']['emitido_em'] ?? now()->toDateTimeString()) }}
            · {{ $municipio ?? 'Rede municipal' }}
        </p>
        <div class="acoes no-print">
            <button type="button" class="botao" onclick="window.print()"><i class="fa fa-print"></i> Imprimir</button>
            <a class="botao claro" href="{{ url('/eventos-formacao/portal/certificado/'.$evento['payload']['publico_token']) }}">Nova consulta</a>
        </div>
    </section>
@endsection
