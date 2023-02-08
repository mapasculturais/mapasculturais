# Componente `<mc-input-datepicker-wrapper>`
Adiciona um campo de input de data

## Propriedades
- *Entity **entity** = null* - Entidade
- *String **fieldType*** - Tipos date | datetime | time
- *String **prop*** - Propriedade da entidade
### Importando componente
```PHP
<?php 
$this->import('mc-input-datepicker-wrapper');
?>
```
### Exemplos de uso
```HTML
<mc-input-datepicker-wrapper :entity="entity" :prop="prop" field-type="date"></mc-input-datepicker-wrapper>
```