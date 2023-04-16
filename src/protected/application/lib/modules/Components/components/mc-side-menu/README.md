# Componente `<mc-side-menu>`
Abre um side lateral com as informações do slot default

### Eventos
- **toggle** - Altera exibição do side menu

## Propriedades
- *isOpen **boolean*** - Exibe ou oculta o componente

### Importando componente
```PHP
<?php 
$this->import('mc-side-menu');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<mc-side-menu :is-open="open" @toggle="open = !open"></mc-side-menu>

```