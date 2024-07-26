# Componente `<create-event>`

O componente `create-event` é utilizado para criar e gerenciar eventos. Ele exibe um formulário para criação e edição de eventos, incluindo campos obrigatórios e opcionais.

### Eventos
- **create** - Emitido após a criação ou atualização do evento.

## Propriedades
- *Boolean **editable*** - Define se o evento pode ser editado.

## Slots
- **default**: Conteúdo padrão do modal, exibido tanto na criação quanto na edição do evento.
- **button**: Slot para personalizar o botão principal do modal.
- **actions**: Ações exibidas no rodapé do modal, variando conforme o estado da entidade do evento (novo, rascunho, publicado).

### Importando componente
```PHP
<?php 
$this->import('create-event');
?>
```

### Exemplos de uso
```HTML
<create-event :editable="true" @create="handleCreateEvent">
    <template #button="{ modal }">
        <button class="custom-button" @click="modal.close()">Custom Button</button>
    </template>
</create-event>
```