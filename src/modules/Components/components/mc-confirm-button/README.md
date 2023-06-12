# Componente `<mc-confirm-button>`
Adiciona botão para executar ação que depende de confirmação do usuário

### Eventos
- **confirm**
- **cancel**
  
## Propriedades
- **message**: *String* - Texto da mensagem exibida para o usuário
- **yes**: *String* (opcional) - Label do botão de confirmacão
- **no**: *String* (opcional) - Label do botão de cancelamento

## Slots
- **default**: texto do botão (não utilizada se o slog *button* for utilizado)
- **button**: *(opcional)* slot para personalizar o html do botão
- **message**: *(opcional)* slot para personalizar o html da mensagem

### Importando componente
```PHP
<?php 
$this->import('mc-confirm-button');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<mc-confirm-button message="Confirma a execução da ação?"
    @confirm="doSomething($event)">executar ação</mc-confirm-button>

<!-- utilizano o evento on-cancel -->
<mc-confirm-button message="Confirma a execução da ação?"
    @confirm="doSomething($event)"
    @cancel="dontDoSomething($event)">executar ação</mc-confirm-button>

<!-- renomeando os botões -->
<mc-confirm-button message="Confirma a execução da ação?"
    @confirm="doSomething($event)"
    @cancel="dontDoSomething($event)"
    yes="Com certeza!" no="De jeito nenhum!">executar ação</mc-confirm-button>

<!-- personalizando o html do botão: utilizando tag <a> -->
<mc-confirm-button message="Confirma a execução da ação?"
    @confirm="doSomething($event)"
    @cancel="dontDoSomething($event)"
    yes="Com certeza!" no="De jeito nenhum!">
    <template #button="modal">
        <a @click="modal.open()">executar ação</a>
    </template>    
</mc-confirm-button>

<!-- personalizando o html da mensagem -->
<mc-confirm-button
    @confirm="doSomething($event)"
    @cancel="dontDoSomething($event)"
    yes="Com certeza!" no="De jeito nenhum!">
    executar ação
    <template #message="message">
        <h1>CONFIRMA EXECUÇÃO DA AÇÃO???</h1>
        <p>
            É possível colocar um link para 
            cancelar <a @click="message.cancel()">assim</a> 
            e para confirmar <a @click="message.confirm()">assim</a>
        </p>
    </template> 
</mc-confirm-button>

<!-- personalizando o htmls da mensagem e do botão -->
<mc-confirm-button
    @confirm="doSomething($event)"
    @cancel="dontDoSomething($event)"
    yes="Com certeza!" no="De jeito nenhum!">
    <template #button="modal">
        <a @click="modal.open()">executar ação</a>
    </template> 
    <template #message="message">
        <h1>CONFIRMA EXECUÇÃO DA AÇÃO???</h1>
    </template> 
</mc-confirm-button>
```