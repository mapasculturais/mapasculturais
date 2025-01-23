# Componente `<mc-chat>`
O componente mc-chat` implementa um chat simplificado. Ele utiliza slots para permitir a inserção/alteração de conteúdo personalizado em diferentes partes do chat.

### Propriedades
- *anonymousSender **String*** : Nome anônimo que será exibido como remetente caso o usuário opte por anonimato. O padrão é null.

- *thread **Entity** (Obrigatório)* : Representa o tópico da conversa, incluindo informações como id e status.

## Slots

- **default**: Slot genérico para personalizar mensagens no chat.
- **my-message**: Slot para personalizar mensagens enviadas pelo usuário atual.
- **my-attachment**: Personaliza mensagens com anexos enviadas pelo usuário atual.
- **other-message**: Personaliza mensagens enviadas por outros usuários.
- **other-attachment**: Personaliza mensagens com anexos enviadas por outros usuários.

## Métodos
- **sendMessage()**: Envia uma mensagem para o chat.
- **saveAttachmentMessage()**: Envia uma mensagem com um arquivo anexado.
- **isMine(message)**: Retorna `true` se a mensagem pertence ao usuário atual.
- **senderName(message):**: Retorna o nome do remetente da mensagem, considerando anonimato, se configurado.
- **isClosed()**: Verifica se o chat está fechado.
- **fetchNewMessages()**: Busca novas mensagens do chat.
- **addNewMessages(newMessages)**: Adiciona novas mensagens ao chat.
- **startAutoRefresh()**: Inicia o intervalo de atualização automática das mensagens.
- **clearAutoRefresh()**: Limpa o intervalo de atualização automática das mensagens.

### Importando componente
```PHP
<?php 
$this->import('mc-chat');
?>
```

### Exemplos de uso
```HTML
<mc-chat v-if="thread" :thread="thread" anonymous-sender="<?= i::__('Suporte') ?>"></mc-chat>
```
