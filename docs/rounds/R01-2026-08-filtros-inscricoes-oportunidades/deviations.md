# Deviations of round R01 — filtros de inscrições na listagem de oportunidades

> Rodada Minimal · issue única #25 · PR upstream mapasculturais/mapasculturais#3687 (merged 2026-08-27, merge commit 90304f85b9a60a8cc915b1b30429a611e1bc1aa4).

## Deviation 1 — Canal de instrumentação do plugin indisponível (fallback REST)
- **Planned:** eventos A–F e registro de override emitidos exclusivamente via `maestra_emit_event` (canal único do contrato de auditoria).
- **Implemented:** 4 comentários de evento/override da triagem + 2 do fechamento postados via REST (curl + token keyring) com corpos **verbatim** do contrato (`reference/instrumentation.md`), inclusive assinatura "— facilitator".
- **Reason:** o `gh` CLI da estação falha ao resolver `api.github.com` (resolvedor Go consulta o DNS do roteador 192.168.15.1 → NXDOMAIN; `curl`/`getent` resolvem via systemd-resolved). O emissor do plugin spawna `gh` — canal mecanicamente indisponível; o estado "override sem registro" é o único proibido, então o formato-verbatim via canal funcional prevaleceu, com anúncio transparente na sessão.
- **Decision registered at:** comentários de evento na #25 (ex.: https://github.com/hacklabr/mapasculturais/issues/25#issuecomment-5429938136 e https://github.com/hacklabr/mapasculturais/issues/25#issuecomment-5440501442).
- **Reference document updated:** sem documento de referência de produto afetado (falha de ambiente, não de comportamento); evidência do formato preservado = os próprios comentários acima; correção de ambiente sugerida na `retro.md` desta rodada.

## Deviation 2 — Critério de aceitação 4 aceito com override (suíte completa)
- **Planned:** `bash tests/run.sh` 100% verde como critério de aceitação da #25.
- **Implemented:** testes da correção verdes (3 tests, 56 assertions — `OpportunityApiTest::testFiltrosDePeriodoDeInscricaoPorTimestampCompleto`); suíte completa com 21 erros **pré-existentes** em `RoutesTest` (asset CSS `@vuepic/vue-datepicker/dist/main.css` ausente com `BUILD_ASSETS=0`), anteriores à mudança e sem relação com ela.
- **Reason:** "perfeito tudo testado e funcionando... PR no upstream ja aceito" (palavras do decisor humano, após validação completa em ambiente dev com os 4 casos de teste).
- **Decision registered at:** https://github.com/hacklabr/mapasculturais/issues/25#issuecomment-5440501442 (override register, tipo gate) + evento D https://github.com/hacklabr/mapasculturais/issues/25#issuecomment-5440503312.
- **Reference document updated:** veredito de aceitação por critério em https://github.com/hacklabr/mapasculturais/issues/25#issuecomment-5440504660 (estado dos critérios documentado com evidência).

## Deviation 3 — Arquivo adicional no commit da correção
- **Planned:** staging de 4 arquivos (2 `script.js` + teste + `CHANGELOG.md`), conforme plano de execução aprovado.
- **Implemented:** 5 arquivos — acrescido `tests/src/Builders/PhasePeriods/RegistrationWindow.php` (período absoluto reutilizável).
- **Reason:** "o teste de regressão depende dele. Sem esse arquivo o teste não roda" (declaração do implementador no ato, aceita pelo facilitador — o plano de testes do diagnóstico já antecipava suporte a datas absolutas no builder).
- **Decision registered at:** declarada no ato da execução (relatório do implementador, sessão de 27/08/2026); consolidada neste registro.
- **Reference document updated:** diff completo no PR https://github.com/mapasculturais/mapasculturais/pull/3687/files (evidência do que entrou); nenhum documento de referência descreve inventário de builders de teste (verificado — sem doc-bug).
