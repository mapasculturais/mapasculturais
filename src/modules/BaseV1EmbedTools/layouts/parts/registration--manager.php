<?php

use MapasCulturais\i;

?>
<div id="filtro-inscritos">
    <span class="label"> <?php i::_e("Filtrar inscrição:"); ?> </span>
    <input ng-model="data.registrationsFilter" placeholder="<?php i::_e('Busque pelo número de inscrição, status da avaliação, nome ou cpf do responsável') ?>" />
    <label class="label-removeDraft">
        <input type="checkbox" ng-model="data.removeDraft">
        <?php i::_e("Remover rascunhos"); ?>
    </label>
</div>

<div class="dropdown" style="width:100%; margin:10px 0px;">
    <div class="placeholder" ng-click="filter_dropdown = ''"><?php i::_e("Colunas Habilitadas:") ?></div>
    <div class="submenu-dropdown" style="background: #fff;">
        <div class="filter-search" style="padding: 5px;">
            <input type="text" ng-model="filter_dropdown" style="width:100%;" placeholder="Busque pelo nome dos campos do formulário de inscrição e selecione as colunas visíveis" />
        </div>
        <ul class="filter-list">
            <li ng-repeat="field in data.defaultSelectFields | filter:filter_dropdown" ng-if="field.required" ng-class="{'selected':isSelected(data.registrationTableColumns, field.fieldName)}" ng-click="toggleSelectionColumn(data.registrationTableColumns, field.fieldName)">
                <span>{{field.title}}</span>
            </li>
            <li ng-repeat="field in data.opportunitySelectFields | filter:filter_dropdown" ng-if="field.required" ng-class="{'selected':isSelected(data.registrationTableColumns, field.fieldName)}" ng-click="toggleSelectionColumn(data.registrationTableColumns, field.fieldName)">
                <span>{{field.title}}</span>
            </li>
        </ul>
    </div>
</div>


<div id="selected-filters" style="width:100%; margin:10px 0px;">
    <span>
        <a ng-repeat="field in data.defaultSelectFields" ng-click="toggleSelectionColumn(data.registrationTableColumns, field.fieldName)" class="tag-selected tag-opportunity" ng-if="isSelected(data.registrationTableColumns, field.fieldName)" rel='noopener noreferrer'>{{field.title}}</a>
        <a ng-repeat="field in data.opportunitySelectFields" ng-click="toggleSelectionColumn(data.registrationTableColumns, field.fieldName)" class="tag-selected tag-opportunity" ng-if="isSelected(data.registrationTableColumns, field.fieldName)" rel='noopener noreferrer'>{{field.title}}</a>
    </span>
</div>


<style>
    table.fullscreen {
        background-color: white;
    }
</style>
<div id="registrations-table-container">
    <table id="registrations-table" class="js-registration-list registrations-table" ng-class="{'no-options': data.entity.registrationCategories.length === 0, 'no-attachments': data.entity.registrationFileConfigurations.length === 0, 'registrations-results': data.entity.published, 'fullscreen': data.fullscreenTable}"><!-- adicionar a classe registrations-results quando resultados publicados-->
        <thead>
            <tr>
                <?php $this->applyTemplateHook('registration-list-header', 'begin'); ?>
                <th ng-show="data.registrationTableColumns.number" class="registration-id-col">
                    <?php i::_e("Inscrição"); ?>
                </th>
                <th ng-show="data.registrationTableColumns.category" ng-if="data.entity.registrationCategories" class="registration-option-col" title="{{data.registrationCategory}}">
                    <mc-select class="left transparent-placeholder" placeholder="status" model="registrationsFilters['category']" data="data.registrationCategoriesToFilter" title="{{data.registrationCategory}}"></mc-select>
                </th>
                <th ng-repeat="field in data.opportunitySelectFields" ng-show="data.registrationTableColumns[field.fieldName]" class="registration-option-col">
                    <mc-select class="left transparent-placeholder" placeholder="{{field.title}}" model="registrationsFilters[field.fieldName]" data="field.options" title="{{field.title}}"></mc-select>
                </th>
                <th ng-show="data.registrationTableColumns.agents" class="registration-agents-col">
                    <?php i::_e("Agentes"); ?>
                </th>
                <th ng-show="data.registrationTableColumns.attachments" ng-if="data.entity.registrationFileConfigurations.length > 0" class="registration-attachments-col">
                    <?php i::_e("Anexos"); ?>
                </th>
                <th ng-show="data.registrationTableColumns.evaluation" class="registration-status-col">
                    <?php i::_e("Avaliação"); ?>
                </th>
                <th ng-show="data.registrationTableColumns.status" class="registration-status-col">
                    <mc-select placeholder="Status" model="registrationsFilters['status']" data="data.registrationStatusesToFilter"></mc-select>
                </th>

                <?php $this->applyTemplateHook('registration-list-header', 'end'); ?>
            </tr>
        </thead>
        <tr class="table-info">
            <td colspan='{{numberOfEnabledColumns()}}'>                
                <span ng-if="!usingRegistrationsFilters() && data.registrationsAPIMetadata.count === 0"><?php i::_e("Nenhuma inscrição."); ?></span>
                <span ng-if="usingRegistrationsFilters() && data.registrationsAPIMetadata.count === 0"><?php i::_e("Nenhuma inscrição encontrada com os filtros selecionados."); ?></span>
                <span ng-if="!usingRegistrationsFilters() && data.registrationsAPIMetadata.count === 1"><?php i::_e("1 inscrição."); ?></span>
                <span ng-if="usingRegistrationsFilters() && data.registrationsAPIMetadata.count === 1"><?php i::_e("1 inscrição encontrada com os filtros selecionados."); ?></span>
                <span ng-if="!usingRegistrationsFilters() && data.registrationsAPIMetadata.count > 1">
                    {{data.registrations.length}} <i> de {{ data.registrationsAPIMetadata.count }}</i> <?php i::_e("inscrições."); ?>
                    <?php if ($entity->registrationLimit > 0) : ?>
                        | <?php i::_e("Número máximo de vagas na oportunidade:"); ?> <?php echo $entity->registrationLimit; ?>
                    <?php endif; ?>
                </span>
                <div ng-if="usingRegistrationsFilters() && data.registrationsAPIMetadata.count > 1">
                    <div ng-if="data.registrations.length === 0">
                        <?php i::_e("Nenhuma inscrição encontrada com os filtros selecionados."); ?>
                    </div>
                    <div ng-if="data.registrations.length >= 1 ">
                        <strong> {{ data.registrations.length }} </strong>
                        <span ng-if="data.registrationsAPIMetadata.count > 1"> de {{ data.registrationsAPIMetadata.count }}</i> </span>
                        <span ng-if="data.registrations.length === 1"> <?php i::_e("inscrição encontrada"); ?> </span>
                        <span ng-if="data.registrations.length > 1"> <?php i::_e("inscrições encontradas"); ?> </span>
                        <?php i::_e(" com os filtros selecionados."); ?>
                    </div>

                </div>
            </td>
        </tr>
        <tbody>
            <tr ng-repeat="reg in data.registrations" id="registration-{{reg.id}}" ng-class="getStatusSlug(reg.status)">
                <?php $this->applyTemplateHook('registration-list-item', 'begin'); ?>
                <td ng-show="data.registrationTableColumns.number" class="registration-id-col"><a href="{{reg.singleUrl}}" rel='noopener noreferrer'>{{reg.number}}</a></td>
                <td ng-show="data.registrationTableColumns.category" ng-if="data.entity.registrationCategories" class="registration-option-col">{{reg.category}}</td>
                <td ng-repeat="field in data.opportunitySelectFields" ng-if="data.registrationTableColumns[field.fieldName]" class="registration-option-col">
                    {{reg[field.fieldName]}}
                </td>
                <td ng-show="data.registrationTableColumns.agents" class="registration-agents-col">
                    <p>
                        <span class="label"><?php i::_e("Responsável"); ?></span><br />
                        <a href="{{reg.owner.singleUrl}}" rel='noopener noreferrer'>{{reg.owner.name}}</a>
                    </p>

                    <p ng-repeat="relation in reg.agentRelations" ng-if="relation.agent">
                        <span class="label">{{relation.label}}</span><br />
                        <a href="{{relation.agent.singleUrl}}" rel='noopener noreferrer'>{{relation.agent.name}}</a>
                    </p>
                </td>
                <td ng-show="data.registrationTableColumns.attachments" ng-if="data.entity.registrationFileConfigurations.length > 0" class="registration-attachments-col">
                    <a ng-if="reg.files.zipArchive.url" class="icon icon-download" href="{{reg.files.zipArchive.url}}" rel='noopener noreferrer'>
                        <div class="screen-reader-text"><?php i::_e("Baixar arquivos"); ?></div>
                    </a>
                </td>
                <td ng-show="data.registrationTableColumns.evaluation" class="registration-status-col">
                    {{reg.evaluationResultString}}
                </td>

                <td ng-show="data.registrationTableColumns.status" class="registration-status-col">
                    <?php if ($entity->publishedRegistrations) : ?>
                        <span class="status status-{{getStatusSlug(reg.status)}}">{{getStatusNameById(reg.status)}}</span>
                    <?php else : ?>
                        <mc-select model="reg" data="data.registrationStatusesNames" getter="getRegistrationStatus" setter="setRegistrationStatus"></mc-select>
                    <?php endif; ?>
                </td>
                <?php $this->applyTemplateHook('registration-list-item', 'end'); ?>
            </tr>
        </tbody>
        <tfoot  ng-if="data.registrationsAPIMetadata.count > data.registrations.length">
            <tr>
                <td colspan='{{numberOfEnabledColumns()}}' align="center">
                    <div ng-if="data.findingRegistrations">
                        <img src="<?php $this->asset('img/spinner_192.gif') ?>" width="48">
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan='{{numberOfEnabledColumns()}}' align="center" ng-if="!data.findingRegistrations">
                    <button ng-click="findRegistrations();data.findingRegistrations = true"><?php MapasCulturais\i::_e("Carregar mais");?></button>
                </td>
            </tr>
        </tfoot>
    </table>

</div>