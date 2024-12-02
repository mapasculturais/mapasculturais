# Componente `<mc-stepper>`
O componente `mc-stepper` é utilizado para exibir uma barra de navegação sequencial, geralmente usada para orientar o progresso através de uma série de etapas ou passos em um processo.

### Eventos
- **stepChanged** - Disparado quando a etapa atual é alterada através da navegação no stepper.
  
## Propriedades
- *Small **Boolean*** - Define o tamanho compacto do stepper.
- *Id **String*** - ID único para o componente.
- *Steps **Array*** - Array de etapas ou número total de etapas no stepper.
- *OnlyActiveLabel **Boolean*** - Define se apenas a label da etapa ativa deve ser exibida.
- *NoLabels **Boolean*** - Define se as labels das etapas devem ser ocultadas.
- *DisableNavigation **Boolean*** - Desabilita a navegação entre as etapas.
- *DisabledSteps* - Array com os índices de etapas desabilitadas.

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