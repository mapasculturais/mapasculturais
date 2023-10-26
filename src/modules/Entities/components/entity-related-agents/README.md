# Componente `<entity-related-agents>`
Componente de listagem e edição dos grupos de agentes relacionados à entidade.

## Propriedades
- *Entity **entity*** - Entidade
- *Boolean **editable** = false* - Modo de edição do componente
- *String/Array/Object **classes*** - Classes a serem aplicadas no componente

### Importando componente
```PHP
<?php 
$this->import('entity-related-agents');
?>
```

### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<entity-related-agents :entity="entity"></entity-related-agents>

<!-- utilização para edição -->
<entity-related-agents :entity="entity" editable></entity-related-agents>

<!-- utilizaçao com classes personalizadas -->
<entity-related-agents :entity="entity" classes="col-12"></entity-related-agents>

<entity-related-agents :entity="entity" classes="['col-12']"></entity-related-agents>
```