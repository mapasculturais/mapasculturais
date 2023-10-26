        

            <article class="objeto clearfix" ng-repeat="event in events">
                <h1>
                    <a href="{{event.singleUrl}}" rel='noopener noreferrer'>
                        {{event.name}}
                        <span class="event-subtitle">{{event.subTitle}}</span>
                    </a>
                </h1>
                <div class="objeto-content clearfix">
                    <a href="{{event.singleUrl}}" class="js-single-url" rel='noopener noreferrer'>
                        <img class="objeto-thumb" ng-src="{{event['@files:avatar.avatarMedium'].url||defaultImageURL.replace('avatar','avatar--event')}}">
                    </a>
                    <div class="objeto-resumo">
                        <p>{{event.shortDescription}}</p>
                        <ul class="event-ocurrences">
                            <?php $this->applyTemplateHook('list.event.occurrences','begin'); ?>
                            <li ng-repeat="occ in event.occurrences">
                                <?php $this->applyTemplateHook('list.event.occurrence','begin'); ?>
                                <a href="{{occ.space.singleUrl}}" rel='noopener noreferrer'>{{occ.space.name}}</a>
                                {{occ.space.endereco.trim()}}
                                {{occ.rule.description.trim()}}<span ng-show="occ.rule.price.length" >. {{occ.rule.price.trim()}}</span>.
                                <?php $this->applyTemplateHook('list.event.occurrence','end'); ?>
                            </li>
                            <?php $this->applyTemplateHook('list.event.occurrences','end'); ?>
                        </ul>
                    </div>
                    <div class="objeto-meta">
                        <?php $this->applyTemplateHook('list.event.meta','begin'); ?>
                        <div ng-if="event.project.name">
                            <span class="label"><?php \MapasCulturais\i::_e("Projeto");?>:</span>
                            <a href="{{event.project.singleUrl}}" rel='noopener noreferrer'>{{event.project.name}}</a>
                        </div>
                        <div>
                            <span ng-show="event.terms.linguagem" class="label"><?php \MapasCulturais\i::_e("Linguagem");?>:</span>
                            <span ng-repeat="linguagem in event.terms.linguagem">
                                <a rel='noopener noreferrer'>{{linguagem}}</a>{{$last ? '' : ', '}}
                            </span>
                        </div>
                        <div><span class="label"><?php \MapasCulturais\i::_e("Classificação");?>:</span> <a ng-click="toggleSelection(data.event.classificacaoEtaria, getId(classificacoes, event.classificacaoEtaria))" rel='noopener noreferrer'>{{event.classificacaoEtaria}}</a></div>
                        <div ng-if="event.terms.tag.length > 0">
                            <span class="label">Tags:</span>
                            <span ng-repeat="tags in event.terms.tag">
                                <a class="tag tag-event" href="<?php echo $app->createUrl('site', 'search') ?>##(event:(keyword:'{{tags}}'),global:(enabled:(event:!t),filterEntity:event,viewMode:list))">{{tags}}</a>
                            </span>
                        </div>
                        <?php $this->applyTemplateHook('list.event.meta','end'); ?>
                    </div>
                </div>
            </article>
