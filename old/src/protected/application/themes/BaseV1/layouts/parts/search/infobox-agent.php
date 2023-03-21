        <article class="objeto clearfix" ng-if="openEntity.agent">
            <?php $this->applyTemplateHook('infobox-agent','begin'); ?>
            <h1><a href="{{openEntity.agent.singleUrl}}" rel='noopener noreferrer'>{{openEntity.agent.name}}</a></h1>
            <?php $this->applyTemplateHook('infobox-agent.content','before'); ?>
            <div class="objeto-content clearfix">
                <?php $this->applyTemplateHook('infobox-agent.content','begin'); ?>
                <img class="objeto-thumb" ng-src="{{openEntity.agent['@files:avatar.avatarSmall'].url||assetsUrl.avatarAgent}}">
                <p class="objeto-resumo">{{openEntity.agent.shortDescription}}</p>
                <div class="objeto-meta">
                    <?php $this->applyTemplateHook('infobox-agent.metadata','begin'); ?>
                    <div><span class="label"><?php \MapasCulturais\i::_e("Tipo");?>:</span> <a ng-click="toggleSelection(data.agent.filters.type, openEntity.agent.type.id.toString())" rel='noopener noreferrer'>{{openEntity.agent.type.name}}</a></div>
                    <div>
                        <span class="label"><?php \MapasCulturais\i::_e("Áreas de atuação");?>:</span>
                            <span ng-repeat="area in openEntity.agent.terms.area">
                                <a ng-click="toggleSelection(data.agent.filters.area, area)" rel='noopener noreferrer'>{{area}}</a>{{$last ? '' : ', '}}
                            </span>
                    </div>
                    <div ng-if="openEntity.agent.terms.tag.length > 0">
                        <span class="label">Tags:</span>
                        <span ng-repeat="tags in openEntity.agent.terms.tag">
                            <a class="tag tag-agent" href="<?php echo $app->createUrl('site', 'search') ?>##(agent:(keyword:'{{tags}}'),global:(enabled:(agent:!t),filterEntity:agent,viewMode:list))">{{tags}}</a>
                        </span>
                    </div>
                    <?php $this->applyTemplateHook('infobox-agent.metadata','end'); ?>
                </div>
                <?php $this->applyTemplateHook('infobox-agent.content','end'); ?>
            </div>
            <?php $this->applyTemplateHook('infobox-agent.content','after'); ?>
            <?php $this->applyTemplateHook('infobox-agent','end'); ?>
        </article>
    
