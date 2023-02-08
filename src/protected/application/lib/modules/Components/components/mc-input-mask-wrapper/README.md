# Componente `<mc-input-mask-wrapper>`
Adiciona um campo de input com máscara

## Propriedades
- *Entity **entity** = null* - Entidade
- *Bollean **mask** = false* - Se deve exibir a mascara

### Importando componente
```PHP
<?php 
$this->import('mc-input-mask-wrapper');
?>
```
### Exemplos de uso
```HTML
<mc-input-mask-wrapper v-if="is('string') && mask" v-model="entity[prop]" :type-mask="prop"></mc-input-mask-wrapper>
```