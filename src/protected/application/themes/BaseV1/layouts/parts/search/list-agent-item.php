        
        
            <article class="objeto clearfix" ng-repeat="agent in agents" id="agent-result-{{agent.id}}">
                <h1><a href="{{agent.singleUrl}}" rel='noopener noreferrer'>{{agent.name}}</a></h1>
                <div class="objeto-content clearfix">
                    <a href="{{agent.singleUrl}}" class="js-single-url" rel='noopener noreferrer'>
                        <img class="objeto-thumb" ng-src="{{agent['@files:avatar.avatarMedium'].url||defaultImageURL.replace('avatar','avatar--agent')}}">
                    </a>
                    <p class="objeto-resumo">{{agent.shortDescription}}</p>
                    <div class="objeto-meta">
                        <?php $this->applyTemplateHook('list.agent.meta','begin'); ?>
                        <div><span class="label"><?php \MapasCulturais\i::_e("Tipo");?>:</span> <a ng-click="data.agent.type=agent.type.id" rel='noopener noreferrer'>{{agent.type.name}}</a></div>
                        <div>
                            <span class="label"><?php \MapasCulturais\i::_e("Área de atuação");?>:</span>
                            <span ng-repeat="area in agent.terms.area">
                                <a ng-click="toggleSelection(data.agent.areas, getId(areas, area))" rel='noopener noreferrer'>{{area}}</a>{{$last ? '' : ', '}}
                            </span>
                        </div>
                        <div ng-if="agent.terms.tag.length > 0">
                            <span class="label">Tags:</span>
                            <span ng-repeat="tags in agent.terms.tag">
                                <a class="tag tag-agent" href="<?php echo $app->createUrl('site', 'search') ?>##(agent:(keyword:'{{tags}}'),global:(enabled:(agent:!t),filterEntity:agent,viewMode:list))">{{tags}}</a>
                            </span>
                        </div>
                        <?php $this->applyTemplateHook('list.agent.meta','end'); ?>
                    </div>
                </div>
            </article>
