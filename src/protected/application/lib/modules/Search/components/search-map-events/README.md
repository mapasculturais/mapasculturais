# Componente `<search-map-events>`
Exibe o mapa da busca de eventos

### Eventos
- **namesDefined** - disparado quando o método `defineNames` é chamado, após a definição do `name` e `nomeCompleto`
  
## Propriedades
- *Object **pseudoQuery*** - objeto pseudoQuery

### Importando componente
```PHP
<?php 
$this->import('search-map-events');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<search-map-events :pseudo-query="pseudoQuery"></search-map-events>

```