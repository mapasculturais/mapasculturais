# Componente `<mc-status>`
O componente `mc-status` exibe um status visual baseado no nome do status fornecido como propriedade. Ele aplica classes CSS correspondentes ao status para destacar visualmente o estado associado.
  
## Propriedades
- *String **statusName*** - Nome do status a ser exibido. As classes CSS serão aplicadas com base neste nome para refletir o estado correspondente.

### Importando componente
```PHP
<?php 
$this->import('mc-status');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<mc-status status-name="Aguardando Avaliação"></mc-status>
```