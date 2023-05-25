<?php
use MapasCulturais\i;

?>
<div class="aba-content" id="support">
    <header id="header-inscritos" class="clearfix">
        <?php $this->applyTemplateHook('header-inscritos-support','begin'); ?>
        <h3><?php i::_e("Inscritos");?></h3>
        <?php $this->applyTemplateHook('header-inscritos-support','actions'); ?>
        <?php $this->applyTemplateHook('header-inscritos-support','end'); ?>
    </header>


    <div id="filtro-inscritos">
        <span class="label"> <?php i::_e("Filtrar inscrição:");?> </span>
        <input ng-model="data.registrationsFilter" placeholder="<?php i::_e('Busque pelo número de inscrição, status da avaliação, nome ou cpf do responsável') ?>" />
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
                <?php $this->applyTemplateHook('registration-list-header-support','begin'); ?>
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
                <?php i::_e("Status");?>
                </th>

                <?php $this->applyTemplateHook('registration-list-header-support','end'); ?>
            </tr>
        </thead>
        <tr>
            <td colspan="3">
                
                <span ng-if="!usingRegistrationsFilters() && data.registrationsAPIMetadata.count === 0"><?php i::_e("Nenhuma inscrição.");?></span>
                <span ng-if="usingRegistrationsFilters() && data.registrationsAPIMetadata.count === 0"><?php i::_e("Nenhuma inscrição encontrada com os filtros selecionados.");?></span>
                <span ng-if="!usingRegistrationsFilters() && data.registrationsAPIMetadata.count === 1"><?php i::_e("1 inscrição.");?></span>
                <span ng-if="usingRegistrationsFilters() && data.registrationsAPIMetadata.count === 1"><?php i::_e("1 inscrição encontrada com os filtros selecionados.");?></span>
                <span ng-if="!usingRegistrationsFilters() && data.registrationsAPIMetadata.count > 1">
                    {{data.registrations.length}} <i> de {{ data.registrationsAPIMetadata.count }}</i> <?php i::_e("inscrições.");?>
                    <?php if($entity->registrationLimit > 0):?>
                        | <?php i::_e("Número máximo de vagas na oportunidade:");?> <?php echo $entity->registrationLimit;?>
                    <?php endif;?>
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
        <?php $this->applyTemplateHook('registration-list-item-support','begin'); ?>
                <td ng-show="data.registrationTableColumns.number" class="registration-id-col"><a href="<?=$app->createUrl('suporte', 'inscricao', ['{{reg.id}}'])?>" rel='noopener noreferrer'>{{reg.number}}</a></td>
                <td ng-show="data.registrationTableColumns.category" ng-if="data.entity.registrationCategories" class="registration-option-col">{{reg.category}}</td>
                <td ng-repeat="field in data.opportunitySelectFields" ng-if="data.registrationTableColumns[field.fieldName]" class="registration-option-col">
                    {{reg[field.fieldName]}}
                </td>
                <td ng-show="data.registrationTableColumns.agents" class="registration-agents-col">
                    <p>
                        <span class="label"><?php i::_e("Responsável");?></span><br />
                        <a href="{{reg.owner.singleUrl}}" rel='noopener noreferrer'>{{reg.owner.name}}</a>
                    </p>

                    <p ng-repeat="relation in reg.agentRelations" ng-if="relation.agent">
                        <span class="label">{{relation.label}}</span><br />
                        <a href="{{relation.agent.singleUrl}}" rel='noopener noreferrer'>{{relation.agent.name}}</a>
                    </p>
                </td>
                <td ng-show="data.registrationTableColumns.status" class="registration-status-col">
                    {{getStatusNameById(reg.status)}}
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan='3' align="center">
                    <div ng-if="data.findingRegistrations">
                        <img src="<?php $this->asset('img/spinner_192.gif')?>" width="48">
                    </div>
                </td>
            </tr>
        </tfoot>
    </table>

        <?php
        $_evaluation_type = $entity->evaluationMethodConfiguration->getType();
        if( is_object($_evaluation_type) && property_exists($_evaluation_type, "id") && $_evaluation_type->id === "simple" ): ?>
            <div ng-if="hasEvaluations()">
                <button class="btn btn-primary" ng-click="applyEvaluations()"> {{ data.confirmEvaluationLabel }} </button>
            </div>
        <?php endif; ?>

    </div>
</div>