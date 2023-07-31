# Componente `<entity-terms>`
Componente para exibição e edição dos termos das taxonomias de uma entidade. 
  
## Propriedades
- *Entity **entity*** - Entidade
- *String **title*** - Título do componente
- *String **taxonomy*** - Taxonomia
- *Boolean **editable** = false* - Modo de edição do componente
- *String/Array/Object **classes*** - Classes a serem aplicadas no componente

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

<!-- utilizaçao básica para listagem das áreas de atuação -->
<entity-terms :entity="entity" taxonomy="area" ></entity-terms>

<!-- utilizaçao básica para edição das áreas de atuação -->
<entity-terms :entity="entity" taxonomy="area" editable></entity-terms>

<!-- utilizaçao com classes personalizadas para listagem das áreas de atuação -->
<entity-terms :entity="entity" taxonomy="area" classes="col-12"></entity-terms>

<entity-terms :entity="entity" taxonomy="area" :classes="['col-12']"></entity-terms>
```
