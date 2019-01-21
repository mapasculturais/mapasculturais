<?php
    use MapasCulturais\i;
    $uidParam = (isset($entity->controller->urlData['uid'])) ? 'uid:' . $entity->controller->urlData['uid'] : null ;
    $configuration = $opportunity->evaluationMethodConfiguration;
    $definition = $configuration->definition;
    $evaluationMethod = $definition->evaluationMethod;
    $evaluationMethodResultStatusList = $evaluationMethod->getResultStatusList();
    $this->jsObject['evaluationMethodResultStatusList'] = $evaluationMethodResultStatusList;
?>
<div class="sidebar-left sidebar registration">

    <?php if($action === 'single' && !$opportunity->publishedRegistrations && $entity->canUser('viewUserEvaluation')): ?>

    <div ng-controller="RegistrationListController" id="registrations-list-container">
        <h4><?php i::_e('Inscrições'); ?></h4>
        <div class="registrations-list-filter">
            <fieldset>
                <legend><?php i::_e('Filtrar inscrições'); ?></legend>
                <div>
                    <input ng-model="data.keyword" ng-model-options="{ debounce: 333 }"  class="registrations-list-filter-name" placeholder="<?php i::_e('N° de inscrição ou Nome') ?>"/>
                    <mc-select  model="data.evaluationStatusFilter" data="data.evaluationStatus" placeholder="<?php i::_e('Status') ?>"></mc-select>
                </div>
            </fieldset>
        </div>
        <hr/>     
        <ul id="registrations-list" class="registrations-list">
            <li ng-repeat="registration in data.registrations" ng-show="show(registration)" class="registration-item"

                ng-class="{
                    current: registration.id == data.current,
                    visible:show(registration),
                    missing: !evaluated(registration),
                    valid: getEvaluationResult(registration) === '1',
                    invalid: getEvaluationResult(registration) === '-1'
                    }">
                <a href="{{::registration.singleUrl}}<?php echo $uidParam; ?>">
                    <div class="registration-evaluated"> (<?php i::_e('Avaliação:'); ?> <strong> {{status_str(registration)}}</strong>) </div>
                    <div class="registration-number">{{::registration.number}}</div>
                    <div class="registration-owner">{{::registration.owner.name}}</div>
                    <div ng-if="registration.category" class="registration-category">{{::registration.category}}</div>
                </a>
            </li>
        </ul>
    </div>

    <?php endif; ?>

</div>
