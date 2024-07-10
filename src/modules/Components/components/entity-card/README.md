# Componente `<entity-card>`
O componente `<entity-card>` exibe informações detalhadas sobre uma entidade (Entity), incluindo seu nome, descrição, selos e outras características associadas. Ele foi projetado para ser reutilizável em diferentes contextos onde informações sobre entidades precisam ser apresentadas de forma clara e organizada.

### Eventos
- **move** - Disparado durante o movimento da área de recorte.
- **move-end** - Disparado quando o movimento da área de recorte termina.
- **resize** - Disparado durante o redimensionamento da área de recorte.
- **resize-end** - Disparado quando o redimensionamento da área de recorte termina.

### Propriedades
- *Entity **entity*** - Entidade (Obrigatório).
- *String **class*** - Classe CSS para personalização.
- *Boolean **portrait*** = false - Define se a imagem será exibida em formato de retrato.
- *Boolean **sliceDescription*** = false - Define se a descrição será truncada.
- *String **tag*** = 'h2' - Define a tag HTML para o título.

## Slots
- **avatar** - Customiza o avatar da entidade
- **title** - Customiza o título da entidade
- **type** - Customiza o tipo da entidade
- **labels** - Customiza os rótulos adicionais no cabeçalho

### Importando componente
```PHP
<?php 
$this->import('entity-card');
?>
```
### Exemplos de uso
```HTML
<!-- utilização básica -->
<entity-card :entity="entity"></entity-card>

<!-- customizando slots e utilizando eventos -->
<entity-card :entity="entity" class="custom-class" @move="onMoveHandler" @resize="onResizeHandler">
    <template #avatar>
        <mc-avatar :entity="entity" size="large"></mc-avatar>
    </template>
    <template #title>
        <mc-title tag="h1" :shortLength="60" :longLength="80" class="bold">{{ entity.name }}</mc-title>
    </template>
    <template #type>
        <div v-if="entity.type" class="user-info__attr">
            Tipo: {{ entity.type.name }}
        </div>
    </template>
    <template #labels>
        <div class="custom-labels">
            ID: <span class="bold">{{ entity.id }}</span>
            <span v-if="openSubscriptions">Inscrições Abertas</span>
        </div>
    </template>
</entity-card>
```