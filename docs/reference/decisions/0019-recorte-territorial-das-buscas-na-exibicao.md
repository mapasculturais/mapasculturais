# 0019. Recorte territorial das buscas por variáveis de ambiente aplicado na exibição (merge no fetch), não no servidor

**Status:** Current
**Round:** R05 (épica #99, issues #113/#114; RF-B7 do PRD)

## Contexto

A R05 unifica os filtros de Estado/Município nas cinco buscas públicas (agente, espaço,
evento, projeto, oportunidade) e introduz `SEARCH_FILTER_STATES`/`SEARCH_FILTER_CITIES`
para que a instalação recorte os resultados por região de atuação — inclusive sem exibir
os filtros ao usuário (matriz de comportamento no briefing
`docs/rounds/R05-2026-10-filtros-territoriais-buscas/briefing.md`). A pergunta de
arquitetura: onde o recorte forçado é aplicado?

Os caminhos candidatos eram conhecidos: (a) o frontend, que monta a query da API a partir
da `pseudoQuery` (`Utils.parsePseudoQuery`,
`src/modules/Components/assets/js/components-base/Utils.js:171-245`) nos componentes
`search-list` / `search-map` / `search-list-event`; (b) o servidor, via hook
`ApiQuery({Classe}).params` (`src/core/ApiQuery.php:590`) que injetaria
`En_Estado = IIN(...)` em toda consulta da classe.

A opção (b) foi medida e rejeitada: o hook dispara para **todo** ApiQuery da classe —
painel do gestor, tabelas de admin, singles (listas relacionadas), exports e jobs — e
distinguir "busca pública" exigiria inspeção de rota dentro do hook, frágil por construção.
Além disso, o fluxo de eventos não passa por um ApiQuery único: `findOccurrences` faz
correlação manual de chaves `space:` (`src/core/Controllers/Event.php:196-230`) e
`findByEvents` idem no controller de Space (`src/core/Controllers/Space.php:140-184`) —
uma restrição server-side genérica não cobriria esses caminhos sem tocar os dois
controllers de qualquer forma.

## Decisão

1. **O recorte territorial é de exibição/descoberta, aplicado no frontend**: as
   configurações `search.filters.states` / `search.filters.cities` (env
   `SEARCH_FILTER_STATES` / `SEARCH_FILTER_CITIES`) são parseadas no servidor, publicadas
   no jsObject das cinco telas de busca e aplicadas como filtro adicional no **merge
   pós-`parsePseudoQuery`** dos componentes de listagem/mapa — intersectando com a
   escolha do usuário quando o filtro está visível.
2. **A API pública permanece irrestrita**: `/api/{entidade}/find?En_Estado=...` responde
   qualquer território para quem chamar diretamente. Isso é deliberado — os dados já são
   públicos; o recorte governa o catálogo exibido, não a confidencialidade. Se um dia
   houver requisito de isolamento real por território, ele será uma decisão nova
   (server-side), não uma extensão desta.
3. O recorte **não** entra na `pseudoQuery` como valor default de campo: `clearFilters()`
   zera todas as chaves (`search-filter-event/script.js:176-190`), o que destruiria a
   restrição — por isso o merge acontece depois do parse, fora do alcance do "Limpar
   todos os filtros".
4. Evento segue a exceção já vigente: filtra pelo endereço **do espaço** (chaves
   `space:En_Estado`/`space:En_Municipio`), resolvido pela correlação manual dos
   controllers de evento/espaço — que a R05 estende ao `findByEvents` (mapa), corrigindo
   a quebra latente em que `space:En_Estado` caía em `PropertyDoesNotExists`
   (`src/core/ApiQuery.php:3770-3771`).

## Alternativas consideradas

- **Restrição server-side via `ApiQuery(*).params`**: descartada — raio de explosão em
  todo consumidor da classe (painel/admin/exports/jobs) e não-cobertura dos fluxos de
  evento sem mudança de controller.
- **Valor default na pseudoQuery/initial-pseudo-query**: descartado — removível pelo
  "Limpar todos os filtros" e confunde "escolha do usuário" com "política da instalação".

## Consequências

**Positivas:** (+) matriz de comportamento implementável sem tocar o core de consulta;
(+) recorte sobrevive a limpar filtros e a campos ocultos; (+) mesmo mecanismo nas 5
buscas, com a exceção de evento confinada ao prefixo `space:`; (+) retrocompatível —
instalação sem as variáveis tem comportamento idêntico ao atual.

**Negativas:** (−) contornável por quem chama a API diretamente (aceito e declarado —
ver Decisão 2); (−) duplicação conceitual: existem agora dois recortes territoriais
independentes — este (exibição) e os filtros de subsite (`filtro_*_meta_En_*`,
`src/core/Entities/Subsite.php:259-310`) — que convivem sem se tocar por decisão de
escopo da R05; (−) entidades com endereço internacional (`En_Pais ≠ BR`) não casam com
recortes por UF/município e ficam fora de buscas recortadas.

**Relações:** apoia-se no ADR 0004 (a DSL ApiQuery — `IIN` case/accent-insensitive — é o
mecanismo de filtro subjacente; o `space:` de evento é a mini-DSL própria que o 0004
registra como exceção) e no ADR 0018 (a R04 criou os metadados `En_*` e a geolocalização
em projeto/oportunidade que esta rodada filtra).
