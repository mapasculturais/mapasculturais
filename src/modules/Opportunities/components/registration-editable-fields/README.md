# Componente `<registration-editable-fields>`
Componente para o gestor permitir a edição dos formulários das inscrições em um determinado prazo de tempo.
  
## Propriedades
- *Entity **registration*** - Inscrição

### Importando componente
```PHP
<?php 
$this->import('registration-editable-fields');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<registration-editable-fields :registration="registration"></registration-editable-fields>

```