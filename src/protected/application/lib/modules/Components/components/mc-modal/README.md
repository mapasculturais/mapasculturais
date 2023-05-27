# Componente `<mc-modal>`
Componente que cria uma modal

### Eventos
- **open** - disparado quando a modal é aberta
- **close** - disparado quando a modal é fechada
  
## Propriedades
- *String **title** = ''* - Título da modal, que aparece no header da modal aberta
- *String **classes** = ''* - Classes css para adicionar na modal aberta
- *String **buttonLabel** = ''* - Label do botao de abrir a modal
- *String **buttonClasses** = ''* - Classes do botao de abrir a modal
- *Boolean **closeButton** = true* - Se deve exibir o botão de fechar a modal

## Slots
- **default** `{open: function, close: function, loading: function}` - Conteúdo da modal aberta.
- **actions** `{open: function, close: function, loading: function}` - Botões de ação no footer da modal
- **button** `{open: function, close: function, loading: function}` - Botão para abrir a modal

### Importando componente
```PHP
<?php 
$this->import('mc-modal');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<mc-modal button-label="abrir">
    <p>conteúdo da modal</p>
</mc-modal>

<!-- definindo um título para a modal -->
<mc-modal button-label="abrir" title="Título da modal">
    <p>conteúdo da modal</p>
</mc-modal>

<!-- definindo uma classe para o botão de abrir a modal -->
<mc-modal button-label="abrir" button-classes="button--secondary" title="Título da modal">
    <p>conteúdo da modal</p>
</mc-modal>

<!-- definindo botões de ação -->
<mc-modal button-label="abrir" title="Título da modal">
    <p>conteúdo da modal</p>

    <template #actions="modal">
        <button @click="doSomething(modal)">fazer algo</button>
        <button @click="modal.close()">cancelar</button>
    </template>
</mc-modal>

<!-- definindo um botão customizado para abrir a modal -->
<mc-modal title="Título da modal">
    <p>conteúdo da modal</p>

    <template #actions="modal">
        <button @click="doSomething(modal)">fazer algo</button>
        <button @click="modal.close()">cancelar</button>
    </template>

    <template #button="modal">
        <a href="#" @click="modal.open()">abrir a modal</a>
    </template>
</mc-modal>

<!-- usando uma tag <template> para o slot padrao -->
<mc-modal title="Título da modal">
    <template #default="modal">
        <p>conteúdo da modal</p>
        <a @click="modal.close()">você pode fechar a modal por aqui também</a>
    </template>

    <template #actions="modal">
        <button @click="doSomething(modal)">fazer algo</button>
        <button @click="modal.close()">cancelar</button>
    </template>

    <template #button-label="modal">
        <a href="#" @click="modal.open()">abrir a modal</a>
    </template>
</mc-modal>