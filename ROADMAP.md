# ROADMAP — Gestão de Eventos / Formação Continuada

> Pacote: `buriti/i-educar-eventos-formacao-package` · TR **5.12** · Prioridade **P0** · Nota **D**  
> Alinhado a `tmp/DOC-EXECUTIVO-PE-77-2026-LAGUNA-IEDUCAR.md`

## Visão

Inscrições, presença, certificado, QR/credencial, grupos e vagas para formação continuada.

## Fases

### F0 · Escopo

Tipos de evento exigidos no TR.

### F1 · MVP

Evento, inscrição, presença, certificado PDF.

### F2 · QR/credencial

Check-in e crachá.

### F3 · Grupos/vagas

Regras de público e lotação.

### F4 · PoC

Demo completa.

## Cobertura 100% dos requisitos (TR)

Fonte: [`REQUISITOS-TR.md`](./REQUISITOS-TR.md) — **38 itens**.

Cada lote deve sair da PoC como **atende** ou entrar no plano de 30 dias.

### Lote R1 — itens 5.12.1 … 5.12.17 (15)

- `5.12.1`, `5.12.2`, `5.12.3`, `5.12.4`, `5.12.5`, `5.12.6`, `5.12.7`, `5.12.8`, `5.12.9`, `5.12.10`, `5.12.12`, `5.12.13`, `5.12.15`, `5.12.16`, `5.12.17`

- [ ] Implementar / validar / evidenciar na matriz PoC

### Lote R2 — itens 5.12.18 … 5.12.32 (15)

- `5.12.18`, `5.12.19`, `5.12.20`, `5.12.21`, `5.12.22`, `5.12.23`, `5.12.24`, `5.12.25`, `5.12.26`, `5.12.27`, `5.12.28`, `5.12.29`, `5.12.30`, `5.12.31`, `5.12.32`

- [ ] Implementar / validar / evidenciar na matriz PoC

### Lote R3 — itens 5.12.33 … 5.12.40 (8)

- `5.12.33`, `5.12.34`, `5.12.35`, `5.12.36`, `5.12.37`, `5.12.38`, `5.12.39`, `5.12.40`

- [ ] Implementar / validar / evidenciar na matriz PoC

### Gate 100%

- [ ] Todos os lotes R* com status atende ou gap≤30dias
- [ ] Soma atende ≥ 85% na demonstração

## Conformidade Laravel / pacote i-Educar 2.12

Antes da fase de código:

1. Criar `composer.json` com `php >=8.3`, `illuminate/support`, provider em `extra.laravel`, `compatible: ["2.12"]`.
2. Scaffold: `src/Providers`, `Http/Controllers`, `Services`, `Support`, `database/migrations`, `routes/web.php`, views Blade.
3. Schema `eventos_formacao` (ou estratégia documentada); FKs lógicas para núcleo (`ref_cod_aluno`, `ref_cod_escola`, etc.).
4. Menu/processos em migration própria; respeitar multi-tenant.
5. Não acoplar UI mobile; dados para lojas só via ``buriti/i-educar-apps-api-package``.
6. Seguir `packages/buriti/PADRAO-PACOTE-2.12.md`.

## Dependências

- Cadastro mestre de aluno/escola/turma no i-Educar (SGE).
- Matriz item a item OCR do Anexo I antes de cravar %.
- Alinhamento com `i-educar-apps-api-package` (APIs 5.13/5.14; UI/lojas em equipe externa) e exports (4.x) quando o módulo expõe dados ao cidadão/SME.

## Riscos

- Valor global do edital incompatível com greenfield total — reuso e MVP demonstrável são obrigatórios.
- PDF escaneado: numerações OCR podem exigir reconciliação manual.
- PoC só conta o que estiver operacional no dia.

## Definição de pronto da fase PoC

Checklist do `TODO.md` marcado para a fatia MVP + roteiro de demonstração ensaiado.
