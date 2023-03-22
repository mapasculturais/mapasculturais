# Componente `<entity-gallery-video>`
Componente da galeria de vídeos da entidade

## Propriedades
- *Entity **entity*** - Entidade
- *String **title*** - Título do componente
- *Boolean **editable** = false* - Modo de edição do componente
- *String/Array/Object **classes*** - Classes a serem aplicadas no componente

### Importando componente
```PHP
<?php 
$this->import('entity-gallery-video');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<entity-gallery-video :entity="entity" title="Galeria de vídeos"></entity-gallery-video>

<!-- utilizano no modo de edição -->
<entity-gallery-video :entity="entity" title="Editar galeria de vídeos" editable></entity-gallery-video>

<!-- utilização com classes personalizadas -->
<entity-gallery-video :entity="entity" title="Galeria de vídeos" classes="classe-unica"></entity-gallery-video>

<entity-gallery-video :entity="entity" title="Galeria de vídeos" :classes="['classe-um', 'classe-dois']"></entity-gallery-video>
```