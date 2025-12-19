# Componente `<mc-file>`

Componente para seleção de arquivos com input personalizado

### Propriedades

- *String* **accept** - tipos de arquivos aceitos pelo input ([ver docs](https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Attributes/accept))

### Eventos

- **fileSelected** - disparado quando um arquivo é selecionado, emitindo o arquivo selecionado
- **mcFileClear** - Fica sempre escutando para lipar a seleção de um arquivo

### Importando componente

```php
<?php 
$this->import('mc-file');
?>
```

### Exemplos de uso

```html
<!-- utilizaçao básica -->
<mc-file @file-selected="fileHandler()"></mc-file>

```
