# Componente `<mc-container>`
- O componente mc-container é um contêiner simples que encapsula outros elementos ou componentes. Ele utiliza slots para renderizar o conteúdo filho, proporcionando uma estrutura flexível e reutilizável para organizar layouts.

## Propriedades
- Este componente não possui propriedades.

## Emissões
- Este componente não emite eventos.
## Setup
- hasSlot(name): Função que verifica se um slot específico está presente.

## Slots
- default: Slot padrão para renderizar o conteúdo dentro do contêiner. Todo o conteúdo inserido dentro do componente mc-container será renderizado
```HTML 
<div class="container">
    <slot name="default"> </slot>
</div>
```
## Importando o componente
```PHP
<?php 
$this->import('mc-container');
?>
```
## Exemplo de Uso

```HTML
<!-- utilizaçao básica -->
você pode incluir o componente mc-container diretamente no template do seu aplicativo, onde deseja que ele seja renderizado.

<mc-container></mc-container>
```

```HTML
<!-- Exemplo Completo -->
 <mc-container>
    <h1>Meu Título</h1>
    <p>Este é um parágrafo dentro do contêiner.</p>
    <button>Clique aqui</button>
</mc-container>
```

## Observações
- O componente mc-container é útil para organizar e agrupar elementos no layout.
- Ele não possui lógica complexa ou propriedades configuráveis, tornando-o fácil de usar e integrar em diferentes partes da aplicação.
- A classe CSS container pode ser estilizada conforme necessário para ajustar a aparência e o comportamento do contêiner.
