<?php
if($this->controller->action === 'create' || !$this->isEditable())
    return;

$this->addRelatedProfileAgentsToJs($entity);
?>
<div class="agentes-relacionados" ng-controller="RelatedProfileAgentsController">
    <div class="widget">
        <h3>Administradores</h3>
        <div class="agentes clearfix">
            <div class="avatar" ng-repeat="admin in profiles">
                <img ng-src="{{admin.agent.files.avatar.files.avatarSmall.url}}" />

                <div class="descricao-do-agente">
                    <h1><a href="{{admin.profile.singleUrl}}">{{admin.profile.name}}</a></h1>
                    <div class="objeto-meta">
                        <div ng-if="admin.profile.terms.area">
                            <span class="label"><?php echo strtolower($this->dict('taxonomies:area: name', true)) ?>:</span>
                            <span ng-repeat="area in admin.profile.terms.area">{{area}}<span ng-if="!$last && area">, </span></span>
                        </div>
                        <div><span class="label">tipo:</span> {{admin.profile.type.name}}</div>
                    </div>
                    <div>
                        <a href="#" class="btn btn-danger delete" ng-click="deleteAdminRelation(admin.agent.id)">Excluir</a>
                    </div>
                </div>
            </div>
            <div ng-click="editbox.open('add-related-agent', $event)" class="hltip editable editable-empty" title="Adicionar Agente aos Administradores"></div>

            <edit-box id="add-related-agent" position="left" title="Adicionar agente relacionado" cancel-label="Cancelar" close-on-cancel='true'>
                <find-entity entity="agent" no-results-text="Nenhum agente encontrado" description="" spinner-condition="false" select="createAdminRole"></find-entity>
            </edit-box>
        </div>
    </div>
</div>
