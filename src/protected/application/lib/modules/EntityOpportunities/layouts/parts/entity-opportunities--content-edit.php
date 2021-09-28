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
        
    <?php foreach($entity->getOpportunities(Opportunity::STATUS_DRAFT) as $opportunity): ?>
        <?php $this->part('entity-opportunities--item', ['opportunity' => $opportunity, 'entity' => $entity]) ?>
    <?php endforeach; ?>

    <?php $this->applyTemplateHook('entity-opportunities','end'); ?>
</div>
<?php $this->applyTemplateHook('entity-opportunities','after'); ?>
