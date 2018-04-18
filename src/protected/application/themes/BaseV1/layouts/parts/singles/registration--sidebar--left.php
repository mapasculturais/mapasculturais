<?php
use MapasCulturais\i;
?>
<div class="sidebar-left sidebar registration">
    <?php if($action === 'single' && !$opportunity->publishedRegistrations && $entity->canUser('viewUserEvaluation')): ?>

        <div ng-controller="RegistrationListController" id="registrations-list-container">
            <h4><?php i::_e('Inscrições'); ?></h4>

            <div><label><input type="checkbox" ng-model="data.pending" /> <?php i::_e('Somente avaliações pendentes'); ?></label></div>
            <input ng-model="data.keyword" ng-model-options="{ debounce: 333 }" id="registrations-list--filter" placeholder="<?php i::_e('Filtre pelo nome do agente'); ?>" />
            <ul id="registrations-list" class="registrations-list">
                <li ng-repeat="registration in data.registrations" ng-show="show(registration)" class="registration-item"
                    ng-class="{current: registration.id == data.current, visible:show(registration), missing: !evaluated(registration)}">

                    <a href="{{::registration.singleUrl}}">
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
