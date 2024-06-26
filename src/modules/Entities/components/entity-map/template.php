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

<?php $this->applyTemplateHook('entity-map','before'); ?>
<mc-map v-if="entity.endereco" :center="entity.location">
    <mc-map-marker :entity="entity" :draggable="editable" @moved="change($event)"></mc-map-marker>
</mc-map>
<?php $this->applyTemplateHook('entity-map','after'); ?>