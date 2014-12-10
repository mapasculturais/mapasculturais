<?php
if($this->controller->action === 'create')
    return;

$this->addRelatedAgentsToJs($entity);
?>
<div class="agentes-relacionados" ng-controller="RelatedAgentsController">
    <div class="widget" ng-if="isEditable">
        <edit-box id="new-related-agent-group" position="left" title="Adicionar grupo de agentes" cancel-label="Cancelar" submit-label="Criar" on-cancel="closeNewGroupEditBox" on-submit="createGroup">
            <input type="text" ng-model="data.newGroupName" placeholder="Nome do grupo de agentes"/>
        </edit-box>
        <a class="botao adicionar hltip" title="Grupos de agentes podem ser usados para exibir membros de um coletivo, equipes técnicas, etc." ng-click="editbox.open('new-related-agent-group', $event)">adicionar grupo de agentes</a>
    </div>
    <div class="widget" ng-repeat="group in groups">
        <h3>{{group.name}}</h3>
        <div class="agentes clearfix">
            <div class="avatar" ng-repeat="(i, relation) in group.relations" ng-class="{pending: relation.status < 0}">
                <a href="{{relation.agent.singleUrl}}" ng-if="!isEditable">
                    <img ng-src="{{avatarUrl(relation.agent)}}" />
                </a>
                <img ng-if="isEditable" ng-src="{{avatarUrl(relation.agent)}}" />

                <div class="descricao-do-agente">
                    <h1><a href="{{relation.agent.singleUrl}}">{{relation.agent.name}}</a></h1>
                    <div class="alert warning" ng-if="relation.status < 0">Aguardando confirmação.</div>
                    <div class="objeto-meta">
                        <div ng-if="relation.agent.terms.area">
                            <span class="label">área de atuação:</span>
                            <span ng-repeat="area in relation.agent.terms.area">{{area}}<span ng-if="!$last && area">, </span></span>
                        </div>
                        <div><span class="label">tipo:</span> {{relation.agent.type.name}}</div>
                    </div>
                    <div class="clearfix" ng-if='isEditable && canChangeControl'>
                        <span class="label">Permitir editar:</span>
                        <div class="slider-frame" ng-click="toggleControl(relation)" >
                            <span class="slider-button" ng-class="{'on':relation.hasControl}">{{relation.hasControl ? 'Sim' : 'Não' }}</span>
                        </div>
                    </div>
                    <div class="textright" ng-if="isEditable && (!relation.hasControl || canChangeControl) && !disabledCD(group.name)">
                        <button type="submit" class="bt-remove-agent" ng-click="deleteRelation(relation)">Excluir</button>
                    </div>
                </div>
            </div>
            <div ng-if="isEditable && !disabledCD(group.name)" ng-click="editbox.open(getCreateAgentRelationEditBoxId(group.name), $event)" class="hltip editable editable-empty" title="Adicionar Integrante a este Grupo"></div>

            <edit-box ng-if="isEditable" id="{{getCreateAgentRelationEditBoxId(group.name)}}" position="left" title="Adicionar agente relacionado" spinner-condition="spinners[group.name]" cancel-label="Cancelar" close-on-cancel='true'>
                <find-entity entity="agent" no-results-text="Nenhum agente encontrado" spinner-condition="spinners[group.name]" description="" group="{{group.name}}" filter="filterResult" select="createRelation"></find-entity>
            </edit-box>
        </div>
    </div>
</div>