# Componente `<collapsible-content>`

Componente básico para encapsular conteúdo com funcionalidade de colapso usando slots Header, Body e Footer.

## Propriedades

- `open` (Boolean, default: false) - Estado inicial aberto/fechado
- `classes` (String/Array/Object) - Classes CSS adicionais

## Métodos

- `toggle()` - Alterna estado
- `open()` - Abre
- `close()` - Fecha

## Slots

- **header** (obrigatório) - Cabeçalho clicável
- **body** ou **default** - Conteúdo exibido quando aberto
- **footer** (opcional) - Rodapé exibido apenas quando aberto

## Importando

```PHP
<?php $this->import('collapsible-content'); ?>
```

## Exemplos

```HTML
<!-- Básico -->
<collapsible-content>
    <template #header><h3>Título</h3></template>
    <template #body>Conteúdo</template>
</collapsible-content>

<!-- Com controle externo -->
<collapsible-content :open="show">
    <template #header><h3>Expandir</h3></template>
    <div>Conteúdo</div>
</collapsible-content>
```

