# Componente `<mc-notification>`
A ideia do componente `mc-notification` é servir de modelo para a criação de notificações baseadas nos status `success`, `error` e `info`.

 
## Propriedades
- *String **type*** - Tipo de notificação
- *String **message*** - Mensagem de exibição

## Slots
- **default** `{default}`: adiciona mensagem no slot

### Importando componente
```PHP
<?php 
$this->import('mc-notification');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<mc-notification type="success" message="Sucesso nesse processamento"></mc-notification>

<mc-notification type="success">
    Sucesso nesse processamento
</mc-notification>
```