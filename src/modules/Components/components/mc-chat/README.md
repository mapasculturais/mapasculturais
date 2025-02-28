# Componente `<mc-chat>`
O componente mc-chat` implementa um chat simplificado. Ele utiliza slots para permitir a inserção/alteração de conteúdo personalizado em diferentes partes do chat.

### Propriedades
- *anonymousSender **String*** : Nome anônimo que será exibido como remetente caso o usuário opte por anonimato. O padrão é null.

- *ping-pong **Boolean*** : Define se o chat deve ser configurado para ping-pong, onde cada participante só pode enviar uma mensagem por vez. O padrão é false.

- *thread **Entity** (Obrigatório)* : Representa o tópico da conversa, incluindo informações como id e status.

## Slots

- **default**: Slot genérico para personalizar mensagens no chat.
- **message-form**": Slot para personalizar o formulário de envio de mensagens e anexos.
- **message-payload**: Slot para personalizar o campo de mensagem.
- **message-send-button**: Slot para personalizar o botão de envio de mensagem.
- **message-upload**: Slot para personalizar o campo de upload de arquivos.
- **my-message**: Slot para personalizar mensagens enviadas pelo usuário atual.
- **other-message**: Personaliza mensagens enviadas por outros usuários.

## Métodos
- **addNewMessages(newMessages)**: Adiciona novas mensagens ao chat.
- **clearAutoRefresh()**: Limpa o intervalo de atualização automática das mensagens.
- **createNewMessage(payload)**: Inicia a entidade de uma nova mensagem no chat.
- **fetchNewMessages()**: Busca novas mensagens do chat.
- **handleEntitiesUpdate(entities)**: Manipula atualizações de entidades.
- **handleTextareaBlur()**: Manipula o desfoco no campo de texto.
- **handleTextareaFocus()**: Manipula o foco no campo de texto.
- **isClosed()**: Verifica se o chat está fechado.
- **isMine(message)**: Retorna `true` se a mensagem pertence ao usuário atual.
- **senderName(message):**: Retorna o nome do remetente da mensagem, considerando anonimato, se configurado.
- **sendMessage()**: Envia uma mensagem para o chat.
- **startAutoRefresh()**: Inicia o intervalo de atualização automática das mensagens.
- **updateAutoRefreshInterval()**: Atualiza o intervalo de atualização automática das mensagens.
- **verifyState(status)**: Retorna a string referente ao status.

### Importando componente
```PHP
<?php 
$this->import('mc-chat');
?>
```

### Exemplos de uso

#### Exemplo de uso com avaliador anônimo
```HTML
<mc-chat v-if="thread" :thread="thread" anonymous-sender="<?= i::__('Suporte') ?>"></mc-chat>
```

#### Exemplo de uso com ping-pong
```HTML
<mc-chat v-if="thread" :thread="thread" :ping-pong="true"></mc-chat>
```
