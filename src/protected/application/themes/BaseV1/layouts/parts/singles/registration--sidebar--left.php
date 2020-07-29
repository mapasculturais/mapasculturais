<?php
use MapasCulturais\i;
?>
<div class="sidebar-left sidebar registration">
    <?php if($action === 'single' && !$opportunity->publishedRegistrations && $entity->canUser('viewUserEvaluation')): ?>

        <div ng-controller="RegistrationListController" id="registrations-list-container">
            <h4><?php i::_e('Inscrições'); ?></h4>

            <div><label><input type="checkbox" ng-model="data.pending" /> <?php i::_e('Somente avaliações pendentes'); ?></label></div>
            <input ng-model="data.keyword" ng-model-options="{ debounce: 333 }" id="registrations-list--filter" placeholder="<?php i::_e('Filtre pelo nome do agente'); ?>" />


            <!-- <div style="overflow:scroll; min-height:250px; max-height:250px;" class="modal-body container" id="container">
                <div infinite-scroll='loadMore()' infinite-scroll-distance='2' infinite-scroll-parent='true' infinite-scroll-container="'#container'">
                <img ng-repeat='image in images' ng-src='http://placehold.it/225x250&text={{image}}' />
            </div> -->

            <!-- <div style="overflow:scroll; min-height:250px; max-height:250px;" class="modal-body container" id="container"> -->
            <div class="registrations-list" id="container" >
                <ul infinite-scroll='loadMore()' infinite-scroll-distance='2' infinite-scroll-parent='true' infinite-scroll-container="'#container'">
                    <li ng-repeat="registration in data.registrations" ng-show="show(registration)" class="registration-item" ng-class="{
                        current: registration.id == data.current,
                        visible:show(registration),
                        missing: !evaluated(registration),
                        valid: getEvaluationResult(registration) === '1',
                        invalid: getEvaluationResult(registration) === '-1'
                    }">
                        <a href="{{::registration.singleUrl}}">
                            <div class="registration-evaluated"> (<?php i::_e('Avaliação:'); ?> <strong> {{status_str(registration)}}</strong>) </div>
                            <div class="registration-number">{{::registration.number}}</div>
                            <div class="registration-owner">{{::registration.owner.name}}</div>
                            <div ng-if="registration.category" class="registration-category">{{::registration.category}}</div>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- referencia do infinity scroll : https://embed.plnkr.co/plunk/PiWpFW -->
            <!-- <ul id="registrations-list" class="registrations-list" infinite-scroll='loadMoreEvaluations()' infinite-scroll-distance='3' infinite-scroll-container='"#registrations-list"' infinite-scroll-parent='true'>
                <li ng-repeat="registration in data.registrations" ng-show="show(registration)" class="registration-item"

                    ng-class="{
                        current: registration.id == data.current,
                        visible:show(registration),
                        missing: !evaluated(registration),
                        valid: getEvaluationResult(registration) === '1',
                        invalid: getEvaluationResult(registration) === '-1'
                        }">
                    <a href="{{::registration.singleUrl}}">
                        <div class="registration-evaluated"> (<?php i::_e('Avaliação:'); ?> <strong> {{status_str(registration)}}</strong>) </div>
                        <div class="registration-number">{{::registration.number}}</div>
                        <div class="registration-owner">{{::registration.owner.name}}</div>
                        <div ng-if="registration.category" class="registration-category">{{::registration.category}}</div>
                    </a>
                </li>
            </ul> -->
        </div>

    <?php endif; ?>
</div>
