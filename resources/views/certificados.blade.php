@extends('eventos::layout')
@section('titulo', 'Certificados')
@section('migalha', 'Certificados')
@section('icone-tela', 'fa-certificate')
@section('titulo-tela', 'Certificados')
@section('lead')Emissão liberada quando a frequência atingir o mínimo ({{ $minimo ?? 75 }}%).@endsection

@section('conteudo')
<section class="bloco">
    <h2><i class="fa fa-certificate"></i> Emitir</h2>
    <form method="post" action="{{ url('/eventos-formacao/certificados') }}" class="form-grid">
        @csrf
        <label class="col-wide">Presença elegível
            <select name="presenca_id" required>
                <option value="">Selecione…</option>
                @foreach($presencas ?? [] as $p)
                    @php $pct = (float) ($p['payload']['percentual'] ?? 0); @endphp
                    <option value="{{ $p['id'] }}" @if($pct < ($minimo ?? 75)) disabled @endif>
                        {{ $p['titulo'] }} — {{ $p['payload']['evento'] ?? '' }} ({{ $pct }}%)
                    </option>
                @endforeach
            </select>
        </label>
        <div class="acoes"><button class="botao" type="submit">Emitir certificado</button></div>
    </form>
</section>

<section class="bloco">
    <h2><i class="fa fa-list"></i> Emitidos</h2>
    @if(($registros ?? []) === [])
        <p style="margin:0;color:var(--suave)">Nenhum certificado emitido.</p>
    @else
        <table>
            <thead><tr><th>Participante</th><th>Evento</th><th>Código</th><th>Frequência</th></tr></thead>
            <tbody>
            @foreach($registros as $r)
                @php $p = $r['payload']; @endphp
                <tr>
                    <td>{{ $r['titulo'] }}</td>
                    <td>{{ $p['evento'] ?? '—' }}</td>
                    <td><code>{{ $p['codigo'] ?? $r['meta'] }}</code></td>
                    <td>{{ $p['percentual'] ?? 0 }}%</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</section>
@endsection
