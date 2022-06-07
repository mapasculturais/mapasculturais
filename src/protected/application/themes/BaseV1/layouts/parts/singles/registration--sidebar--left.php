<?php
use MapasCulturais\i;
?>
<div class="sidebar-left sidebar registration">
    <?php if($action === 'single' && !$opportunity->publishedRegistrations && $entity->canUser('viewUserEvaluation')): ?>

        <div ng-controller="RegistrationListController" id="registrations-list-container">
            <h4><?php i::_e('Inscrições'); ?></h4>

            <div><label><input type="checkbox" ng-model="data.pending" /> <?php i::_e('Somente avaliações pendentes'); ?></label></div>
            <input ng-model="data.keyword" ng-model-options="{ debounce: 333 }" id="registrations-list--filter" placeholder="<?php i::_e('Filtre pelo nome do agente'); ?>" />

            <div class="registrations-list" id="container" >
                <ul infinite-scroll='loadMore()' infinite-scroll-distance='2' infinite-scroll-parent='true' infinite-scroll-container="'#container'">
                    <!-- <li ng-repeat="registration in data.registrations" ng-show="show(registration)" class="registration-item" ng-class="{ -->
                    <li ng-repeat="registration in registrationAndEvaluations" ng-show="newShow(registration)" class="registration-item" ng-class="{
                        current: registration.id == data.current,
                        visible: newShow(registration),
                        missing: !newEvaluated(registration),
                        valid: getEvaluationResult(registration) === '1',
                        invalid: getEvaluationResult(registration) === '-1'
                    }">
                        <a href="{{::registration.singleUrl}}" rel='noopener noreferrer'>
                            <div class="registration-evaluated"> (<?php i::_e('Avaliação:'); ?> <strong> {{registrationStatus(registration)}}</strong>) </div>
                            <div class="registration-number">{{::registration.number}}</div>
                            <div class="registration-owner" ng-if="data.avaliableEvaluationFields.agentsSummary">{{::registration.owner.name}}</div>
                            <div ng-if="registration.category && data.avaliableEvaluationFields.category" class="registration-category">{{::registration.category}}</div>
                        </a>
                    </li>
                </ul>
            </div>

        </div>

    <?php endif; ?>
</div>
