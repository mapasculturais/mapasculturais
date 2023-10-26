# Componente `<entity-field-datepicker>`
Adiciona um campo de input de data

## Propriedades
- *Entity **entity** = null* - Entidade
- *String **fieldType*** - Tipos date | datetime | time
- *String **prop*** - Propriedade da entidade
- *String **minDate*** - Propriedade para seleção mínima de data
- *String **maxDate*** - Propriedade para seleção máxima de data
### Importando componente
```PHP
<?php 
$this->import('entity-field-datepicker');
?>
```
### Exemplos de uso
```HTML
<entity-field-datepicker :entity="entity" :prop="prop" field-type="date"></entity-field-datepicker>

<entity-field-datepicker :entity="entity" :prop="prop" :min-date="2012-01-01" :max-date="2012-02-01" field-type="date"></entity-field-datepicker>
```