# Componente `<theme-logo>`
Componente com a logo do tema <br>
As cores da logo podem ser modificadas no arquivo `init.php` ou por meio das propriedades
  
## Propriedades
- *String **href*** - Link de redirecionamento
- *String **bg1*** = Cor do primeiro item
- *String **bg2*** = Cor do segundo item
- *String **bg3*** = Cor do terceiro item
- *String **bg4*** = Cor do quarto item

### Importando componente
```PHP
<?php 
$this->import('theme-logo');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<theme-logo href="http://exemple.com"></theme-logo>

<!-- cores personalizadas -->
<theme-logo href="http://exemple.com" bg1="#0074C1" bg2="#D50200" bg3="#0074C1" bg4="#D50200"></theme-logo>
```