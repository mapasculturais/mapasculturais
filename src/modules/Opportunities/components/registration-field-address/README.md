# Componente `<registration-field-address>`
Componente para edição do campo lista de endereços no formulário de inscrição

### Eventos
- **update:registration** - disparado quando a inscrição é atualizada
  
## Propriedades
- *Entity **registration*** - Inscrição
- *String **prop*** - nome do campo

### Importando componente
```PHP
<?php 
$this->import('registration-field-address');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<registration-field-address :registration="registration" :prop="prop"></registration-field-address>

```