# Componente `<entity-cover>`
Componente para edição da imagem de capa da entidade
  
## Propriedades
- *Entity **entity*** - Entidade
- *String/Array/Object **classes*** - Classes a serem aplicadas no componente

### Importando componente
```PHP
<?php 
$this->import('entity-cover');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<entity-cover :entity="entity"></entity-cover>

<!-- utilização com classes personalizadas -->
<entity-cover :entity="entity" classes="classe-unica"></entity-cover>
<entity-cover :entity="entity" :classes="['classe-um', 'classe-dois']"></entity-cover>
```