# Componente `<stepper>`
Componente para listar um passo-a-passo

### Eventos
- 
  
## Propriedades
- *Number **totalSteps** obrigatório* - Total de steps
- *Number **stepped** = 1* - Step atual
- *String **actualLabel*** - Label do step atual
- *String **lastLabel*** - Label do último step
- *Boolean **small** = false* - Modifica o estilo do stepper

## Slots
- 

### Importando componente
```PHP
<?php 
$this->import('stepper');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<stepper :total-steps="5"></stepper>

<!-- Utilização com step definido -->
<stepper :total-steps="5" :stepped="2"></stepper>

<!-- Utilização com a versão small -->
<stepper :total-steps="5" small></stepper>

<!-- Utilização com labels -->
<stepper :total-steps="5" actual-label="step atual" last-label="último step"></stepper>

```