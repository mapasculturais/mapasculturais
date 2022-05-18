# Componente `<entity-links>`
Mostra os termos da entidade,
  
## Propriedades
- **entity**: *Entity* - Entidade com a propriedade `metalists.links` carregada. Para saber como se obter o objeto entity ver a documentação dos componentes `<entity>` e `<entities>`;
- **title**: *String* (opcional) - Label do elemento;

### Importando componente
```PHP
<?php 
$this->import('entity-links');
?>
```
### Exemplos de uso
```PHP
<!-- utilizaçao básica para listagem dos links -->
<entity-links title="Links" :entity="entity"> </entity-links>
```
