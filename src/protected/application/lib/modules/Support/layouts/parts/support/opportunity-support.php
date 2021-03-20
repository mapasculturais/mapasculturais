<?php

use MapasCulturais\i;
?>
<div ng-controller='Support'>
    <?php $this->applyTemplateHook('opportunity-support', 'before'); ?>
    <div class="aba-content" id="support">
        <div class="support-content">
            <?php $this->applyTemplateHook('opportunity-support', 'begin'); ?>

            <header>
                <div class="title">
                    <h2><?php i::_e("Agentes autorizados"); ?></h2>
                </div>
                <div class="modal-button">

                </div>
            </header>

            <div class="support-body">
                <div class="committee ng-scope" ng-repeat="(key,agent) in data.agents">
                    <div ng-controller='SupportModal'>
                        <div class="committee--info">
                            <span ng-click="data.openModal = true" class="btn btn-default add alignright mr10 ng-scope"><?php i::_e("Autorizar campos"); ?></span>
                            <img class="committee--avatar" ng-src="{{agent.owner.avatar.avatarSmall.url}}" src="{{agent.owner.avatar.avatarSmall.url}}">
                            <span class="committee--name ng-binding">{{agent.agent.name}}</span>
                            <div>{{agent.group}}</div>
                        </div>

                        <div class="support-content-modal" ng-if="data.openModal" class="bg-support-modal">
                            <?php $this->part('support/opportunity-support-fields-association', ['entity' => $entity]); ?>
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