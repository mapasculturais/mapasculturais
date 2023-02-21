# Componente `<stepper>`
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
$this->import('stepper');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<stepper :steps="['step 1', 'step 2', 'step 3', ...]"></stepper>

<!-- Utilização com step definido -->
<stepper :steps="['step 1', 'step 2', 'step 3', ...]" :stepped="2"></stepper>

<!-- Utilização com a versão small -->
<stepper :steps="['step 1', 'step 2', 'step 3', ...]" small></stepper>

<!-- Utilização apenas com labels ativas -->
<stepper :steps="['step 1', 'step 2', 'step 3', ...]" only-active-label></stepper>

<!-- Utilização sem labels -->
<stepper :steps="['step 1', 'step 2', 'step 3', ...]" no-labels></stepper>
```