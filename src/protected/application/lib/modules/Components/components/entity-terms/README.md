# Componente `<entity-terms>`
Mostra os termos da entidade,
  
## Propriedades
- **entity**: *Entity* - Entidade com a propriedade `terms` carregada. Para saber como se obter o objeto entity ver a documentação dos componentes `<entity>` e `<entities>`;
- **taxonomy**: *String* - Taxonomia do termo;
- **title**: *String* (opcional) - Label do elemento;

### Importando componente
```PHP
<?php 
$this->import('entity-terms');
?>
```
### Exemplos de uso
```PHP
<!-- utilizaçao básica para listagem das tags -->
<entity-terms :entity="entity" taxonomy="tag" title="Tags" ></entity-terms>

<!-- utilizaçao básica para listagem das areas -->
<entity-terms :entity="entity" taxonomy="area" title="Áreas de atuação" ></entity-terms>
```
