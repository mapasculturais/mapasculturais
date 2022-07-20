<?php
use MapasCulturais\i;
?>
<?php $this->applyTemplateHook('accountability-registrations-list','before'); ?>
<td class="registration-status-col" ng-controller="publishedResultRegistration">
    <?php $this->applyTemplateHook('accountability-registrations-list','begin'); ?>

    <button class="btn btn-primary" ng-if="(!isPublished(reg.id)  && reg.status > 1) && published == false" ng-click="publishedResult(reg)"><?php i::_e("Publicar");?></button>
    <button class="btn btn-success" ng-if="isPublished(reg.id) || published == true"><?php i::_e("Publicado");?></button>

    <?php $this->applyTemplateHook('accountability-registrations-list','end'); ?>
</td>
<?php $this->applyTemplateHook('accountability-registrations-list','after'); ?>