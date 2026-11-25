@extends('eventos::layout')
@section('titulo', 'Eventos')
@section('migalha', 'Eventos')
@section('icone-tela', 'fa-calendar')
@section('titulo-tela', 'Cadastro de eventos')
@section('lead')Período, local, carga horária, vagas e abertura de inscrição.@endsection

@section('conteudo')
<section class="bloco">
    <h2><i class="fa fa-plus"></i> Novo evento</h2>
    <form method="post" action="{{ url('/eventos-formacao/eventos') }}" class="form-grid">
        @csrf
        <label>Título <input name="titulo" required maxlength="180"></label>
        <label>Local <input name="local" maxlength="180"></label>
        <label>Início <input type="date" name="inicio"></label>
        <label>Fim <input type="date" name="fim"></label>
        <label>Inscrições a partir de <input type="date" name="inscricao_inicio"></label>
        <label>Carga horária <input type="number" step="0.5" min="0" name="carga_horaria"></label>
        <label>Carga EAD <input type="number" step="0.5" min="0" name="carga_ead"></label>
        <label>Vagas <input type="number" min="0" name="vagas" value="30"></label>
        <label>Público
            <select name="publico">
                <option value="ambos">Servidores e externo</option>
                <option value="servidor">Só servidores</option>
                <option value="externo">Só externo</option>
            </select>
        </label>
        <label>Status
            <select name="status">
                <option value="rascunho">Rascunho</option>
                <option value="aberto">Aberto</option>
                <option value="encerrado">Encerrado</option>
            </select>
        </label>
        <label><input type="checkbox" name="certificado" value="1" checked> Emite certificado</label>
        <div class="acoes"><button class="botao" type="submit">Salvar evento</button></div>
    </form>
</section>

<section class="bloco">
    <h2><i class="fa fa-list"></i> Eventos cadastrados</h2>
    @if(($registros ?? []) === [])
        <p style="margin:0;color:var(--suave)">Nenhum evento ainda.</p>
    @else
        <table>
            <thead><tr><th>Título</th><th>Local</th><th>Período</th><th>Vagas</th><th>Status</th></tr></thead>
            <tbody>
            @foreach($registros as $r)
                @php $p = $r['payload']; @endphp
                <tr>
                    <td>{{ $r['titulo'] }}</td>
                    <td>{{ $p['local'] ?? '—' }}</td>
                    <td>{{ $p['inicio'] ?? '—' }} → {{ $p['fim'] ?? '—' }}</td>
                    <td>{{ $p['vagas'] ?? 0 }}</td>
                    <td><span class="badge {{ ($p['status'] ?? '') === 'aberto' ? 'ok' : 'warn' }}">{{ $p['status'] ?? '—' }}</span></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</section>
@endsection
