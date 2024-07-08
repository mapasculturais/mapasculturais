# Componente `<mc-accordion>`

## Descrição
O componente mc-accordion implementa um painel expansível que exibe e oculta seu conteúdo quando o cabeçalho é clicado.

## Props:
Este componente não possui props definidas diretamente. Ele utiliza slots para passar o conteúdo do título e do conteúdo que serão exibidos dentro do accordion.

## Slots:
- name="title": Slot para o título do accordion. Deve conter o título que será exibido no cabeçalho do accordion.

- name="content": Slot para o conteúdo do accordion. O conteúdo será exibido ou ocultado quando o accordion estiver ativo.

## Eventos:
Este componente não emite eventos.

## Importando o componente
```PHP
<?php 
$this->import('mc-accordion');
?>
```
## Exemplo de Uso 
```HTML
você pode incluir o componente mc-accordion diretamente no template do seu aplicativo, onde deseja que ele seja renderizado.

<mc-accordion>
    <template v-slot:title>
        Título do Accordion
    </template>
    <template v-slot:content>
        Conteúdo do Accordion
    </template>
</mc-accordion>
```
## Estrutura:
```HTML
<section class="mc-accordion">
    <header @click="toggle()" :class="{ 'mc-accordion__header--active': active }" class="mc-accordion__header">
        <mc-title tag="h3" class="bold mc-accordion__title">
            <slot name="title"></slot>
        </mc-title>
        <mc-icon :name="active ? 'arrowPoint-up' : 'arrowPoint-down'" class="primary__color"></mc-icon>
    </header>
    <div v-if="active" class="mc-accordion__content">
        <slot name="content"></slot>
    </div>
</section>
```
## Observações:
- O cabeçalho do accordion (mc-accordion__header) expande e recolhe o conteúdo quando clicado, alternando a classe mc-accordion__header--active.
- O ícone dentro do cabeçalho indica o estado atual do accordion (expandido ou recolhido).
- O conteúdo do accordion (mc-accordion__content) é exibido somente quando o accordion está ativo (active é true).

