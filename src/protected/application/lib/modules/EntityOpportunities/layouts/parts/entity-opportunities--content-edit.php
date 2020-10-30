<?php 
use MapasCulturais\i;
use MapasCulturais\Entities\Opportunity;

$evaluation_methods = $app->getRegisteredEvaluationMethods();

?>
<?php $this->applyTemplateHook('entity-opportunities','before'); ?>
<div id="entity-opportunities" class="aba-content">
    <?php $this->applyTemplateHook('entity-opportunities','begin'); ?>

    <section class="highlighted-message clearfix">
        <p><?php i::_e("Configurações da aba Oportunidade") ?></p>
        <span class="label"><?php \MapasCulturais\i::_e("Utilizar a aba de oportunidades?");?>:</span>
        <span class="js-editable" data-edit="useOpportunityTab" data-type="select" data-value="<?php echo $entity->useOpportunityTab ?>" data-original-title="<?php i::esc_attr_e("Utilizar a aba de oportunidades?"); ?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Sim");?>"><?php echo $entity->useOpportunityTab ? $entity->useOpportunityTab : '1'; ?></span>
        <br>
        <span class="label"><?php \MapasCulturais\i::_e("Nome da aba");?>:</span>
        <span class="js-editable" data-edit="opportunityTabName" data-original-title="<?php i::esc_attr_e("Título da aba"); ?>" data-emptytext="<?php i::esc_attr_e("Deixe em branco para utilizar a padrão");?>"><?php echo $entity->opportunityTabName; ?></span>
    </section>
    
    <edit-box id="new-opportunity" position="right" title="<?php i::esc_attr_e('Escolha o método de avaliação da oportunidade') ?>"  cancel-label="<?php i::esc_attr_e("Cancelar");?>" close-on-cancel="true">
        <ul class="evaluation-methods">
            <?php foreach($evaluation_methods as $method): ?>
            <li class="evaluation-methods--item">
                <a href="<?php echo $this->controller->createUrl('createOpportunity', [$entity->id, 'evaluationMethod' => $method->slug]) ?>">
                    <span class="evaluation-methods--name"><?php echo $method->name; ?></span>
                    <p class="evaluation-methods--name"><?php echo $method->description; ?></p>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </edit-box>
    <p>
        <a class="btn btn-default add" ng-click="editbox.open('new-opportunity', $event)"  rel='noopener noreferrer'><?php i::_e("Criar oportunidade");?></a>
    </p>
    <?php foreach($entity->getOpportunities(Opportunity::STATUS_DRAFT) as $opportunity): ?>
        <?php $this->part('entity-opportunities--item', ['opportunity' => $opportunity, 'entity' => $entity]) ?>
    <?php endforeach; ?>

    <?php $this->applyTemplateHook('entity-opportunities','end'); ?>
</div>
<?php $this->applyTemplateHook('entity-opportunities','after'); ?>
