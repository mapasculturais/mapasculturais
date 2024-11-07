# Componente `<entity-link-project>`

O componente `entity-link-project` é utilizado para exibir e gerenciar a vinculação de um projeto a uma entidade. Ele permite que você veja o projeto vinculado e forneça uma interface para alterar o projeto vinculado ou adicionar um novo.

## Propriedades
- *Entity **entity*** - Entidade
- *String **title*** - Título que será exibido ao lado do nome da entidade.
- *String **type*** - Tipo da entidade que está sendo vinculado.
- *Boolean **editable*** - Define se o componente pode ser editado.
- *String/Array/Object **classes*** - Classes CSS adicionais para estilização.
- *String **label*** - Rótulo exibido para a ação de adicionar.

## Slots
- **button**: Slot para personalizar o botão no componente `select-entity` usado para alterar o projeto vinculado.

### Importando componente
```PHP
<?php 
$this->import('entity-link-project');
?>
```

### Exemplos de uso
```HTML
<entity-link-project
    :entity="myEntity"
    :type="'project'"
    :label="'Adicionar Projeto'"
    :classes="'custom-class'"
>
    <template #button="{ toggle }">
        <a @click="toggle()">
            <button class="custom-button">
                <mc-icon name="add"></mc-icon>Adicionar
            </button>
        </a>
    </template>
</entity-link-project>
```