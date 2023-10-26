# Componente `<entity-parent-edit>`
Componente para listar as entidades relacionadas
  
## Propriedades
- *Entity **entity*** - Entidade
- *String **title*** - Titulo do componente
- *String **type*** - Tipo das entiades relacionadas

### Importando componente
```PHP
<?php 
$this->import('entity-parent-edit');
?>
```

### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<entity-parent-edit :entity="entity" type="project"></entity-parent-edit>

<!-- utilização com classes personalizadas -->
<entity-parent-edit :entity="entity" classes="col-12" type="project" ></entity-parent-edit>

<entity-parent-edit :entity="entity" :classes="['col-12']" type="project" ></entity-parent-edit>
```