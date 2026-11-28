@extends('eventos::portal.layout')
@section('titulo', 'Eventos abertos')

@section('conteudo')
    <h1>Eventos com inscrição aberta</h1>
    <p class="lead">Participe dos eventos liberados para o público externo. Depois use o mesmo portal para marcar presença e emitir certificado.</p>

    <section class="cartao">
        <h2><i class="fa fa-calendar"></i> Disponíveis agora</h2>
        @if(($eventos ?? []) === [])
            <p class="meta-linha" style="margin:0">Nenhum evento com inscrição externa aberta no momento.</p>
        @else
            <div class="lista-eventos">
                @foreach($eventos as $e)
                    @php
                        $p = $e['payload'];
                        $token = $p['publico_token'] ?? null;
                        $restantes = \iEducar\Packages\BuritiEventosFormacao\Support\EventoPublico::vagasRestantes($e);
                    @endphp
                    <article class="item-evento">
                        <div>
                            <h3>{{ $e['titulo'] }}</h3>
                            <p class="meta-linha">
                                {{ $p['local'] ?? 'Local a definir' }}
                                · {{ $p['inicio'] ?? '—' }} → {{ $p['fim'] ?? '—' }}
                                @if($restantes !== null)
                                    · {{ $restantes }} vaga(s)
                                @endif
                            </p>
                        </div>
                        @if($token)
                            <a class="botao" href="{{ url('/eventos-formacao/portal/evento/'.$token) }}">
                                <i class="fa fa-ticket"></i> Inscrever-se
                            </a>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection
