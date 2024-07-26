# Componente `<create-agent>`

O componente `create-agent` é utilizado para criar e gerenciar agentes. Ele exibe um formulário para criação e edição de agentes, incluindo campos obrigatórios e opcionais.

### Eventos
- **create** - Emitido após a criação ou atualização do agente.

## Propriedades
- *Boolean **editable*** - Define se o agente pode ser editado.

## Slots
- **default**: Conteúdo padrão do modal, exibido tanto na criação quanto na edição do agente.
- **button**: Slot para personalizar o botão principal do modal.
- **actions**: Ações exibidas no rodapé do modal, variando conforme o estado da entidade do agente (novo, rascunho, publicado).

### Importando componente
```PHP
<?php 
$this->import('create-agent');
?>
```

### Exemplos de uso
```HTML
<create-agent :editable="true" @create="handleCreateAgent">
    <template #button="{ modal }">
        <button class="custom-button" @click="modal.close()">Custom Button</button>
    </template>
</create-agent>
```