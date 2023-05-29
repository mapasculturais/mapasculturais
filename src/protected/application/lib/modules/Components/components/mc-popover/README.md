# Componente `<mc-popover>`
Componente que cria uma caixinha popover

### Eventos
- **open** - disparado quando a popover é aberto
- **close** - disparado quando a popover é fechado
  
## Propriedades
- *String **classes** = ''* - Classes css para adicionar na modal aberta
- *String **buttonLabel** = ''* - Label do botao de abrir a modal
- *String **buttonClasses** = 'button-primary'* - Classes do botao de abrir a modal
- *String **openside*** - Define para qual direção a popover abrirá. pode ser uma das seguintes opções: `up-right`, `up-left`, `down-right`, `down-left`, `left-up`, `left-down`, `right-up`, `right-down`,

## Slots
- **default** `{open: function, close: function, loading: function}` - Conteúdo da popover aberto.
- **button** `{open: function, close: function, loading: function}` - Botão para abrir a modal

### Importando componente
```PHP
<?php 
$this->import('popover');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<mc-popover openside="down-right" button-label="abrir">
    <p>conteúdo da popover</p>
</mc-popover>

<!-- definindo uma classe para o botão de abrir a popover -->
<mc-popover openside="down-right" button-label="abrir" button-classes="button--secondary">
    <p>conteúdo da popover</p>
</mc-popover>

<!-- customizando o botão de abrir a popover -->
<mc-popover openside="down-right">
    <p>conteúdo da popover</p>

    <template #button="popover">
        <a href="#" @click="popover.toggle()">abrir a popover</a>
    </template>
</mc-popover>

<!-- botão de abrir diferente do botão fechar -->
<mc-popover openside="down-right">
    <p>conteúdo da popover</p>

    <template #button="popover">
        <a v-if="!popover.active" href="#" @click="popover.open()">abrir</a> 
        <a v-if="popover.active" href="#" @click="popover.close()">fechar</a>
    </template>
</mc-popover>

<!-- botão de fechar dentro do conteúdo da popover -->
<mc-popover openside="down-right" button-label="abrir">
    <template #default="popover">
        <p>conteúdo da popover</p>
        <a @click="popover.close()" href="#">fechar</a>
    </template>
</mc-popover>


<!-- utilizando eventos -->
<mc-popover @open="doSomething('abriu')" @close="doSomething('fechou')" openside="down-right" button-label="abrir">
    <template #default="popover">
        <p>conteúdo da popover</p>
        <a @click="popover.close()" href="#">fechar</a>
    </template>
</mc-popover>