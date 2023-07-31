# Componente `<entity-owner>`
Mostra os termos da entidade,
  
## Propriedades
- *Entity **entity*** - Entidade
- *String **title*** - Titulo do componente
- *Boolean **editable** = false* - Modo de edição do componente
- *String/Array/Object **classes*** - Classes a serem aplicadas no componente

### Importando componente
```PHP
<?php 
$this->import('entity-owner');
?>
```

### Exemplos de uso
```PHP
<!-- utilizaçao básica -->
<entity-owner :entity="entity" title="Publicado por"></entity-links>

<!-- utilizaçao nas telas de edição -->
<entity-owner :entity="entity" title="Publicado por" editable></entity-links>

<!-- utilizaçao com classes personalizadas -->
<entity-owner :entity="entity" title="Publicado por" classes="col-12 sm:col-6"><entity-owner>

<entity-owner :entity="entity" title="Publicado por" classes="['col-12', 'sm:col-6']"><entity-owner>
```
