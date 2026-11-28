@extends('eventos::layout')
@section('titulo', 'Inscrições')
@section('migalha', 'Inscrições')
@section('icone-tela', 'fa-ticket')
@section('titulo-tela', 'Inscrições')
@section('lead')Inscrição manual (SME) ou via portal externo, com protocolo e controle de vagas.@endsection

@section('acoes-topo')
    <a class="botao claro icone" href="{{ url('/eventos-formacao/portal') }}" target="_blank" rel="noopener" title="Portal externo" aria-label="Portal externo"><i aria-hidden="true" class="fa fa-external-link"></i></a>
@endsection

@section('conteudo')
<section class="bloco">
    <h2><i class="fa fa-plus"></i> Inscrição interna</h2>
    <form method="post" action="{{ url('/eventos-formacao/inscricoes') }}" class="form-grid">
        @csrf
        <label>Evento
            <select name="evento_id" required>
                <option value="">Selecione…</option>
                @foreach($eventos ?? [] as $e)
                    <option value="{{ $e['id'] }}">{{ $e['titulo'] }} ({{ $e['payload']['vagas'] ?? 0 }} vagas)</option>
                @endforeach
            </select>
        </label>
        <label>Nome <input name="nome" required maxlength="180"></label>
        <label>CPF <input name="cpf" maxlength="14" placeholder="000.000.000-00"></label>
        <label>Tipo
            <select name="tipo">
                <option value="servidor">Servidor</option>
                <option value="externo">Externo</option>
            </select>
        </label>
        <div class="acoes"><button class="botao" type="submit">Inscrever</button></div>
    </form>
</section>

<section class="bloco">
    <h2><i class="fa fa-list"></i> Inscrições</h2>
    @if(($registros ?? []) === [])
        <p style="margin:0;color:var(--suave)">Nenhuma inscrição ainda.</p>
    @else
        <div style="overflow-x:auto">
            <table>
                <thead><tr><th>Protocolo</th><th>Participante</th><th>Evento</th><th>Tipo</th><th>Origem</th><th>Status</th></tr></thead>
                <tbody>
                @foreach($registros as $r)
                    @php $p = $r['payload']; @endphp
                    <tr>
                        <td><code>{{ $p['protocolo'] ?? $r['meta'] ?? '—' }}</code></td>
                        <td>{{ $r['titulo'] }}</td>
                        <td>{{ $p['evento'] ?? '—' }}</td>
                        <td>{{ $p['tipo'] ?? '—' }}</td>
                        <td>{{ $p['origem'] ?? '—' }}</td>
                        <td><span class="badge ok">{{ $p['status'] ?? '—' }}</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
@endsection
