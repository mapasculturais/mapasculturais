# Componente `<mc-stepper>`
Componente para listar um passo-a-passo

### Eventos
- 
  
## Propriedades
- *Array/Object **steps*** - Steps a serem listados
- *Number **actual-step** = 1* - Step atual
- *Boolean **only-active-label** = false* - Mostra apenas os labels dos steps ativos
- *Boolean **no-labels** = false* - Não lista nenhuma label 
- *Boolean **small** = false* - Modifica o estilo do stepper

### Importando componente
```PHP
<?php 
$this->import('mc-stepper');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<mc-stepper :steps="['step 1', 'step 2', 'step 3', ...]"></mc-stepper>

<!-- Utilização com step definido -->
<mc-stepper :steps="['step 1', 'step 2', 'step 3', ...]" :stepped="2"></mc-stepper>

<!-- Utilização com a versão small -->
<mc-stepper :steps="['step 1', 'step 2', 'step 3', ...]" small></mc-stepper>

<!-- Utilização apenas com labels ativas -->
<mc-stepper :steps="['step 1', 'step 2', 'step 3', ...]" only-active-label></mc-stepper>

<!-- Utilização sem labels -->
<mc-stepper :steps="['step 1', 'step 2', 'step 3', ...]" no-labels></mc-stepper>
```