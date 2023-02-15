# Componente `<registration-card>`
Cards da tela 'minhas inscrições'
  
## Propriedades
- *Entity **entity*** - Entidade
- *Booleano **border*** - Adiciona uma borda no card (para lugares onde o card é inserido em uma tela com fundo de mesma cor)

### Importando componente
```PHP
<?php 
$this->import('registration-card');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<registration-card :entity="registration"></registration-card>

```