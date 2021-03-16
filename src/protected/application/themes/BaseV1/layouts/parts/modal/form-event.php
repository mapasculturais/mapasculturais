<?php
 use MapasCulturais\i;
$url = $app->createUrl($entity_name);
$classes = $this->getModalClasses($use_modal);
$name = mb_strtolower($entity_classname::getEntityTypeLabel());

$title = sprintf(i::__("Crie um %s com informações básicas"), $name);
$app->applyHook('mapasculturais.add_entity_modal.title', [&$title]);
?>

<div id="<?php echo $modal_id; ?>" class="entity-modal <?php echo $classes['classes']; ?>" title="<?php echo $title; ?>" style="display: none">

    <?php $this->part('modal/before-form'); ?>
    <?php $this->part('modal/feedback', ['entity_name' => $entity_name, 'label' => $name]); ?>

    <form method="POST" class="create-entity <?php echo ($use_modal) ? "" : "is-attached"; ?>" action="<?php echo $url; ?>"
          data-entity="<?php echo $url; ?>" data-formid="<?php echo $modal_id; ?>" id="form-for-<?php echo $modal_id; ?>">
        
        <?php $this->renderModalFields($entity_classname, $entity_name, $modal_id); ?>
        <?php $this->renderModalRequiredMetadata($entity_classname, $entity_name); ?>
        <?php $this->renderModalTaxonomies($entity_classname, $entity_name); ?>

        <input type="hidden" name="parent_id" value="<?php echo $app->user->profile->id; ?>">
        <?php $this->part('modal/footer', ['entity' => $entity_name]); ?>

        <?php $this->part('modal/actions-event', ['entity_name' => $entity_name, 'classes' => $classes, 'name' => $name, 'modal_id' => $modal_id]); ?>       

    </form>

    <?php $app->applyHook('mapasculturais.add_entity_modal.form:after'); ?>

</div>