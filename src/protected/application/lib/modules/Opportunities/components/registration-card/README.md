# Componente `<registration-card>`
Cards da tela 'minhas inscrições'
  
## Propriedades
- *Entity **entity*** - Entidade
- *Booleano **has-border** = false* - Adiciona uma borda no card (para lugares onde o card é inserido em uma tela com fundo de mesma cor)
- *Booleano **picture-card** = false* - Renderiza o card com imagem do agente inscrito

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

<!-- utilizaçao com o modo imagem -->
<registration-card :entity="registration" picture-card></registration-card>

<!-- utilizaçao do card com bordas -->
<registration-card :entity="registration" has-border></registration-card>

```