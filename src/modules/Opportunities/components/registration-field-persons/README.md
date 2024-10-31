# Componente `<registration-field-persons>`
Componente para edição do campo lista de pessoas no formulário de inscrição

### Eventos
- **update:registration** - disparado quando a inscrição é atualizada
  
## Propriedades
- *Entity **registration*** - Inscrição
- *String **prop*** - nome do campo

### Importando componente
```PHP
<?php 
$this->import('registration-field-persons');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<registration-field-persons :registration="registration" :prop="prop"></registration-field-persons>

```