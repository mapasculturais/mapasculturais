<?php 
use MapasCulturais\i;
$evaluation_methods = $app->getRegisteredEvaluationMethods();

?>
<div id="entity-opportunities" class="aba-content">
    <ul>
        <?php foreach($entity->opportunities as $opp): ?>
        <li><a href="<?php echo $opp->editUrl ?>"><?php echo $opp->name ?></a></li>
        <?php endforeach; ?>
    </ul>
    
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
    <a class="btn btn-default add" ng-click="editbox.open('new-opportunity', $event)" ><?php i::_e("Criar oportunidade");?></a>
    
</div>