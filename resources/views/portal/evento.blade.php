@extends('eventos::portal.layout')
@section('titulo', $evento['titulo'] ?? 'Evento')

@section('conteudo')
    @php $p = $evento['payload'] ?? []; @endphp
    <h1>{{ $evento['titulo'] }}</h1>
    <p class="lead">
        {{ $p['local'] ?? 'Local a definir' }}
        · {{ $p['inicio'] ?? '—' }} → {{ $p['fim'] ?? '—' }}
        @if(!empty($p['carga_horaria'])) · CH {{ $p['carga_horaria'] }}h @endif
    </p>

    <div class="acoes" style="margin-top:0;margin-bottom:16px">
        @if(!empty($urlPresenca))
            <a class="botao claro" href="{{ $urlPresenca }}"><i class="fa fa-check-square-o"></i> Marcar presença</a>
        @endif
        @if(!empty($urlCertificado) && ($p['certificado'] ?? true))
            <a class="botao claro" href="{{ $urlCertificado }}"><i class="fa fa-certificate"></i> Certificado</a>
        @endif
    </div>

    <section class="cartao">
        <h2><i class="fa fa-info-circle"></i> Sobre o evento</h2>
        <p class="meta-linha">Status: <span class="badge {{ ($p['status'] ?? '') === 'aberto' ? 'ok' : 'warn' }}">{{ $p['status'] ?? '—' }}</span></p>
        <p class="meta-linha">Público: {{ $p['publico'] ?? 'ambos' }}</p>
        @if($vagasRestantes !== null)
            <p class="meta-linha">Vagas restantes: <strong>{{ $vagasRestantes }}</strong></p>
        @endif
        @if($p['certificado'] ?? true)
            <p class="meta-linha">Emite certificado (com frequência mínima).</p>
        @endif
    </section>

    @if($abertas)
        <section class="cartao">
            <h2><i class="fa fa-ticket"></i> Inscrição externa</h2>
            <form method="post" action="{{ url('/eventos-formacao/portal/evento/'.$token) }}" class="form-grid">
                @csrf
                <label class="col-wide">Nome completo
                    <input name="nome" required maxlength="180" value="{{ old('nome') }}">
                </label>
                <label>CPF
                    <input name="cpf" required maxlength="14" inputmode="numeric" placeholder="000.000.000-00" value="{{ old('cpf') }}">
                </label>
                <label>E-mail
                    <input type="email" name="email" maxlength="120" value="{{ old('email') }}">
                </label>
                <label>Telefone
                    <input name="telefone" maxlength="20" value="{{ old('telefone') }}">
                </label>
                <div class="acoes" style="grid-column:1/-1">
                    <button class="botao" type="submit"><i class="fa fa-check"></i> Confirmar inscrição</button>
                </div>
            </form>
        </section>
    @else
        <section class="cartao">
            <h2><i class="fa fa-lock"></i> Inscrições</h2>
            <p class="meta-linha" style="margin:0">As inscrições externas não estão abertas neste momento. Você ainda pode marcar presença ou buscar certificado, se já estiver inscrito.</p>
        </section>
    @endif
@endsection
