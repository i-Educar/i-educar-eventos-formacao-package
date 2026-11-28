@extends('eventos::portal.layout')
@section('titulo', 'Certificado')

@section('conteudo')
    <h1>Emitir / consultar certificado</h1>
    <p class="lead">{{ $evento['titulo'] }} · frequência mínima {{ $minimo }}%. Use o CPF da inscrição.</p>

    <section class="cartao">
        <h2><i class="fa fa-certificate"></i> Buscar</h2>
        <form method="post" action="{{ url('/eventos-formacao/portal/certificado/'.$token) }}" class="form-grid">
            @csrf
            <label class="col-wide">CPF do participante
                <input name="cpf" required maxlength="14" inputmode="numeric" placeholder="000.000.000-00" value="{{ old('cpf') }}">
            </label>
            <div class="acoes" style="grid-column:1/-1">
                <button class="botao" type="submit"><i class="fa fa-certificate"></i> Emitir ou ver certificado</button>
                <a class="botao claro" href="{{ url('/eventos-formacao/portal/presenca/'.$token) }}">Marcar presença</a>
                <a class="botao claro" href="{{ url('/eventos-formacao/portal/evento/'.$token) }}">Voltar ao evento</a>
            </div>
        </form>
    </section>
@endsection
