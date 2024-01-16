# Componente `<mc-select>`
Componente select personalizado do mapas culturais

### Eventos
- **changeOption** - disparado quando uma opção é selecionada, enviando o `text` e `value` da mesma.
  
## Propriedades
- *String **default-value*** - Opção inicial

## Slots
- **default** - Espaço para inserção das tags `<option>`

### Importando componente
```PHP
<?php 
$this->import('mc-select');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<mc-select :default-value="valor2" @change-option="optionHandler">
    <option value="valor1">opção 1</option>
    <option value="valor2">opção 2</option>
    <option value="valor3">opção 3</option>
</mc-select>

```