<?php use MapasCulturais\i; ?>
<?php if ($entity->publishedPreliminaryRegistrations): ?>
<!--#preliminary-results-->
<div id="preliminaryresults" class="aba-content">

    <?php if ($entity->publishedRegistrations): ?>
        <div class="clearfix">
            <div class='alert success'><?php \MapasCulturais\i::_e("O resultado oficial já foi publicado");?>
                <div class="close" style="cursor: pointer;"></div>
            </div>
        </div>
    <?php elseif ($entity->publishedPreliminaryRegistrations): ?>
        <div class="clearfix">
            <div class='alert success'><?php \MapasCulturais\i::_e("O resultado preliminar já foi publicado");?>
                <div class="close" style="cursor: pointer;"></div>
            </div>
        </div>
    <?php endif; ?>
    <div class="highlighted-message clearfix" ng-if="data.registrationsAPIMetadata.count > 0">
        <?php i::_e('O resultado poderá sofrer alterações na publicação do resultado oficial.'); ?>
    </div>
    <?php if ($entity->canUser('@control')): ?>
        <div class="clearfix sombra registration-toolbar">
            <div class="registration-actions">
                <div class="dropdown js-dropdown">
                    <div class="placeholder icon icon-project"><span>Relatórios</span></div>
                    <div class="submenu-dropdown js-submenu-dropdown" style="display: none; background-color: #FFF;">
                        <ul>
                            <li>
                                <a href="<?php echo $app->createUrl('opportunity','report', [$entity->id]); ?>"><?php \MapasCulturais\i::esc_attr_e("Inscritos");?></a>
                            </li>
                            <li>
                                <a href="<?php echo $app->createUrl('opportunity','reportDrafts', [$entity->id]); ?>"><?php \MapasCulturais\i::esc_attr_e("Rascunhos");?></a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="registration-actions">
                <div class="dropdown js-dropdown" ng-click="isShowConfig = !isShowConfig">
                    <div class="placeholder icon icon-search"><span>Filtrar</span></div>
                </div>
            </div>

        </div>


        <div class="registration-table-columns" ng-show="isShowConfig" >
            <div class="clearfix">
                <div id="registration-columns-view" class="dropdown registration-columns-view-dropdown">
                    <div class="placeholder" ng-click="filter_dropdown = ''"><?php i::_e("Habilitar Colunas:") ?></div>
                    <div class="submenu-dropdown" style="background: #fff;">
                        <div class="filter-search" style="padding: 5px;">
                            <input type="text" ng-model="filter_dropdown" style="width:100%;" placeholder="Busque pelo nome dos campos do formulário de inscrição e selecione as colunas visíveis" />
                        </div>
                        <ul class="filter-list">
                            <li ng-repeat="field in data.defaultPremilinarySelectFields | filter:filter_dropdown" ng-if="field.required"
                                ng-class="{'selected':isSelected(data.registrationTableColumns, field.fieldName)}"
                                ng-click="toggleSelectionColumn(data.registrationTableColumns, field.fieldName)" >
                                <span>{{field.title}}</span>
                            </li>
                            <li ng-repeat="field in data.opportunitySelectFields | filter:filter_dropdown" ng-if="field.required"
                                ng-class="{'selected':isSelected(data.registrationTableColumns, field.fieldName)}"
                                ng-click="toggleSelectionColumn(data.registrationTableColumns, field.fieldName)" >
                                <span>{{field.title}}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="clearfix">
                <div id="selected-filters" class="registration-columns-view-filters">
                 <span>
                    <a ng-repeat="field in data.defaultPremilinarySelectFields" ng-click="toggleSelectionColumn(data.registrationTableColumns, field.fieldName)"  class="tag-selected sombra" ng-if="isSelected(data.registrationTableColumns, field.fieldName)" >{{field.title}}</a>
                    <a ng-repeat="field in data.opportunitySelectFields" ng-click="toggleSelectionColumn(data.registrationTableColumns, field.fieldName)"  class="tag-selected sombra" ng-if="isSelected(data.registrationTableColumns, field.fieldName)" >{{field.title}}</a>
                 </span>
                </div>
            </div>

            <div id="filtro-inscritos">
                <input ng-model="data.registrations.filtro" placeholder="<?php i::_e('Pesquisar pelo nome do responsável ou número de inscrição') ?>" />
            </div>

        </div>
    <?php endif; ?>
    <table id="registrations-table" class="js-registration-list registrations-table" ng-class="{'no-options': data.entity.registrationCategories.length === 0, 'no-attachments': data.entity.registrationFileConfigurations.length === 0, 'registrations-results': data.entity.published, 'fullscreen': data.fullscreenTable}"><!-- adicionar a classe registrations-results quando resultados publicados-->
        <thead>
        <tr>
            <th ng-show="data.registrationTableColumns.number" class="registration-id-col">
                <?php i::_e("Inscrição");?>
            </th>
            <th ng-show="data.registrationTableColumns.category" ng-if="data.entity.registrationCategories" class="registration-option-col" title="{{data.registrationCategory}}">
                <mc-select class="left transparent-placeholder" placeholder="status" model="registrationsFilters['category']" data="data.registrationCategoriesToFilter" title="{{data.registrationCategory}}"></mc-select>
            </th>
            <th ng-repeat="field in data.opportunitySelectFields" ng-show="data.registrationTableColumns[field.fieldName]" class="registration-option-col">
                <mc-select class="left transparent-placeholder" placeholder="{{field.title}}" model="registrationsFilters[field.fieldName]" data="field.options" title="{{field.title}}"></mc-select>
            </th>
            <th ng-show="data.registrationTableColumns.agents" class="registration-agents-col">
                <?php i::_e("Agentes");?>
            </th>
            <th ng-show="data.registrationTableColumns.status" class="registration-status-col">
                <mc-select placeholder="Status" model="registrationsFilters['status']" data="data.registrationStatuses"></mc-select>
            </th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td colspan='{{numberOfEnabledColumns()}}'>
                <span ng-if="!usingRegistrationsFilters() && getCountPreliminaryResultRegistrations() === 0"><?php \MapasCulturais\i::_e("Nenhuma inscrição.");?></span>
                <span ng-if="!usingRegistrationsFilters() && getCountPreliminaryResultRegistrations() === 1"><?php \MapasCulturais\i::_e("1 inscrição.");?></span>
                <span ng-if="!usingRegistrationsFilters() && getCountPreliminaryResultRegistrations() > 1">{{getCountPreliminaryResultRegistrations()}} <?php \MapasCulturais\i::_e("inscrições.");?></span>
            </td>
        </tr>

        <tr ng-repeat="reg in data.registrations | filter:data.registrations.filtro" id="registration-{{reg.id}}" ng-class="getStatusSlug(reg.status)" ng-if="hasPreliminaryResultRegistrations(reg)">
            <td class="registration-id-col"><strong>{{reg.publishedPreliminaryRevision.number}}</strong></td>
            <td ng-show="data.registrationTableColumns.category" ng-if="data.entity.registrationCategories" class="registration-option-col">{{reg.publishedPreliminaryRevision.category}}</td>
            <td ng-show="data.registrationTableColumns.agents"  class="registration-agents-col">
                <p>
                    <span class="label"><?php \MapasCulturais\i::_e("Responsável");?></span><br />
                    <a href="{{reg.publishedPreliminaryRevision.owner.singleUrl}}">{{reg.publishedPreliminaryRevision.owner.name}}</a>
                </p>

                <p ng-repeat="relation in reg.agentRelations" ng-if="relation.agent">
                    <span class="label">{{relation.label}}</span><br />
                    <a href="{{relation.agent.singleUrl}}">{{relation.agent.name}}</a>
                </p>
            </td>

            <td ng-show="data.registrationTableColumns.status" class="registration-status-col">
                <span class="status status-{{getStatusSlug(reg.publishedPreliminaryRevision.status)}}"> {{getStatusNameById(reg.publishedPreliminaryRevision.status)}} </span>
            </td>
        </tr>

        </tbody>
    </table>

</div>
<?php endif; ?>
<!--#preliminary-results-->