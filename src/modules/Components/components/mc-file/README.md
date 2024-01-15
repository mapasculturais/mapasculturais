# Componente `<mc-file>`
Componente para seleção de arquivos com input personalizado

### Eventos
- **fileSelected** - disparado quando um arquivo é selecionado, emitindo o arquivo selecionado

### Importando componente
```PHP
<?php 
$this->import('mc-file');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<mc-file @file-selected="fileHandler()"></mc-file>

```