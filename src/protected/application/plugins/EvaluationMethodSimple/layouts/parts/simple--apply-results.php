<?php 
use MapasCulturais\i;

$url = $this->controller->createUrl('applyEvaluationsSimple', [$entity->id]);

$em = $entity->getEvaluationMethod();

?>
<div ng-controller="ApplySimpleEvaluationResults">
<a class="btn btn-primary hltip"  ng-click="editbox.open('apply-consolidated-results-editbox', $event)"> 
    <?php i::_e('Aplicar avaliações'); ?> 
</a>
  
<edit-box id="apply-consolidated-results-editbox" position="bottom" 
            title="<?php i::esc_attr_e('Aplicar resultado das avaliações simples') ?>" 
            spinner-condition="data.applying"
            cancel-label="Cancelar" 
            submit-label="<?php i::_e('Aplicar') ?>"
            close-on-cancel="true" 
            on-submit="applyEvaluations">

    <label>
        <?php i::_e('Avaliação') ?>
        <select ng-model="data.applyFrom">
            <?php 
            foreach($consolidated_results as $consolidated_result): 
                $eval = $consolidated_result['evaluation'] ?: 'Sem Avaliações';
            ?>
                <option value="<?= $consolidated_result['evaluation'] ?>">
                    <?= $em->valueToString($eval) ?> (<?= $consolidated_result['num'] ?> <?php i::_e('Inscrições') ?>)
                </option>
            <?php endforeach ?>
        </select>
    </label>
    <label>
        <?php i::_e('Status') ?>
        <select ng-model="data.applyTo">
            <option value="0"><?php i::_e('Rascunho') ?></option>
            <option value="1"><?php i::_e('Pendente') ?></option>
            <option ng-repeat="status in data.registrationStatusesNames" value="{{status.value}}">{{status.label}}</option>
        </select>
    </label>
    <label><input type="checkbox" ng-model="data.status" ng-true-value="'all'" ng-false-value="'pending'"> <?= i::__("Aplicar a todas as inscrições enviadas") ?> </label><br>
    <em><?php i::_e('Deixando desmarcado, o status será aplicado somente às inscrições com status pendente'); ?></em>
</edit-box>
</div>