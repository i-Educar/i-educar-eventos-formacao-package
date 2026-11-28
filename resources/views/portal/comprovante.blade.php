@extends('eventos::portal.layout')
@section('titulo', 'Comprovante de inscrição')

@section('conteudo')
    @php $p = $inscricao['payload'] ?? []; @endphp
    <h1>Inscrição confirmada</h1>
    <p class="lead">Guarde o protocolo. Use o CPF nas páginas de presença e certificado deste evento.</p>

    <section class="cartao">
        <h2><i class="fa fa-ticket"></i> Protocolo</h2>
        <p class="protocolo-grande">{{ $protocolo }}</p>
        <p class="meta-linha">Participante: <strong>{{ $inscricao['titulo'] }}</strong></p>
        <p class="meta-linha">Evento: <strong>{{ $p['evento'] ?? ($evento['titulo'] ?? '—') }}</strong></p>
        <p class="meta-linha">CPF: {{ $p['cpf'] ?? '—' }}</p>
        <p class="meta-linha">Status: <span class="badge ok">{{ $p['status'] ?? 'confirmada' }}</span></p>
        <div class="acoes no-print">
            <button type="button" class="botao claro" onclick="window.print()"><i class="fa fa-print"></i> Imprimir</button>
            @if($evento)
                @php
                    $urlInsc = \iEducar\Packages\BuritiEventosFormacao\Support\EventoPublico::urlInscricao($evento);
                    $urlPres = \iEducar\Packages\BuritiEventosFormacao\Support\EventoPublico::urlPresenca($evento);
                    $urlCert = \iEducar\Packages\BuritiEventosFormacao\Support\EventoPublico::urlCertificado($evento);
                @endphp
                @if($urlPres)
                    <a class="botao" href="{{ $urlPres }}"><i class="fa fa-check-square-o"></i> Marcar presença</a>
                @endif
                @if($urlCert && (($evento['payload']['certificado'] ?? true)))
                    <a class="botao claro" href="{{ $urlCert }}"><i class="fa fa-certificate"></i> Certificado</a>
                @endif
                @if($urlInsc)
                    <a class="botao claro" href="{{ $urlInsc }}"><i class="fa fa-calendar"></i> Voltar ao evento</a>
                @endif
            @endif
        </div>
    </section>
@endsection
