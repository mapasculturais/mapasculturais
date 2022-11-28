# Componente `<entity-parent>`
Componente para listar as entidades relacionadas
  
## Propriedades
- *Entity **entity*** - Entidade
- *String **title*** - Titulo do componente
- *String **type*** - Tipo das entiades relacionadas

### Importando componente
```PHP
<?php 
$this->import('entity-parent');
?>
```

### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<entity-parent :entity="entity" type="project"></entity-parent>

<!-- utilização nas telas de edição -->
<entity-parent :entity="entity" type="project" editable></entity-parent>

<!-- utilização com classes personalizadas -->
<entity-parent :entity="entity" classes="col-12" type="project" ></entity-parent>

<entity-parent :entity="entity" :classes="['col-12']" type="project" ></entity-parent>
```