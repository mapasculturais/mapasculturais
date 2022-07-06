# Componente `<entity-related-agents>`
O componente `<entity-related-agents>` serve para listar e editar os grupos de agentes relacionados à entidade.

Este documento (README.md) deve conter a descrição do que o componente faz e toda a interface pública do componente.

## Propriedades
- **entity**: *Entity* (obrigatório) - Entidade 
- **editable**: *Boolean* (opcional) - Edição

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
<entity-related-agents :entity="entity" :editable="true"></entity-related-agents>

```