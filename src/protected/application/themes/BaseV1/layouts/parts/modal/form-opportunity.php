<?php
use MapasCulturais\i;
$url = $app->createUrl($entity_name);
$classes = $this->getModalClasses($use_modal);
$name = mb_strtolower($entity_classname::getEntityTypeLabel());
$evaluation_methods = $app->getRegisteredEvaluationMethods();
$title = sprintf(i::__("Crie uma %s com informações básicas"), $name);

$app->applyHook('mapasculturais.add_entity_modal.title', [&$title]);
?>

<?php $this->applyTemplateHook("{$entity_name}-modal", 'before'); ?>
<div id="<?php echo $modal_id; ?>" class="entity-modal <?php echo $classes['classes']; ?>" title="<?php echo $title; ?>" style="display: none">
    <?php $this->applyTemplateHook("{$entity_name}-modal", 'begin'); ?>
    <?php $this->part('modal/before-form'); ?>
    <?php $this->part('modal/feedback', ['entity_name' => $entity_name, 'label' => $name]); ?>

    <form method="POST" class="create-entity <?php echo ($use_modal) ? "" : "is-attached"; ?>" action="<?php echo $url; ?>"
          data-entity="<?php echo $url; ?>" data-formid="<?php echo $modal_id; ?>" id="form-for-<?php echo $modal_id; ?>">
        
       <?php $this->part('modal/field--select', ['entity_classname' => $entity_classname,'evaluation_methods' => $evaluation_methods]); ?>

        <?php $this->renderModalFields($entity_classname, $entity_name, $modal_id); ?>
        
        <?php $this->renderModalRequiredMetadata($entity_classname, $entity_name); ?>
        <?php $this->renderModalTaxonomies($entity_classname, $entity_name); ?>

        <input type="hidden" name="parent_id" value="<?php echo $app->user->profile->id; ?>">
        <?php $this->part('modal/footer', ['entity' => $entity_name]); ?>

        <div class="actions">
            <button type="button" class="btn btn-default <?php echo $classes['cancel_class']; ?>" data-form-id='<?php echo $modal_id; ?>'>
                <?php i::_e("Cancelar"); ?>
            </button>
            <input type="submit" class="btn btn-primary" value="<?php i::_e("Adicionar"); ?> <?php echo $name; ?>">
        </div>
    </form>

    <?php $app->applyHook('mapasculturais.add_entity_modal.form:after'); ?>
    <?php $this->applyTemplateHook("{$entity_name}-modal", 'end'); ?>
</div>
<?php $this->applyTemplateHook("{$entity_name}-modal", 'after'); ?>