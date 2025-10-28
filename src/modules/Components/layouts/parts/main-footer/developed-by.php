<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('mc-icon');
?>
<p>
    <?php i::_e("plataforma criada pela comunidade") ?> 
    <span class="mapas"> <mc-icon name="map"></mc-icon><?php i::_e("mapas culturais"); ?> </span> 
    <?php i::_e("e desenvolvida por "); ?><strong>hacklab<span style="color: red">/</span></strong>
</p>