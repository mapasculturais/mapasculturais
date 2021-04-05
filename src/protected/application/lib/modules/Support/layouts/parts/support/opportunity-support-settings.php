<?php

use MapasCulturais\i;
?>
<div ng-controller='Support'>
    <?php $this->applyTemplateHook('opportunity-support', 'before');?>
    <div class="aba-content" id="support-settings">
        <!-- Header -->
        <header>
            <h4><?php i::_e("Agentes autorizados");?></h4>
            <a class="btn btn-default add alignright" ng-click="editbox.open('add-age', $event); editBoxOpen()" rel="noopener noreferrer"><?php i::_e("Adicionar agente");?></a>
            <!-- Edit box add agentes -->
            <edit-box id="add-age" position="top" title="" cancel-label="" close-on-cancel="true">
                <h4><?php i::_e("Adicionar agentes");?></h4>
                <div class="directive-find-entity ">
                    <input ng-model="data.searchAgents" ng-change="findAgents()" placeholder="buscar por nome" />
                    <img src="<?php $this->asset('img/spinner-black.gif')?>" class="spinner" ng-class="{hidden:!data.spinner}" />
                    <ul class="result-container" ng-repeat="(key,agent) in data.agents" style="overflow-y: auto;" ng-click="selectAgent(agent)">
                        <li class="search-agent clearfix ng-scope">
                            <img class="search-agent-thumb" ng-src="{{avatarUrl(agent)}}" alt="{{agent.name}}">
                            <h1 class="ng-binding">{{agent.name}}</h1>
                            <div class="objeto-meta">
                                <div class="ng-binding">
                                    <span class="label"><?php i::_e("área de atuação:");?></span>
                                    {{agent.terms.area.join(', ')}}
                                </div>
                                <div class="ng-binding"><span class="label"><?php i::_e("tipo:");?></span>{{agent.type.name}}</div>
                            </div>
                        </li>
                    </ul>
                </div>

                <a class="btn btn-default cancel alignright" ng-click="editBoxCancel()" rel="noopener noreferrer"><?php i::_e("Cancelar");?></a>
            </edit-box><!-- Fim edit box add agentes -->

            <p><?php i::_e("Gerencie os agentes de suporte dessa oportunidade.");?></p>
        </header><!-- Fim header -->
        <!-- Menssagem caso nao exista agentes -->
        <p class="support-message" ng-if="!data.agentsRelations.length"><?php i::_e("Nenhum agente cadastrado");?></p>
        
        <!-- Content -->
        <div class="support-content" ng-if="data.agentsRelations.length">
            <?php $this->applyTemplateHook('opportunity-support', 'begin');?>

            <div class="support-body">
                <div class="committee ng-scope" ng-repeat="(key,agentRelation) in data.agentsRelations">
                    <div ng-controller='SupportModal'>
                        <div class="committee--info">
                            <span class="btn btn-danger delete alignright" ng-click="deleteAgentRelation(agentRelation.agent.id)"><?php i::_e("Excluir");?></span>
                            <span ng-click="data.openModal = true" class="btn btn-default add alignright mr10 ng-scope"><?php i::_e("Autorizar campos");?></span>
                            <img class="committee--avatar" ng-src="{{(agentRelation.agent.avatar.avatarSmall.url) ? agentRelation.agent.avatar.avatarSmall.url : data.defaultAvatar}}" src="{{(agentRelation.agent.avatar.avatarSmall.url) ? agentRelation.agent.avatar.avatarSmall.url : data.defaultAvatar}}">
                            <span class="committee--name ng-binding">{{agentRelation.agent.name}}</span>
                        </div>

                        <div ng-class="{open:data.openModal}" class="bg-support-modal">
                            <div class="support-content-modal">
                                <?php $this->part('support/opportunity-support-fields-association', ['entity' => $entity]);?>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!--Fim content -->
            <?php $this->applyTemplateHook('opportunity-support', 'end');?>
        </div>
    </div>
    <?php $this->applyTemplateHook('opportunity-support', 'after');?>
</div>