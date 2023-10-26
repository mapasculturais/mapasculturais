# Componente `<panel--last-registrations>`
Listagem dos cards das ultimas inscrições do usuário - painel de controle

## Propriedades
- *Number **limit*** - Limite de cards listados no componente

### Importando componente
```PHP
<?php 
$this->import('panel--last-registrations');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<panel--last-registrations></panel--last-registrations>

<!-- utilização alterando limite de cards a serem carregados -->
<panel--last-registrations :limit="10"></panel--last-registrations>

```