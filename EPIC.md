# EPIC — Gestão de Eventos / Formação Continuada

| Campo | Valor |
| --- | --- |
| Pacote | `buriti/i-educar-eventos-formacao-package` |
| TR Laguna | **5.12** |
| Prioridade | **P0** |
| Nota atual (executivo) | **D** |
| Fonte | `tmp/DOC-EXECUTIVO-PE-77-2026-LAGUNA-IEDUCAR.md` |
| Regra de ouro | Pregão Eletrônico 77/2026 · Laguna/SC · PoC ≥ 85% por módulo · gaps ≤ 30 dias |
| Base atual | Inexistente como produto |

## Objetivo

Inscrições, presença, certificado, QR/credencial, grupos e vagas para formação continuada.

## Padrão técnico (i-Educar 2.12)

| Item | Valor |
| --- | --- |
| Alvo | i-Educar **2.12** · PHP **≥ 8.3** · Illuminate **^11|^12|^13** (Laravel 13 no app) |
| Composer | `buriti/i-educar-eventos-formacao-package` |
| Namespace | `iEducar\Packages\BuritiEventosFormacao\` → `src/` |
| Compat | `extra.ieducar.compatible: ["2.12"]` · GPL-2.0-or-later · `type: library` |
| Schema / dados | eventos_formacao |
| Rotas | prefixo `eventos-formacao`; SME com middleware `ieducar.*` + `auth`; API apps via Sanctum quando exposto |
| Tenancy | `PackageTenant::allows('buriti/i-educar-eventos-formacao-package')` antes de migrations/menu |
| Plug-and-play | path em `packages/buriti/…`; manter em `ignore` até instalável |
| Referência | `packages/buriti/PADRAO-PACOTE-2.12.md` |

**Nota de arquitetura:** Inscrição/presença/certificado; histórico pode espelhar no 5.1.

## Cobertura 100% do TR

Este EPIC cobre **todos** os requisitos do módulo(s) 5.12 listados em [`REQUISITOS-TR.md`](./REQUISITOS-TR.md) (38 itens OCR).

- PoC: ≥ **85%** demonstráveis e operacionais.
- Até **15%** restante: produção em ≤ **30 dias**, sem ônus.
- Nenhum item do `REQUISITOS-TR.md` pode ficar fora do `ROADMAP.md` / `TODO.md` (direto ou por grupo rastreável).

## Épicos filhos

1. Cadastro de eventos/turmas de formação com vagas e públicos.
2. Inscrição, lista de espera e credencial QR.
3. Controle de presença e emissão de certificado.
4. Relatórios para SME e histórico no servidor.

## Critérios de aceite (PoC)

- [ ] Checklist [`REQUISITOS-TR.md`](./REQUISITOS-TR.md) com **100%** dos itens classificados (atende / gap / N/A)
- [ ] Itens **atende** demonstráveis na PoC (≥ 85% do módulo 5.12)
- [ ] Itens **gap** ≤ 15% com plano ≤ 30 dias em produção, sem ônus
- [ ] Nenhum requisito do TR omitido da matriz


## Fora de escopo deste EPIC

- Orçamento comercial da proposta Laguna.
- Substituição do core i-Educar fora do domínio do módulo.
