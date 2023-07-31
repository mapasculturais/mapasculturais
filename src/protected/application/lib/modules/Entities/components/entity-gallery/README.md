# Componente `<entity-gallery>`
Componente da galeria de imagens da entidade

## Propriedades
- *Entity **entity*** - Entidade
- *String **title*** - Título do componente
- *Boolean **editable** = false* - Modo de edição do componente
- *String/Array/Object **classes*** - Classes a serem aplicadas no componente

### Importando componente
```PHP
<?php 
$this->import('entity-gallery');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<entity-gallery :entity="entity" title="Galeria de imagens"></entity-gallery>

<!-- utilizano no modo de edição -->
<entity-gallery :entity="entity" title="Editar galeria de imagens" editable></entity-gallery>

<!-- utilização com classes personalizadas -->
<entity-gallery :entity="entity" title="Galeria de imagens" classes="classe-unica"></entity-gallery>

<entity-gallery :entity="entity" title="Galeria de imagens" :classes="['classe-um', 'classe-dois']"></entity-gallery>
```