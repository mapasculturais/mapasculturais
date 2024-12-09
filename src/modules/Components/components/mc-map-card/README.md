# Componente `<mc-map-card>`
O componente `mc-map-card` é utilizado para exibir informações detalhadas sobre uma entidade em formato de cartão. Ele inclui detalhes como nome, tipo, endereço, acessibilidade e áreas de atuação da entidade.
  
## Propriedades
- *Entity **entity*** - Entidade que será exibida no cartão. Esta propriedade é obrigatória.

### Importando componente
```PHP
<?php 
$this->import('mc-map-card');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica (dentro de um mc-map) -->
<mc-map>
    <mc-map-card :entity="entity" icon="" class="" label=""></mc-map-card>
<mc-map>
```