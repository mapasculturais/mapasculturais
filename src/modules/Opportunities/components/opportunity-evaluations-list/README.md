# Componente `<opportunity-evaluations-list>`
Abre um side lateral com as informações do slot default

### Eventos
- **toggle** - Altera exibição do side menu

## Propriedades
- *isOpen **boolean*** - Exibe ou oculta o componente

### Importando componente
```PHP
<?php 
$this->import('opportunity-evaluations-list');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<opportunity-evaluations-list :is-open="open" @toggle="open = !open"></opportunity-evaluations-list>

```