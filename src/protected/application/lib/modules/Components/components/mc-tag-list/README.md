# Componente `<mc-tag-list>`
Componente para exibição e edição dos termos das taxonomias de uma entidade. 
  
## Propriedades
- *Boolean **editable** = false* - Habilita o modo de edição do componente;
- *entityType **String*** -Propriedade que seta a String com a Entidade
- *tags **Array*** - Array que lista as tags;

### Importando componente
```PHP
<?php 
$this->import('mc-tag-list');
?>
```
### Exemplos de uso
```PHP
<!-- utilizaçao básica para listagem das tags, exemplo na edição do evento.-->
<mc-tag-list entity-type="event" editable="true" :tags="entity.terms?.linguagem"></mc-tag-list>

```
