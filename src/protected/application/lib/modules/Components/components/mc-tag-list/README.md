# Componente `<mc-tag-list>`
Componente para listagem de tags de uma entidade
  
## Propriedades
- *Boolean **editable** = false* - Habilita o modo de edição do componente;
- *classes **String*** - Define a classe da tag li;
- *tags **Array*** - Array que lista as tags;

### Importando componente
```PHP
<?php 
$this->import('mc-tag-list');
?>
```
### Exemplos de uso
```PHP
<!-- utilizaçao básica para listagem das tags-->
<mc-tag-list  editable :tags="pseudoQuery['term:area']"></mc-tag-list>

```
