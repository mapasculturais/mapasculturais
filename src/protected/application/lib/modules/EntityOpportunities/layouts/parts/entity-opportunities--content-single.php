<?php 
use MapasCulturais\i;
$evaluation_methods = $app->getRegisteredEvaluationMethods();

?>
<div id="entity-opportunities" class="aba-content">
    <ul>
        <?php foreach($entity->opportunities as $opp): ?>
        <li><a href="<?php echo $opp->singleUrl ?>"><?php echo $opp->name ?></a></li>
        <?php endforeach; ?>
    </ul>
</div>