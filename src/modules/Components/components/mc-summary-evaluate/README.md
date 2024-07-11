# Componente `<mc-summary-evaluate>`
O componente `mc-summary-evaluate` exibe um resumo das avaliações, mostrando o status de avaliações pendentes, iniciadas, concluídas e enviadas.

## Propriedades
- *Object **summary*** - Objeto com o resumo de avaliações
### Importando componente
```PHP
<?php 
$this->import('mc-summary-evaluate');
?>
```

### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<mc-summary-evaluate :classes="['custom-class']"></mc-summary-evaluate>
```