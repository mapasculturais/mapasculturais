        <div ng-if="openEntity.event">
            <p class="espaco-dos-eventos"><?php \MapasCulturais\i::_e("Eventos encontrados em:");?><br>
                <a href="{{openEntity.event.space.singleUrl}}">
                    <span class="icon icon-space"></span>{{openEntity.event.space.name}}
                </a><br>
                {{openEntity.event.space.endereco}}
            </p>

            <article class="objeto clearfix" ng-repeat="event in openEntity.event.events">
                <h1>
                    <a href="{{event.singleUrl}}">
                        {{event.name}}
                        <span class="event-subtitle">{{event.subTitle}}</span>
                    </a>
                </h1>
                <div class="objeto-content clearfix">
                    <a href="{{event.singleUrl}}" class="js-single-url">
                        <img class="objeto-thumb" ng-src="{{event['@files:avatar.avatarSmall'].url||assetsUrl.avatarEvent}}">
                    </a>
                    <div class="objeto-resumo">
                        <p>{{event.shortDescription}}</p>
                    </div>
                    <ul class="event-ocurrences">
                        <li ng-repeat="occ in event.occurrences">
                            {{occ.rule.description.trim()||event.readableOccurrences[$index].trim()}}<span ng-show="occ.rule.price.length" >. {{occ.rule.price.trim()}}</span><span ng-show="!$last">.</span>
                        </li>
                    </ul>
                    <div class="objeto-meta">
                        <?php $this->applyTemplateHook('event-infobox-new-fields-before','begin'); ?>
                        <?php $this->applyTemplateHook('event-infobox-new-fields-before','end'); ?>
                        <div ng-if="event.project.name">
                            <span class="label"><?php \MapasCulturais\i::_e("Projeto");?>:</span>
                            <a href="{{event.project.singleUrl}}">{{event.project.name}}</a>
                        </div>
                        <div ng-show="event.terms.linguagem && event.terms.linguagem.length">
                            <span class="label"><?php \MapasCulturais\i::_e("Linguagem");?>:</span>
                            <span ng-repeat="linguagem in event.terms.linguagem">
                                <a ng-click="toggleSelection(data.event.linguagens, getId(linguagens, linguagem))">{{linguagem}}</a>{{$last ? '' : ', '}}
                            </span>
                        </div>
                        <div>
                            <span class="label"><?php \MapasCulturais\i::_e("Classificação");?>:</span>
                            <a ng-click="toggleSelection(data.event.classificacaoEtaria, getId(classificacoes, event.classificacaoEtaria))">{{event.classificacaoEtaria}}</a>
                        </div>
                        <div>
                            <span class="label">Tags:</span>
                            <span ng-repeat="tags in event.terms.tag">
                                <a class="tag tag-event" href="<?php echo $app->createUrl('site', 'search') ?>##(event:(keyword:'{{tags}}'),global:(enabled:(event:!t),filterEntity:event,viewMode:list))">{{tags}}</a>
                            </span>
                        </div>
                    </div>
                </div>
            </article>
        </div>
    
