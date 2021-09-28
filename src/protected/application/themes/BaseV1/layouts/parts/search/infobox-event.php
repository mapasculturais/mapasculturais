        <div ng-if="openEntity.event">
            <?php $this->applyTemplateHook('infobox-event','begin'); ?>
            <p class="espaco-dos-eventos">
                <?php $this->applyTemplateHook('infobox-event.space','begin'); ?>
                <?php \MapasCulturais\i::_e("Eventos encontrados em:");?><br>
                <a href="{{openEntity.event.space.singleUrl}}" rel='noopener noreferrer'>
                    <span class="icon icon-space"></span>{{openEntity.event.space.name}}
                </a><br>
                {{openEntity.event.space.endereco}}
                <?php $this->applyTemplateHook('infobox-event.space','end'); ?>
            </p>

            <article class="objeto clearfix" ng-repeat="event in openEntity.event.events">
                <?php $this->applyTemplateHook('infobox-event.event','begin'); ?>
                <h1>
                    <a href="{{event.singleUrl}}" rel='noopener noreferrer'>
                        {{event.name}}
                        <span class="event-subtitle">{{event.subTitle}}</span>
                    </a>
                </h1>
                <?php $this->applyTemplateHook('infobox-event.event.content','before'); ?>
                <div class="objeto-content clearfix">
                    <?php $this->applyTemplateHook('infobox-event.event.content','begin'); ?>
                    <a href="{{event.singleUrl}}" class="js-single-url" rel='noopener noreferrer'>
                        <img class="objeto-thumb" ng-src="{{event['@files:avatar.avatarSmall'].url||assetsUrl.avatarEvent}}">
                    </a>
                    <div class="objeto-resumo">
                        <p>{{event.shortDescription}}</p>
                    </div>
                    <ul class="event-ocurrences">
                        <?php $this->applyTemplateHook('infobox-event.event.occurrences','begin'); ?>
                        <li ng-repeat="occ in event.occurrences">
                            {{occ.rule.description.trim()||event.readableOccurrences[$index].trim()}}<span ng-show="occ.rule.price.length" >. {{occ.rule.price.trim()}}</span><span ng-show="!$last">.</span>
                        </li>
                        <?php $this->applyTemplateHook('infobox-event.event.occurrences','end'); ?>
                    </ul>
                    <div class="objeto-meta">
                        <?php $this->applyTemplateHook('infobox-event.event.metadata','begin'); ?>
                        <div ng-if="event.project.name">
                            <span class="label"><?php \MapasCulturais\i::_e("Projeto");?>:</span>
                            <a href="{{event.project.singleUrl}}" rel='noopener noreferrer'>{{event.project.name}}</a>
                        </div>
                        <div ng-show="event.terms.linguagem && event.terms.linguagem.length">
                            <span class="label"><?php \MapasCulturais\i::_e("Linguagem");?>:</span>
                            <span ng-repeat="linguagem in event.terms.linguagem">
                                <a ng-click="toggleSelection(data.event.filters.linguagem, linguagem)" rel='noopener noreferrer'>{{linguagem}}</a>{{$last ? '' : ', '}}
                            </span>
                        </div>
                        <div>
                            <span class="label"><?php \MapasCulturais\i::_e("Classificação");?>:</span>
                            <a ng-click="toggleSelection(data.event.filters.classificacaoEtaria, event.classificacaoEtaria)" rel='noopener noreferrer'>{{event.classificacaoEtaria}}</a>
                        </div>
                        <div ng-if="event.terms.tag.length">
                            <span class="label">Tags:</span>
                            <span ng-repeat="tags in event.terms.tag">
                                <a class="tag tag-event" href="<?php echo $app->createUrl('site', 'search') ?>##(event:(keyword:'{{tags}}'),global:(enabled:(event:!t),filterEntity:event,viewMode:list))">{{tags}}</a>
                            </span>
                        </div>
                        <?php $this->applyTemplateHook('infobox-event.event.metadata','end'); ?>
                    </div>
                    <?php $this->applyTemplateHook('infobox-event.event.content','end'); ?>
                </div>
                <?php $this->applyTemplateHook('infobox-event.event.content','after'); ?>
                <?php $this->applyTemplateHook('infobox-event.event','end'); ?>
            </article>
            <?php $this->applyTemplateHook('infobox-event','end'); ?>
        </div>
    
