# Componente `<modal>`
Componente que cria uma modal

### Eventos
- **open** - disparado quando a modal é aberta
- **close** - disparado quando a modal é fechada
  
## Propriedades
- *String **title** = ''* - Título da modal, que aparece no header da modal aberta
- *String **button** = ''* - Classes css para adicionar na modal aberta
- *String **classes** = ''* - Classes css para adicionar na modal aberta
- *Boolean **closeButton** = true* - Se deve exibir o botão de fechar a modal

## Slots
- **default** `{open: function, close: function, loading: function}` - Conteúdo da modal aberta.
- **actions** `{open: function, close: function, loading: function}` - Botões de ação no footer da modal
- **button** `{open: function, close: function, loading: function}` - Botão para abrir a modal

### Importando componente
```PHP
<?php 
$this->import('modal');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<modal button="abrir">
    <p>conteúdo da modal</p>
</modal>

<!-- definindo um título para a modal -->
<modal button="abrir" title="Título da modal">
    <p>conteúdo da modal</p>
</modal>

<!-- definindo botões de ação -->
<modal button="abrir" title="Título da modal">
    <p>conteúdo da modal</p>

    <template #actions="modal">
        <button @click="doSomething(modal)">fazer algo</button>
        <button @click="modal.close()">cancelar</button>
    </template>
</modal>

<!-- definindo um botão customizado para abrir a modal -->
<modal title="Título da modal">
    <p>conteúdo da modal</p>

    <template #actions="modal">
        <button @click="doSomething(modal)">fazer algo</button>
        <button @click="modal.close()">cancelar</button>
    </template>

    <template #button="modal">
        <a href="#" @click="modal.open()">abrir a modal</a>
    </template>
</modal>