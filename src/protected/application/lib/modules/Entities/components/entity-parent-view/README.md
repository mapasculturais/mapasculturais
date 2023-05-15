# Componente `<entity-parent-view>`
Componente para listar as entidades relacionadas em modo visualização
  
## Propriedades
- *Entity **entity*** - Entidade

### Importando componente
```PHP
<?php 
$this->import('entity-parent-view');
?>
```

### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<entity-parent-view :entity="entity" type="project"></entity-parent-view>

<!-- utilização nas telas de edição -->
<entity-parent-view :entity="entity" type="project" editable></entity-parent-view>

<!-- utilização com classes personalizadas -->
<entity-parent-view :entity="entity" classes="col-12" type="project" ></entity-parent-view>

<entity-parent-view :entity="entity" :classes="['col-12']" type="project" ></entity-parent-view>
```