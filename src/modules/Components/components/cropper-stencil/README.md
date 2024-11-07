# Componente `<cropper-stencil>`
O componente `cropper-stencil`é utilizado para manipulação de recortes de imagens, permitindo redimensionar e mover uma área de recorte. Ele integra funcionalidades de componentes avançados de recorte como `StencilPreview`, `DraggableArea`, `DraggableElement` e `ResizeEvent`.

### Eventos
- **move** - Disparado quando a área de recorte é movida.
- **move-end** - Disparado quando a movimentação da área de recorte é finalizada.
- **resize** - Disparado quando a área de recorte é redimensionada.
- **resize-end** - Disparado quando o redimensionamento da área de recorte é finalizado.

### Propriedades
- *Image **image*** - Objeto da imagem a ser recortada.
- *Coordinates **coordinates*** - Coordenadas atuais da área de recorte.
- *Transitions **transitions*** - Configurações de transição para a área de recorte.
- *StencilCoordinates **stencilCoordinates*** - Coordenadas da área de recorte (altura, largura, esquerda, topo).

### Importando componente
```PHP
<?php 
$this->import('cropper-stencil');
?>
```
### Exemplos de uso
```HTML
 <!-- Uso básico -->
<cropper-stencil :image="image" :coordinates="coordinates" :transitions="transitions" :stencilCoordinates="stencilCoordinates" @move="handleMove" @move-end="handleMoveEnd" @resize="handleResize" @resize-end="handleResizeEnd"></cropper-stencil>
```