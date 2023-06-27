# Componente `<registration-info>`
Componente para exibir as informações básicas de uma inscrição
  
## Propriedades
- *Entity **registration*** - Entidade
- *String **name*** - Nome

### Importando componente
```PHP
<?php 
$this->import('registration-info');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<registration-info :registration="entity"></registration-info>

```