<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
?>
<div class="circle-stencil" :style="style">
  <draggable-element class="circle-stencil__handler" @drag="onResize" @drag-end="onResizeEnd">
    <svg 
      class="circle-stencil__icon" 
      xmlns="http://www.w3.org/2000/svg" 
      width="26.7" 
      height="26.3" 
      @mousedown.prevent
    >
      <path fill="#FFF" d="M15.1 4.7L18.3 6l-3.2 3.3 2.3 2.3 3.3-3.3 1.3 3.3L26.7 0zM9.3 14.7L6 18l-1.3-3.3L0 26.3l11.6-4.7-3.3-1.3 3.3-3.3z"></path>
    </svg>

  </draggable-element>
  <draggable-area @move="onMove" @move-end="onMoveEnd">
    <stencil-preview class="circle-stencil__preview" :image="image" :coordinates="coordinates" :width="stencilCoordinates.width" :height="stencilCoordinates.height" :transitions="transitions" />
  </draggable-area>
</div>