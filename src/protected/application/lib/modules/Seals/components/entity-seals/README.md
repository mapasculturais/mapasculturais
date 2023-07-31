# Componente `<entity-seals>`
Componente de listagem e edição dos selos da entidade

### Eventos
- **namesDefined** - disparado quando o método `defineNames` é chamado, após a definição do `name` e `nomeCompleto`
  
## Propriedades
- *Entity **entity*** - Entidade
- *String **title*** - Título do componente
- *Boolean **editable** = false* - Modo de edição do componente
- *String/Array/Object **classes*** - Classes a serem aplicadas no componente

### Importando componente
```PHP
<?php 
$this->import('entity-seals');
?>
```

### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<entity-seals :entity="entity" title="Selos da entidade"></entity-seals>

<!-- utilizaçao mas telas de edição -->
<entity-seals :entity="entity" title="Editar selos da entidade" editable></entity-seals>

<!-- utilizaçao com classes personalizadas -->
<entity-seals :entity="entity" title="Selos da entidade" classes="col-12"></entity-seals>

<entity-seals :entity="entity" title="Selos da entidade" :classes="['col-12']"></entity-seals>
```