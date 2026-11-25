@extends('eventos::layout')
@section('titulo', 'Presença')
@section('migalha', 'Presença')
@section('icone-tela', 'fa-check-square-o')
@section('titulo-tela', 'Frequência')
@section('lead')Lance o comparecimento por participante para liberar certificado.@endsection

@section('conteudo')
<section class="bloco">
    <h2><i class="fa fa-plus"></i> Lançar presença</h2>
    <form method="post" action="{{ url('/eventos-formacao/presenca') }}" class="form-grid">
        @csrf
        <label class="col-wide">Inscrição
            <select name="inscricao_id" required>
                <option value="">Selecione…</option>
                @foreach($inscricoes ?? [] as $i)
                    <option value="{{ $i['id'] }}">{{ $i['titulo'] }} — {{ $i['payload']['evento'] ?? '' }}</option>
                @endforeach
            </select>
        </label>
        <label>Frequência (%) <input type="number" name="percentual" min="0" max="100" step="1" value="100" required></label>
        <label>Situação
            <select name="situacao">
                <option value="presente">Presente</option>
                <option value="parcial">Parcial</option>
                <option value="ausente">Ausente</option>
            </select>
        </label>
        <div class="acoes"><button class="botao" type="submit">Salvar</button></div>
    </form>
</section>

<section class="bloco">
    <h2><i class="fa fa-list"></i> Lançamentos</h2>
    @if(($registros ?? []) === [])
        <p style="margin:0;color:var(--suave)">Nenhuma presença lançada.</p>
    @else
        <table>
            <thead><tr><th>Participante</th><th>Evento</th><th>%</th><th>Situação</th></tr></thead>
            <tbody>
            @foreach($registros as $r)
                @php $p = $r['payload']; @endphp
                <tr>
                    <td>{{ $r['titulo'] }}</td>
                    <td>{{ $p['evento'] ?? '—' }}</td>
                    <td>{{ $p['percentual'] ?? 0 }}%</td>
                    <td>{{ $p['situacao'] ?? '—' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</section>
@endsection
