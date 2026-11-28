@php
    $portalOk = \iEducar\Packages\BuritiEventosFormacao\Support\EventoPublico::portalModuloHabilitado();
@endphp
<form method="post" action="{{ url('/eventos-formacao/eventos') }}" class="form-grid">
    @csrf
    <label class="col-wide">Título <input name="titulo" required maxlength="180"></label>
    <label>Local <input name="local" maxlength="180"></label>
    <label>Início <input type="date" name="inicio"></label>
    <label>Fim <input type="date" name="fim"></label>
    <label>Inscrições a partir de <input type="date" name="inscricao_inicio"></label>
    <label>Carga horária (h) <input type="number" step="0.5" min="0" name="carga_horaria"></label>
    <label>Carga EAD (h) <input type="number" step="0.5" min="0" name="carga_ead"></label>
    <label>Vagas <input type="number" min="0" name="vagas" value="30"></label>
    <label>Público-alvo
        <select name="publico">
            <option value="ambos">Servidores e externo</option>
            <option value="servidor">Só servidores</option>
            <option value="externo">Só externo</option>
        </select>
    </label>
    <label>Status
        <select name="status">
            <option value="rascunho">Rascunho</option>
            <option value="aberto" selected>Aberto para inscrição</option>
            <option value="encerrado">Encerrado</option>
        </select>
    </label>
    @if($portalOk)
        <label class="col-wide" style="display:flex;align-items:flex-start;gap:10px;font-weight:700;color:var(--tinta)">
            <input type="checkbox" name="inscricao_externa" value="1" checked style="margin-top:3px">
            <span>
                Publicar este evento no portal externo
                <small style="display:block;font-weight:500;color:var(--suave);margin-top:2px">
                    O acesso externo do módulo está habilitado em
                    <a href="{{ url('/configuracoes/modulos?modulo=eventos') }}">Configurações → Módulos</a>.
                </small>
            </span>
        </label>
    @else
        <p class="col-wide" style="margin:0;padding:10px 12px;border-radius:10px;background:#fff7ed;color:#9a3412;font-size:.86rem">
            Portal externo desligado para o módulo.
            Habilite em <a href="{{ url('/configuracoes/modulos?modulo=eventos') }}">Configurações → Módulos</a>
            (flag «Permitir acesso externo»).
        </p>
        <input type="hidden" name="inscricao_externa" value="0">
    @endif
    <label class="col-wide" style="display:flex;align-items:flex-start;gap:10px;font-weight:700;color:var(--tinta)">
        <input type="checkbox" name="certificado" value="1" checked style="margin-top:3px">
        <span>Emite certificado (frequência mínima definida na configuração do módulo)</span>
    </label>
    <div class="acoes"><button class="botao" type="submit">Salvar evento</button></div>
</form>
