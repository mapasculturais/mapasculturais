<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
use MapasCulturais\i;
?>
<div :class="['opportunity-form-view', classes]">
    <a v-bind:href="url" type="button" class="button button--primary-outline button--large">
      <?php i::_e("Visualizar") ?>
    </a>
</div>