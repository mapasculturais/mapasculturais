<?php
if($this->controller->action === 'create' || !$this->isEditable())
    return;

$this->addRelatedAdminAgentsToJs($entity);
?>
<div class="agentes-relacionados" ng-controller="RelatedAgentsController">
    <div class="widget">
        <h3>Administradores</h3>
        <div class="agentes clearfix">
            <div class="avatar" ng-repeat="admin in admins">
                <a href="{{admin.agent.singleUrl}}" ng-if="!isEditable">
                    <img ng-src="{{avatarUrl(admin.agent)}}" />
                </a>
                <img ng-if="isEditable" ng-src="{{avatarUrl(admin.agent)}}" />

                <div class="descricao-do-agente">
                    <h1><a href="{{admin.agent.singleUrl}}">{{admin.agent.name}}</a></h1>
                    <div class="objeto-meta">
                        <div ng-if="admin.agent.terms.area">
                            <span class="label"><?php echo strtolower($this->dict('taxonomies:area: name', true)) ?>:</span>
                            <span ng-repeat="area in admin.agent.terms.area">{{area}}<span ng-if="!$last && area">, </span></span>
                        </div>
                        <div><span class="label">tipo:</span> {{admin.agent.type.name}}</div>
                    </div>
                    <div ng-if="isEditable">
                        <a href="#" class="btn btn-danger delete" ng-click="deleteAdminRelation(admin.agent)"><?php \MapasCulturais\i::_e("Excluir");?></a>
                    </div>
                </div>
            </div>
            <div ng-if="isEditable" ng-click="editbox.open('add-related-agent', $event)" class="hltip editable editable-empty" title="Adicionar Agente aos Administradores"></div>

            <edit-box ng-if="isEditable" id="add-related-agent" position="left" title="Adicionar agente relacionado" cancel-label="Cancelar" close-on-cancel='true'>
                <find-entity entity="agent" no-results-text="Nenhum agente encontrado" description="" spinner-condition="false" select="createAdminRelation"></find-entity>
            </edit-box>
        </div>
    </div>
</div>
