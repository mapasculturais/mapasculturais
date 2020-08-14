<?php
use MapasCulturais\i;
?>
<p ng-if="data.registrationsAPIMetadata.count > 0">
    <?php i::_e('Veja abaixo as inscrições suplentes/selecionadas'); ?>
</p>
<table class="js-registration-list registrations-table published-registration-table" ng-class="{'no-options': data.entity.registrationCategories.length === 0, 'no-attachments': data.entity.registrationFileConfigurations.length === 0, 'registrations-results': data.entity.published}"><!-- adicionar a classe registrations-results quando resultados publicados-->
    <thead>
        <tr>
            <th class="registration-id-col">
                <?php \MapasCulturais\i::_e("Inscrição");?>
            </th>
            <th ng-if="data.entity.registrationCategories" class="registration-option-col">
                <mc-select placeholder="<?php i::_e('categoria') ?>" model="registrationsFilters['category']" data="data.registrationCategoriesToFilter"></mc-select>
            </th>
            <th class="registration-agents-col">
                <?php \MapasCulturais\i::_e("Agentes");?>
            </th>
            <th class="registration-status-col">
                <mc-select placeholder="status" model="registrationsFilters['status']" data="data.publishedRegistrationStatuses"></mc-select>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan='5'>
                <span ng-if="!usingRegistrationsFilters() && data.registrationsAPIMetadata.count === 0"><?php \MapasCulturais\i::_e("Nenhuma inscrição.");?></span>
                <span ng-if="usingRegistrationsFilters() && data.registrationsAPIMetadata.count === 0"><?php \MapasCulturais\i::_e("Nenhuma inscrição encontrada com os filtros selecionados.");?></span>
                <span ng-if="!usingRegistrationsFilters() && data.registrationsAPIMetadata.count === 1"><?php \MapasCulturais\i::_e("1 inscrição.");?></span>
                <span ng-if="usingRegistrationsFilters() && data.registrationsAPIMetadata.count === 1"><?php \MapasCulturais\i::_e("1 inscrição encontrada com os filtros selecionados.");?></span>
                <span ng-if="!usingRegistrationsFilters() && data.registrationsAPIMetadata.count > 1">                    
                    {{data.registrations.length}} <i> de {{ data.registrationsAPIMetadata.count }}</i> <?php \MapasCulturais\i::_e("inscrições.");?>
                </span>
                <span ng-if="usingRegistrationsFilters() && data.registrationsAPIMetadata.count > 1">{{data.registrationsAPIMetadata.count}} <?php \MapasCulturais\i::_e("inscrições encontradas com os filtros selecionados.");?></span>
            </td>
        </tr>

        <tr ng-repeat="reg in data.registrations" id="registration-{{reg.id}}" class="{{getStatusSlug(reg.status)}}" ng-if="reg.status == 10 || reg.status == 8">
            <td class="registration-id-col"><strong>{{reg.number}}</strong></td>
            <td ng-if="data.entity.registrationCategories" class="registration-option-col">{{reg.category}}</td>
            <td class="registration-agents-col">
                <p>
                    <span class="label"><?php \MapasCulturais\i::_e("Responsável");?></span><br />
                    <a href="{{reg.owner.singleUrl}}" rel='noopener noreferrer'>{{reg.owner.name}}</a>
                </p>

                <p ng-repeat="relation in reg.agentRelations" ng-if="relation.agent">
                    <span class="label">{{relation.label}}</span><br />
                    <a href="{{relation.agent.singleUrl}}" rel='noopener noreferrer'>{{relation.agent.name}}</a>
                </p>
            </td>

            <td class="registration-status-col">
                <span class="status status-{{getStatusSlug(reg.status)}}"> {{getStatusNameById(reg.status)}} </span>
            </td>
        </tr>

    </tbody>
</table>
