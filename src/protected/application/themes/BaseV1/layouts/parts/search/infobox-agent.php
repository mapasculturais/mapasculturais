        <article class="objeto clearfix" ng-if="openEntity.agent">
            <h1><a href="{{openEntity.agent.singleUrl}}">{{openEntity.agent.name}}</a></h1>
            <img class="objeto-thumb" ng-src="{{openEntity.agent['@files:avatar.avatarSmall'].url||assetsUrl.avatarAgent}}">
            <p class="objeto-resumo">{{openEntity.agent.shortDescription}}</p>
            <div class="objeto-meta">
                <?php $this->applyTemplateHook('agent-infobox-new-fields-before','begin'); ?>
                <?php $this->applyTemplateHook('agent-infobox-new-fields-before','end'); ?>
                <div><span class="label"><?php \MapasCulturais\i::_e("Tipo");?>:</span> <a ng-click="data.agent.type=openEntity.agent.type.id">{{openEntity.agent.type.name}}</a></div>
                <div>
                    <span class="label"><?php \MapasCulturais\i::_e("Áreas de atuação");?>:</span>
                        <span ng-repeat="area in openEntity.agent.terms.area">
                            <a ng-click="toggleSelection(data.agent.areas, getId(areas, area))">{{area}}</a>{{$last ? '' : ', '}}
                        </span>
                </div>
                <div>
                    <span class="label">Tags:</span>
                    <span ng-repeat="tags in openEntity.agent.terms.tag">
                        <a class="tag tag-agent" href="<?php echo $app->createUrl('site', 'search') ?>##(agent:(keyword:'{{tags}}'),global:(enabled:(agent:!t),filterEntity:agent,viewMode:list))">{{tags}}</a>
                    </span>
                </div>
            </div>
        </article>
    
