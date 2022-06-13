# Componente `<entity-owner>`
Mostra os termos da entidade,
  
## Propriedades
- **entity**: *Entity* - Entidade. Para saber como se obter o objeto entity ver a documentação dos componentes `<entity>` e `<entities>`;
- **title**: *String* (opcional) - Label do elemento;

### Importando componente
```PHP
<?php 
$this->import('entity-owner');
?>
```
### Exemplos de uso
```PHP
<!-- utilizaçao básica para listagem dos links -->
<entity-owner title="Publicado por" :entity="entity"></entity-links>
```
