# Componente `<mc-accordion>`
O componente  `mc-accordion ` é utilizado para criar uma seção de conteúdo expansível e recolhível, com um cabeçalho clicável que alterna a visibilidade do conteúdo.

## Slots
- **title**: Slot para o título do acordeão que será exibido no cabeçalho.
- **content**: Slot para o conteúdo que será mostrado quando o acordeão estiver expandido.

### Importando componente
```PHP
<?php 
$this->import('mc-accordion');
?>
```

### Exemplos de uso
```HTML
<mc-accordion>
    <template #title>
        <!-- Conteúdo do título -->
        Título do Acordeão
    </template>
    <template #content>
        <!-- Conteúdo expansível -->
        Conteúdo do Acordeão
    </template>
</mc-accordion>
```