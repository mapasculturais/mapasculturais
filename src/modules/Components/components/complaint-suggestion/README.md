# Componente `<complaint-suggestion>`
O componente `<complaint-suggestion>` permite o envio de sugestões e reclamações com a integração do reCAPTCHA para validação e segurança. Ele oferece suporte para autenticação do usuário, personalização de mensagens e envio de dados por meio de uma API.

### Eventos
- **submitSuccess** - Disparado quando a mensagem é enviada com sucesso.
- **submitError** - Disparado quando ocorre um erro ao enviar a mensagem.

## Propriedades
- *Entity **entity*** - Entidade
- *Classes **classes*** - Classes

### Importando componente
```PHP
<?php 
$this->import('complaint-suggestion');
?>
```
### Exemplos de uso
```HTML
    <!-- Uso básico -->
    <complaint-suggestion :entity="entity"></complaint-suggestion>

    <!-- Uso com classes adicionais -->
    <complaint-suggestion :entity="entity" classes="custom-class"></complaint-suggestion>

    <!-- Uso com captura de eventos -->
    <complaint-suggestion :entity="entity" @submitSuccess="handleSuccess" @submitError="handleError"></complaint-suggestion>
```