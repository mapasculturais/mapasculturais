<?php $this->addOpportunityEvaluationsToJs($entity); ?>

<header id="header-inscritos" class="clearfix">
    <h3><?php \MapasCulturais\i::_e("Avaliações");?></h3>
    <!--<a class="btn btn-default download" href="<?php echo $this->controller->createUrl('report', [$entity->id]); ?>"><?php \MapasCulturais\i::_e("Baixar lista de avaliações");?></a>-->
</header>

<table class="js-registration-list registrations-table" ng-class="{'no-options': data.entity.registrationCategories.length === 0, 'no-attachments': data.entity.registrationFileConfigurations.length === 0, 'registrations-results': data.entity.published}"><!-- adicionar a classe registrations-results quando resultados publicados-->
    <thead>
        <tr>
            <th class="registration-agents-col">
                <?php \MapasCulturais\i::_e("Avaliador");?>
            </th>
            <th class="registration-id-col">
                <?php \MapasCulturais\i::_e("Inscrição");?>
            </th>
            <th ng-if="data.entity.registrationCategories" class="registration-option-col">
                <mc-select placeholder="status" model="data.registrationCategory" data="data.registrationCategoriesToFilter"></mc-select>
            </th>
            <th class="registration-agents-col">
                <?php \MapasCulturais\i::_e("Agente Responsável");?>
            </th>
            <th class="registration-status-col">
                <?php \MapasCulturais\i::_e("Status / Avaliação");?>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan='5'>
                <span ng-if="!usingFilters() && getFilteredRegistrations().length === 0"><?php \MapasCulturais\i::_e("Nenhuma avaliação enviada.");?></span>
                <span ng-if="usingFilters() && getFilteredRegistrations().length === 0"><?php \MapasCulturais\i::_e("Nenhuma avaliação encontrada com os filtros selecionados.");?></span>
                <span ng-if="!usingFilters() && getFilteredRegistrations().length === 1"><?php \MapasCulturais\i::_e("1 avaliação enviada.");?></span>
                <span ng-if="usingFilters() && getFilteredRegistrations().length === 1"><?php \MapasCulturais\i::_e("1 avaliação encontrada com os filtros selecionados.");?></span>
                <span ng-if="!usingFilters() && getFilteredRegistrations().length > 1">{{data.opportunityEvaluations.length}} <?php \MapasCulturais\i::_e("Avaliações.");?>
                    <?php if($entity->registrationLimit > 0):?>
                         | <?php \MapasCulturais\i::_e("Número máximo de vagas na oportunidade:");?> <?php echo $entity->registrationLimit;?>
                    <?php endif;?>
                </span>
                <span ng-if="usingFilters() && getFilteredRegistrations().length > 1">{{getFilteredRegistrations().length}} <?php \MapasCulturais\i::_e("inscrições encontradas com os filtros selecionados.");?></span>
            </td>
        </tr>
        <tr ng-repeat="evaluation in ::data.opportunityEvaluations" id="registration-{{::evaluation.registration.id}}" class="{{getStatusSlug(evaluation.registration.status)}}" ng-show="showRegistration(evaluation.registration)" >
            <td class="registration-id-col"><strong>{{::evaluation.valuer.name}}</strong></td>
            <td class="registration-id-col"><a href='{{::evaluation.evaluation.singleUrl}}'>{{::evaluation.registration.number}}</a></td>
            <td ng-if="data.entity.registrationCategories" class="registration-option-col">{{::evaluation.registration.category}}</td>
            <td class="registration-agents-col">
                <p>
                    <span class="label"><?php \MapasCulturais\i::_e("Responsável");?></span><br />
                    <a href="{{::evaluation.registration.owner.singleUrl}}">{{::evaluation.registration.owner.name}}</a>
                </p>
            </td>
            <td class="registration-status-col">
                <div ng-show="::getEvaluationResultString(evaluation)">
                    <strong>{{::getEvaluationStatusLabel(evaluation)}} / {{::getEvaluationResultString(evaluation)}}</strong>
                </div>
                <div ng-hide="::getEvaluationResultString(evaluation)">
                    {{::getEvaluationStatusLabel(evaluation)}}
                </div>
            </td>
        </tr>
    </tbody>
</table>
