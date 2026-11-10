# Briefing — R04: endereço em Projeto e Oportunidade

> RECORD da rodada R04 (épica #99). Aprovado explicitamente pelo humano em 2026-10-08. Selado no fechamento da rodada; correções posteriores entram como adendo datado.

## Problema

Hoje só Agente e Espaço registram endereço completo (brasileiro ou internacional); Projeto e Oportunidade não têm endereço, então não dá para situá-los geograficamente — nem para exibição pública, nem como base para filtro territorial nas buscas (a rodada seguinte depende destes dados existirem).

## Para quem / contexto

Quem cadastra e edita projetos e oportunidades (gestores e proponentes) passa a informar o endereço no mesmo padrão que já conhece de Agente e Espaço; o visitante do catálogo passa a ver o endereço na página pública da entidade.

**O endereço é sempre opcional** em Projeto e Oportunidade — nenhum fluxo passa a exigi-lo (nem publicação), seguindo o comportamento de Agente/Espaço.

## Restrições

- Reutilizar o formulário `country-address-form` existente, sem criar formulário novo (brasileiro via `brasil-address-form`, internacional via `international-address-form`).
- O formulário entra **na mesma seção** das telas de edição e de visualização pública, no lugar relativo equivalente ao de Agente/Espaço; exibição pública seguindo o padrão vivo das singles BaseV2 (corrigido — ver adendo 2026-10-08).
- Nada de endereço próprio em Evento — evento continua usando o endereço do espaço.

## Fora de escopo da R04 (explícito)

- Filtros de Estado/Município nas cinco buscas e as variáveis `SEARCH_FILTER_STATES`/`SEARCH_FILTER_CITIES` — rodada seguinte.
- Unificação da variável antiga `EVENTS_FILTER_STATES_AND_CITIES` — decisão já tomada (unificar), execução na rodada seguinte.
- Endereço em Evento.
- Alterações no filtro admin da aba Tabelas de agentes.

## Âncora no estado atual (verificado em 2026-10-08)

- Metadados `En_*` registrados em `src/conf/agent-types.php` e `src/conf/space-types.php`; ausentes em `src/conf/project-types.php` e `src/conf/opportunity-types.php`.
- Componentes em `src/modules/CountryLocalizations/components/` (`country-address-form`, `brasil-address-form`, `international-address-form`, `*-address-view`) prontos para reuso.
- Uso atual na edição: `src/modules/Entities/views/agent/edit-1.php`, `edit-2.php` e `src/modules/Entities/views/space/edit.php`.

## Decisões tomadas na descoberta (valem para a próxima rodada)

Registradas na épica #99 (comentário de 2026-10-08): recorte da R04 (só endereço), unificação da variável antiga de Eventos, filtros valendo para lista **e** mapa.

## Adendo (2026-10-08)

Correção da referência de exibição pública: o componente `entity-location` **não é usado em nenhuma view** (componente morto). A exibição pública do endereço em Projeto/Oportunidade segue o **padrão vivo das singles BaseV2** — o bloco de endereço das singles (ver `src/modules/Entities/views/space/single.php`) ou o componente `country-address-view`. O item de Restrições que citava `entity-location` fica corrigido por este adendo. Origem: avaliação de viabilidade da épica #99 (issue #102).
