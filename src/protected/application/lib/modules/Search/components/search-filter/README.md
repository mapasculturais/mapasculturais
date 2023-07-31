# Componente `<search-filter>`
Componente base para os filtros das entidades
  
## Propriedades
- *String **position*** - Posição onde o filtro será implementado (list, map, mobile)
- *Object **pseudoQuery*** - Query para filtragem dos resultados

## Slots
- **default** - Para o uso das tabs
- **filter** - Para o uso dos filtros

### Importando componente
```PHP
<?php 
$this->import('search-filter');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<search-filter></search-filter>
```