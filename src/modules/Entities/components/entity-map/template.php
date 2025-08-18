<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    mc-map 
    mc-map-marker
');
?>

<mc-map :center="location">
    <?php $this->applyTemplateHook('entity-map','begin'); ?>
    <mc-map-marker :entity="entity" :draggable="editable" @moved="change($event)"></mc-map-marker>
    <?php $this->applyTemplateHook('entity-map','end'); ?>
</mc-map>