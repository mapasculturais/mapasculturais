# Componente `<entity-header>`
Componente do header da entidade

## Propriedades
- *Entity **entity*** - Entidade
- *Boolean **editable** = false* - Modo de edição do componente

## Slots
- **metadata**: informações adicionadas abaixo do nome da entidade (Ex.: tipo da entidade)
- **description**: Àrea da descrição curta da entidade

### Importando componente
```PHP
<?php 
$this->import('entity-header');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<entity-header :entity="entity"></entity-header>

<!-- utilizaçao nas telas de edição -->
<entity-header :entity="entity" editable></entity-header>
```