<?php

use MapasCulturais\i;
?>
<div ng-controller='Support'>
    <?php $this->applyTemplateHook('opportunity-support', 'before'); ?>
    <div id="support" class="aba-content" id="support">

        <header>
            <h4><?php i::_e("Agentes autorizados"); ?></h4>
            <p><?php i::_e("Gerencie os agentes de suporte dessa oportunidade."); ?></p>
        </header>

        <p class="support-message" ng-if="!data.agents.length"><?php i::_e("Nenhum agente cadastrado"); ?></p>

        <div class="support-content" ng-if="data.agents.length">
            <?php $this->applyTemplateHook('opportunity-support', 'begin'); ?>

            <div class="support-body">
                <div class="committee ng-scope" ng-repeat="(key,agent) in data.agents">
                    <div ng-controller='SupportModal'>
                        <div class="committee--info">
                            <span ng-click="data.openModal = true" class="btn btn-default add alignright mr10 ng-scope"><?php i::_e("Autorizar campos"); ?></span>
                            <img class="committee--avatar" ng-src="{{(agent.agent.avatar.avatarSmall.url) ? agent.agent.avatar.avatarSmall.url : data.defaultAvatar}}" src="{{(agent.agent.avatar.avatarSmall.url) ? agent.agent.avatar.avatarSmall.url : data.defaultAvatar}}">
                            <span class="committee--name ng-binding">{{agent.agent.name}}</span>
                            <span class="committee--group">{{agent.group}}</span>
                        </div>

                        <div ng-class="{open:data.openModal}" class="bg-support-modal">
                            <div class="support-content-modal">
                                <?php $this->part('support/opportunity-support-fields-association', ['entity' => $entity]); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <footer></footer>

            <?php $this->applyTemplateHook('opportunity-support', 'end'); ?>
        </div>


    </div>
    <?php $this->applyTemplateHook('opportunity-support', 'after'); ?>
</div>