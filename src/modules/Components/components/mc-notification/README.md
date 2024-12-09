# Componente `<mc-notification>`
O componente `mc-notification` é utilizado para exibir notificações de diferentes tipos (`sucesso`, `erro`, `informação`) na interface do usuário. Este componente aceita um tipo e uma mensagem, e exibe um ícone e a mensagem correspondente.

## Propriedades
- *Type **string*** - Define o tipo da notificação. Pode ser success, error ou info.
- *Message **String*** - A mensagem a ser exibida na notificação.

## Slots
- **default** `{msg}`: permite a personalização do conteúdo da mensagem exibida na notificação.
 
### Importando componente
```PHP
<?php 
$this->import('mc-notification');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<mc-notification type="success" message="Operação realizada com sucesso"></mc-notification>

<!-- Utilizando Slots -->
<mc-notification type="error">
    <template #default>
        Ocorreu um erro durante a operação.
    </template>
</mc-notification>
```