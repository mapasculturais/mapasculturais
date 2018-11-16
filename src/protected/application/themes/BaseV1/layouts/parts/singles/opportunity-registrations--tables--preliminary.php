<?php use MapasCulturais\i; ?>
<?php if ($entity->publishedPreliminaryRegistrations): ?>
<!--#preliminary-results-->
<div id="preliminaryresults" class="aba-content">

    <div class="highlighted-message clearfix" ng-if="data.registrationsAPIMetadata.count > 0">
        <?php i::_e('O resultado poderá sofrer alterações até a publicação do resultado oficial.'); ?>
    </div>
    <table class="js-registration-list registrations-table published-registration-table" ng-class="{'no-options': data.entity.registrationCategories.length === 0, 'no-attachments': data.entity.registrationFileConfigurations.length === 0, 'registrations-results': data.entity.published}"><!-- adicionar a classe registrations-results quando resultados publicados-->
        <thead>
        <tr>
            <th class="registration-id-col">
                <?php \MapasCulturais\i::_e("Inscrição");?>
            </th>
            <th ng-if="data.entity.registrationCategories" class="registration-option-col">
                <?php i::_e('categoria') ?>
            </th>
            <th class="registration-agents-col">
                <?php \MapasCulturais\i::_e("Agentes");?>
            </th>
            <th class="registration-status-col">
                <?php \MapasCulturais\i::_e("Status");?>
            </th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td colspan='4'>
                <span ng-if="!usingRegistrationsFilters() && getCountPreliminaryResultRegistrations() === 0"><?php \MapasCulturais\i::_e("Nenhuma inscrição.");?></span>
                <span ng-if="!usingRegistrationsFilters() && getCountPreliminaryResultRegistrations() === 1"><?php \MapasCulturais\i::_e("1 inscrição.");?></span>
                <span ng-if="!usingRegistrationsFilters() && getCountPreliminaryResultRegistrations() > 1">{{getCountPreliminaryResultRegistrations()}} <?php \MapasCulturais\i::_e("inscrições.");?></span>
            </td>
        </tr>

        <tr ng-repeat="reg in data.registrations" id="preliminary-registration-{{reg.id}}" class="{{getStatusSlug(reg.publishedPreliminaryRevision.status)}}" ng-if="hasPreliminaryResultRegistrations(reg)" >
            <td class="registration-id-col"><strong>{{reg.publishedPreliminaryRevision.number}}</strong></td>
            <td ng-if="data.entity.registrationCategories" class="registration-option-col">{{reg.publishedPreliminaryRevision.category}}</td>
            <td class="registration-agents-col">
                <p>
                    <span class="label"><?php \MapasCulturais\i::_e("Responsável");?></span><br />
                    <a href="{{reg.publishedPreliminaryRevision.owner.singleUrl}}">{{reg.publishedPreliminaryRevision.owner.name}}</a>
                </p>

                <p ng-repeat="relation in reg.agentRelations" ng-if="relation.agent">
                    <span class="label">{{relation.label}}</span><br />
                    <a href="{{relation.agent.singleUrl}}">{{relation.agent.name}}</a>
                </p>
            </td>

            <td class="registration-status-col">
                <span class="status status-{{getStatusSlug(reg.publishedPreliminaryRevision.status)}}"> {{getStatusNameById(reg.publishedPreliminaryRevision.status)}} </span>
            </td>
        </tr>

        </tbody>
    </table>

</div>
<?php endif; ?>
<!--#preliminary-results-->