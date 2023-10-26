# Componente `<entity-social-media>`
Componente para listagem e edição das redes sociais da entidade
  
## Propriedades
- *Entity **entity*** - Entidade
- *Boolean **editable** = false* - Modo de edição do componente
- *String/Array/Object **classes*** - Classes a serem aplicadas no componente

### Importando componente
```PHP
<?php 
$this->import('entity-social-media');
?>
```

### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<entity-social-media :entity="entity"></entity-social-media>

<!-- utilizaçao nas telas de edição -->
<entity-social-media :entity="entity" editable></entity-social-media>

<!-- utilizaçao com classes personalizadas -->
<entity-social-media :entity="entity" classes="col-12"></entity-social-media>

<entity-social-media :entity="entity" :classes="['col-12']"></entity-social-media>
```