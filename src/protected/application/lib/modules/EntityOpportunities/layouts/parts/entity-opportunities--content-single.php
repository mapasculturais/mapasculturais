<?php 
use MapasCulturais\i;
use MapasCulturais\Entities\Opportunity;
if($entity->opportunities && ($entity->useOpportunityTab !== 'false')): 

    $evaluation_methods = $app->getRegisteredEvaluationMethods();

?>
<div id="entity-opportunities" class="aba-content ">
    <?php foreach($entity->opportunities as $opportunity): ?>
        <?php $this->part('entity-opportunities--item', ['opportunity' => $opportunity, 'entity' => $entity]) ?>
    <?php endforeach; ?>
</div>
<?php endif; ?>