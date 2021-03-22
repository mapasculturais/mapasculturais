<?php 
use MapasCulturais\i;

$em = $entity->getEvaluationMethod();

?>
<div ng-controller="ApplyAccountabilityEvaluationResults">
<a class="btn btn-primary hltip"  ng-click="editbox.open('apply-consolidated-results-editbox', $event)"> 
    <?php i::_e('Aplicar pareceres'); ?> 
</a>
  
<edit-box id="apply-consolidated-results-editbox" position="bottom" 
            title="<?php i::esc_attr_e('Aplicar resultado dos pareceres') ?>" 
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
                $eval = $consolidated_result['evaluation'] ?: 'Sem Paracer';
            ?>
                <option value="<?= $consolidated_result['evaluation'] ?>">
                    <?= $em->valueToString($eval) ?> (<?= $consolidated_result['num'] ?> <?php i::_e('Prestações de Contas') ?>)
                </option>
            <?php endforeach ?>
        </select>
    </label>
    <label>
        <?php i::_e('Status da prestação de contas') ?>
        <select ng-model="data.applyTo">
            <option value="0"><?php i::_e('Rascunho') ?></option>
            <option value="1"><?php i::_e('Pendente') ?></option>
            <option value="10"><?php i::_e('Aprovada') ?></option>
            <option value="8"><?php i::_e('Aprovada com ressalvas') ?></option>
            <option value="3"><?php i::_e('Não aprovada') ?></option>
        </select>
    </label>
    <label><input type="checkbox" ng-model="data.status" ng-true-value="'all'" ng-false-value="'pending'"> <?= i::__("Aplicar a todas as prestações de contas enviadas") ?> </label><br>
    <em><?php i::_e('Deixando desmarcado, o status será aplicado somente às prestações de contas com status pendente'); ?></em>
</edit-box>
</div>