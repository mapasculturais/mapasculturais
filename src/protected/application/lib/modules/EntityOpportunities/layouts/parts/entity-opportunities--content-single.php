<?php 
use MapasCulturais\i;
use MapasCulturais\Entities\Opportunity;
if($entity->opportunities && ($entity->useOpportunityTab !== 'false')): 

    $evaluation_methods = $app->getRegisteredEvaluationMethods();

?>

<?php $this->applyTemplateHook('entity-opportunities','before'); ?>
<div id="entity-opportunities" class="aba-content ">
    <?php $this->applyTemplateHook('entity-opportunities','begin'); ?>
    <?php foreach($entity->opportunities as $opportunity): ?>
        <?php $this->part('entity-opportunities--item', ['opportunity' => $opportunity, 'entity' => $entity]) ?>
    <?php endforeach; ?>
    <?php $this->applyTemplateHook('entity-opportunities','end'); ?>
</div>
<?php $this->applyTemplateHook('entity-opportunities','after'); ?>

<?php endif; ?>