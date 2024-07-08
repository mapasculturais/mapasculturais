# Componente
O componente cropper-stencil é um componente Vue.js que permite aos usuários interagir com uma área de recorte (stencil) em uma imagem. Ele oferece funcionalidades para mover e redimensionar a área de recorte, além de exibir uma pré-visualização do recorte.

### Importando componente
Para utilizar o componente, inclua o seguinte código em seu template HTML:

```PHP
<?php 
$this->import('cropper-stencil');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<cropper-stencil :entity="entity"></cropper-stencil>

<cropper-stencil 
    :image="imageObject" 
    :coordinates="cropCoordinates" 
    :transitions="transitionSettings" 
    :stencilCoordinates="stencilCoords"
    @move="handleMove" 
    @move-end="handleMoveEnd" 
    @resize="handleResize" 
    @resize-end="handleResizeEnd">
</cropper-stencil>

Neste exemplo, o componente cropper-stencil é utilizado para permitir o recorte de uma imagem. As propriedades são passadas para definir a imagem, coordenadas e transições, e eventos são escutados para tratar movimentos e redimensionamentos.
```

# Props
- image: Objeto que contém a imagem a ser exibida no recorte.
- coordinates: Objeto que contém as coordenadas da área de recorte.
- transitions: Objeto que contém as configurações de transição para a área de recorte.
- stencilCoordinates: Objeto que contém as coordenadas da área de recorte (stencil).

# Computed Properties
- style(): Retorna o estilo CSS para a área de recorte, incluindo altura, largura, e transformações baseadas nas coordenadas do stencil. Se as transições estiverem habilitadas, adiciona as configurações de transição ao estilo.

# Métodos
- onMove(moveEvent): Emite o evento move com os detalhes do evento de movimento.
- onMoveEnd(): Emite o evento move-end quando o movimento termina.
- onResize(dragEvent): Calcula e emite o evento resize com os detalhes do evento de redimensionamento.
- onResizeEnd(): Emite o evento resize-end quando o redimensionamento termina.
- aspectRatios(): Retorna as proporções mínimas e máximas para o recorte.
