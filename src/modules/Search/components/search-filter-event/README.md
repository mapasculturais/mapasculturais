# Componente `<search-filter-event>`
filtro da página de busca de eventos
  
## Propriedades
- *String **position*** - Posição onde o filtro será implementado (list, map)
- *API **api*** - api

### Importando componente
```PHP
<?php 
$this->import('search-filter-event');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<search-filter-event></search-filter-event>
```
## Filtro territorial (estado/cidade)
Quando `$MAPAS.config.statesAndCitiesEnable` é `true` (dataset de estados/cidades publicado pelo componente `search` — ADR 0019), exibe `<mc-states-and-cities>` ligado a `pseudoQuery['space:En_Estado']`/`pseudoQuery['space:En_Municipio']` (o filtro aplica-se ao espaço da ocorrência, via `event/findOccurrences`). As props `hide-states`/`hide-cities`/`locked-states` derivam de `$MAPAS.config.searchTerritorialFilters` (`search.filters.states`/`search.filters.cities` da instalação). A flag legada `events.filter.statesAndCities` (config `searchFilterEvent.statesAndCitiesFilterEnabled`) foi removida em #118.
