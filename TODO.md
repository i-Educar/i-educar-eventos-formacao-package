# TODO — Gestão de Eventos / Formação Continuada

> Pacote: `buriti/i-educar-eventos-formacao-package` · TR **5.12** · Prioridade **P0**


## Onda 2 (código inicial 2026-11-24)

- [x] Services de domínio + testes
- [x] docs/onda-2/
- [ ] Migrations/UI completas

## Backlog Laguna

- [ ] Checklist 5.12.x
- [ ] CRUD eventos
- [ ] Inscrição online
- [ ] Presença/QR
- [ ] Certificado
- [ ] Relatórios
- [ ] Vínculo com servidor (5.1)
- [ ] Roteiro PoC

## Scaffold 2.12 (quando iniciar código)

- [ ] `composer.json` (`buriti/…`, PHP ≥8.3, illuminate/support, `compatible: ["2.12"]`)
- [ ] `ServiceProvider` + `const PACKAGE`
- [ ] `routes/web.php` com middleware i-Educar
- [ ] Migrations no schema do domínio + guard `PackageTenant`
- [ ] Processos/menu (faixa própria)
- [ ] README + INSTALL espelhando transporte/setup
- [ ] Entrada futura em `packages/plug-and-play.json` (fora do ignore)
- [ ] Revisar `packages/buriti/PADRAO-PACOTE-2.12.md`

## Cobertura 100% do TR

- [ ] Percorrer 100% de [`REQUISITOS-TR.md`](./REQUISITOS-TR.md) (38 itens) e marcar atende/gap/N/A

- [ ] Abrir tarefas para **cada gap** (ou agrupar com IDs explícitos)

- [ ] Evidências de PoC por item atende

- [ ] Lista dos ≤15% com datas ≤30 dias

### Lotes (mesmo do ROADMAP)

- [ ] Lote R1: 5.12.1 … 5.12.17

- [ ] Lote R2: 5.12.18 … 5.12.32

- [ ] Lote R3: 5.12.33 … 5.12.40

## Gate PoC

- [ ] `REQUISITOS-TR.md` 100% classificado
- [ ] ≥ 85% atende com evidência
- [ ] ≤ 15% gap com plano ≤ 30 dias
- [ ] Roteiro binário ensaiado

## Referências

- `tmp/DOC-EXECUTIVO-PE-77-2026-LAGUNA-IEDUCAR.md`
- OCR: `/tmp/edital-ocr/p-01.txt` … `p-79.txt` (quando disponível)
