# Componente `<occurrence-card>`
Card de uma ocorrência de evento

## Propriedades
- *Object **occurrence*** - Objeto retornado pelo endpoint /api/event/occurrences

### Importando componente
```PHP
<?php 
$this->import('occurrence-card');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<occurrence-card :occurrence="occurrence"></occurrence-card>

```