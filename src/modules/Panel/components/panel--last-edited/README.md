# Componente `<panel--last-edited>`
Listagem dos cards das ultimas entidades editadas - painel de controle

## Propriedades
- *Number **limit*** - Limite de cards listados no componente

### Importando componente
```PHP
<?php 
$this->import('panel--last-edited');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<panel--last-edited></panel--last-edited>

<!-- utilização alterando limite de cards a serem carregados -->
<panel--last-edited :limit="10"></panel--last-edited>

```