<?php
    $url = $app->createUrl($entity);
    $use_modal = true;
    $classes = $this->getModalClasses($use_modal);

    $_entity_class = $app->controller($entity)->entityClassName;
    $new_entity = new $_entity_class();

    $name = mb_strtolower($new_entity->getEntityTypeLabel());

    $title = "Criar $name com informações básicas";
    $app->applyHook('mapasculturais.add_entity_modal.title', [&$title]);
?>

<div id="<?php echo $id; ?>" class="entity-modal <?php echo $classes['classes']; ?>" title="<?php echo $title; ?>" style="display: none">

    <?php $this->part('modal/before-form'); ?>

    <?php $this->part('modal/feedback', ['entity' => $entity, 'label' => $name]); ?>

    <form method="POST" class="create-entity <?php echo ($use_modal) ? "" : "is-attached"; ?>" action="<?php echo $url; ?>"
          data-entity="<?php echo $url; ?>" data-formid="<?php echo $id; ?>" id="form-for-<?php echo $id; ?>">

        <?php $this->renderFields($entity,$new_entity,$id); ?>

        <input type="hidden" name="parent_id" value="<?php echo $app->user->profile->id; ?>">

        <?php $this->part('modal/footer', ['entity' => $entity]); ?>

        <div class="actions">
            <button type="button" class="btn btn-default <?php echo $classes['cancel_class']; ?>" data-form-id='<?php echo $id; ?>'>
                <?php \MapasCulturais\i::_e("Cancelar");?>
            </button>
            <input type="submit" class="btn btn-primary" value="Adicionar <?php echo $name; ?>">
        </div>

    </form>

    <?php $app->applyHook('mapasculturais.add_entity_modal.form:after'); ?>

</div>