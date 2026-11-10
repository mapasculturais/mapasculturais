# Componente `<search-filter-space>`
filtro da página de busca dos espaços
  
## Propriedades
- *String **position*** - Posição onde o filtro será implementado (list, map)
- *API **api*** - api

### Importando componente
```PHP
<?php 
$this->import('search-filter-space');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<search-filter-space></search-filter-space>
```
## Filtro territorial (estado/cidade)
Quando `$MAPAS.config.statesAndCitiesEnable` é `true` (dataset de estados/cidades publicado pelo componente `search` — ADR 0019), exibe `<mc-states-and-cities>` ligado a `pseudoQuery['En_Estado']`/`pseudoQuery['En_Municipio']`. As props `hide-states`/`hide-cities`/`locked-states` derivam de `$MAPAS.config.searchTerritorialFilters` (`search.filters.states`/`search.filters.cities` da instalação).
