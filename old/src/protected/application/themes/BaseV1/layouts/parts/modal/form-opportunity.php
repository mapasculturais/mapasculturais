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

        <p>
            <label style="margin-left: 15px;">
                <input onclick="selectRadio('js-ownerProject')" name="objectType" type="radio" value="project" checked style="width: auto;">
                Projeto
            </label>

            <label style="margin-left: 15px;">
                <input onclick="selectRadio('js-ownerAgent')" name="objectType" type="radio" value="agent" style="width: auto;">
                Agente
            </label>

            <label style="margin-left: 15px;">
                <input onclick="selectRadio('js-ownerSpace')" name="objectType" type="radio" value="space" style="width: auto;">
                Espaço
            </label>

            <label style="margin-left: 15px;">
                <input onclick="selectRadio('js-ownerEvent')" name="objectType" type="radio" value="event" style="width: auto;">
                Evento
            </label>
        </p>

        <?php $inputHiddenId = uniqid('ownerEntity-'); ?>
        <input type="hidden" name="ownerEntity" id="<?php echo $inputHiddenId; ?>">

        <div class="owner-select">
            <div class="js-ownerAgent select-owner-entity">                
                <label class="js-search js-include-editable"
                    data-field-name='ownerEntity'
                    data-emptytext="<?php i::esc_attr_e("Selecione um agente");?>"
                    data-search-box-width="400px"
                    data-search-box-placeholder="<?php i::esc_attr_e("Selecione um agente");?>"
                    data-entity-controller="agent"
                    data-search-result-template="#agent-search-result-template"
                    data-selection-template="#agent-response-template"
                    data-no-result-template="#agent-response-no-results-template"
                    data-selection-format="changeOwner"
                    data-auto-open="true"
                    data-input-selector="#<?php echo $inputHiddenId; ?>"
                    title="<?php i::esc_attr_e("Repassar propriedade");?>"
                ><?php i::_e("Selecione um agente")?></label>
            </div>

            <div class="js-ownerEvent select-owner-entity">            
                <label class="js-search js-include-editable"
                    data-field-name='ownerEntity'
                    data-emptytext="<?php i::esc_attr_e("Selecione um evento");?>"
                    data-search-box-width="400px"
                    data-search-box-placeholder="<?php i::esc_attr_e("Selecione um evento");?>"
                    data-entity-controller="event"
                    data-search-result-template="#agent-search-result-template"
                    data-selection-template="#agent-response-template"
                    data-no-result-template="#agent-response-no-results-template"
                    data-selection-format="changeOwner"
                    data-auto-open="true"
                    data-input-selector="#<?php echo $inputHiddenId; ?>"
                    title="<?php i::esc_attr_e("Repassar propriedade");?>"
                ><?php i::_e("Selecione um evento")?></label>
            </div>

            <div class="js-ownerSpace select-owner-entity">            
                <label class="js-search js-include-editable"
                    data-field-name='ownerEntity'
                    data-emptytext="<?php i::esc_attr_e("Selecione um espaço");?>"
                    data-search-box-width="400px"
                    data-search-box-placeholder="<?php i::esc_attr_e("Selecione um espaço");?>"
                    data-entity-controller="space"
                    data-search-result-template="#agent-search-result-template"
                    data-selection-template="#agent-response-template"
                    data-no-result-template="#agent-response-no-results-template"
                    data-selection-format="changeOwner"
                    data-auto-open="true"
                    data-input-selector="#<?php echo $inputHiddenId; ?>"
                    title="<?php i::esc_attr_e("Repassar propriedade");?>"
                ><?php i::_e("Selecione um espaço")?></label>
            </div>

            <div class="js-ownerProject select-owner-entity">            
                <label class="js-search js-include-editable"
                    data-field-name='ownerEntity'
                    data-emptytext="<?php i::esc_attr_e("Selecione um projeto");?>"
                    data-search-box-width="400px"
                    data-search-box-placeholder="<?php i::esc_attr_e("Selecione um projeto");?>"
                    data-entity-controller="project"
                    data-search-result-template="#agent-search-result-template"
                    data-selection-template="#agent-response-template"
                    data-no-result-template="#agent-response-no-results-template"
                    data-selection-format="changeOwner"
                    data-auto-open="true"
                    data-input-selector="#<?php echo $inputHiddenId; ?>"
                    title="<?php i::esc_attr_e("Repassar propriedade");?>"
                ><?php i::_e("Selecione um projeto")?></label>
            </div>
        </div>
        <!--<input type="text" name="ownerEntity">-->
        
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
            <button class="btn btn-primary" onclick="saveOpportunity('form-for-<?=$modal_id?>')"><?php i::_e("Adicionar"); ?> <?php echo $name; ?></button>
        </div>
    </form>

    <?php $app->applyHook('mapasculturais.add_entity_modal.form:after'); ?>
    <?php $this->applyTemplateHook("{$entity_name}-modal", 'end'); ?>
</div>
<?php $this->applyTemplateHook("{$entity_name}-modal", 'after'); ?>