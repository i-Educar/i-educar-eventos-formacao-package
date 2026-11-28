@extends('eventos::portal.layout')
@section('titulo', 'Marcar presença')

@section('conteudo')
    @php $p = $evento['payload'] ?? []; @endphp
    <h1>Marcar presença</h1>
    <p class="lead">{{ $evento['titulo'] }} · {{ $p['local'] ?? '' }} · informe o CPF da inscrição.</p>

    <section class="cartao">
        <h2><i class="fa fa-check-square-o"></i> Check-in</h2>
        <form method="post" action="{{ url('/eventos-formacao/portal/presenca/'.$token) }}" class="form-grid">
            @csrf
            <label>CPF do inscrito
                <input name="cpf" required maxlength="14" inputmode="numeric" placeholder="000.000.000-00" value="{{ old('cpf') }}">
            </label>
            <label>Situação
                <select name="situacao">
                    <option value="presente" selected>Presente</option>
                    <option value="parcial">Parcial</option>
                    <option value="ausente">Ausente</option>
                </select>
            </label>
            <div class="acoes" style="grid-column:1/-1">
                <button class="botao" type="submit"><i class="fa fa-check"></i> Registrar presença</button>
                <a class="botao claro" href="{{ url('/eventos-formacao/portal/evento/'.$token) }}">Voltar ao evento</a>
            </div>
        </form>
    </section>
@endsection
