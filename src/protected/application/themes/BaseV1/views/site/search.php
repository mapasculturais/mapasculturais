<?php
$this->layout = 'search';

$this->bodyProperties['ng-app'] = "search.app";
$this->bodyProperties['ng-controller'] = "SearchController";
$this->bodyProperties['ng-class'] = "{'infobox-open': showInfobox()}";

$this->addTaxonoyTermsToJs('area');
$this->addTaxonoyTermsToJs('linguagem');

$this->addEntityTypesToJs('MapasCulturais\Entities\Space');
$this->addEntityTypesToJs('MapasCulturais\Entities\Agent');
$this->addEntityTypesToJs('MapasCulturais\Entities\Project');
$this->addEntityTypesToJs('MapasCulturais\Entities\Seal');

$this->includeSearchAssets();

$def = $app->getRegisteredMetadataByMetakey('classificacaoEtaria', 'MapasCulturais\Entities\Event');
$this->jsObject['classificacoesEtarias'] = array_values($def->config['options']);

$this->includeMapAssets();

?>
    <div id="filtro-local" class="clearfix js-leaflet-control" data-leaflet-target=".leaflet-top.leaflet-left" ng-controller="SearchSpatialController" ng-show="data.global.viewMode ==='map'">
        <form id="form-local" method="post">
            <label for="proximo-a"><?php \MapasCulturais\i::_e("Local");?>: </label>
            <input id="endereco" ng-model="data.global.locationFilters.address.text" type="text" class="proximo-a" name="proximo-a" placeholder="<?php \MapasCulturais\i::esc_attr_e("Digite um endereço");?>" />
            <input type="hidden" name="lat" />
            <input type="hidden" name="lng" />
        </form>
        <a id="near-me" class="control-infobox-open hltip btn-map" ng-click="filterNeighborhood()" title="<?php \MapasCulturais\i::esc_attr_e("Buscar somente resultados próximos a mim.");?>"></a>
        <!--<a class="btn btn-primary hltip" href="#" ng-click="drawCircle()" title="Buscar somente resultados em uma área delimitada">delimitar área</a>-->
    </div>
    <!--#filtro-local-->
    <div id="mc-entity-layers" class="js-leaflet-control" data-leaflet-target=".leaflet-bottom.leaflet-right" ng-show="data.global.viewMode ==='map'">
        <div class="label"><?php \MapasCulturais\i::_e("Mostrar");?>:</div>
        <div>
            <?php if($app->isEnabled('events')): ?>
                <a class="hltip hltip-auto-update btn-map btn-map-event" ng-class="{active: data.global.enabled.event}" ng-click="data.global.enabled.event = !data.global.enabled.event" title="{{(data.global.enabled.event) && 'Ocultar' || 'Mostrar'}} <?php \MapasCulturais\i::_e("eventos");?>"></a>
            <?php endif; ?>

            <?php if($app->isEnabled('spaces')): ?>
                <a class="hltip hltip-auto-update btn-map btn-map-space" ng-class="{active: data.global.enabled.space}" ng-click="data.global.enabled.space = !data.global.enabled.space" title="{{(data.global.enabled.space) && 'Ocultar' || 'Mostrar'}} <?php $this->dict('entities: spaces') ?>"></a>
            <?php endif; ?>

            <?php if($app->isEnabled('agents')): ?>
                <a class="hltip hltip-auto-update btn-map btn-map-agent"  ng-class="{active: data.global.enabled.agent}" ng-click="data.global.enabled.agent = !data.global.enabled.agent" title="{{(data.global.enabled.agent) && 'Ocultar' || 'Mostrar'}} <?php \MapasCulturais\i::_e("agentes");?>"></a>
            <?php endif; ?>


            <?php if($app->isEnabled('seals') && ($app->user->is('superAdmin') || $app->user->is('admin'))): ?>
                <a class="hltip hltip-auto-update btn-map btn-map-seal"  ng-class="{active: data.global.enabled.seal}" ng-click="data.global.enabled.seal = !data.global.enabled.seal" title="{{(data.global.enabled.seal) && 'Ocultar' || 'Mostrar'}} <?php \MapasCulturais\i::_e("selos");?>"></a>
            <?php endif; ?>

        </div>
    </div>

    <div id="infobox" ng-show="showInfobox()" class="{{data.global.openEntity.type}}">
        <a class="icon icon-close" ng-click="data.global.openEntity.id=null"></a>

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


        <article class="objeto clearfix" ng-if="openEntity.space">
            <h1><a href="{{openEntity.space.singleUrl}}">{{openEntity.space.name}}</a></h1>
            <div class="objeto-content clearfix">
                <a href="{{openEntity.space.singleUrl}}" class="js-single-url">
                    <img class="objeto-thumb" ng-src="{{openEntity.space['@files:avatar.avatarSmall'].url||assetsUrl.avatarSpace}}">
                </a>
                <p class="objeto-resumo">{{openEntity.space.shortDescription}}</p>
                <div class="objeto-meta">
                    <?php $this->applyTemplateHook('space-infobox-new-fields-before','begin'); ?>
                    <?php $this->applyTemplateHook('space-infobox-new-fields-before','end'); ?>
                    <div><span class="label"><?php \MapasCulturais\i::_e("Tipo");?>:</span> <a ng-click="toggleSelection(data.space.types, getId(types.space, openEntity.space.type.name))">{{openEntity.space.type.name}}</a></div>
                    <div>
                        <span class="label"><?php \MapasCulturais\i::_e("Área de atuação");?>:</span>
                        <span ng-repeat="area in openEntity.space.terms.area">
                            <a ng-click="toggleSelection(data.space.areas, getId(areas, area))">{{area}}</a>{{$last ? '' : ', '}}
                        </span>
                    </div>
                    <div ng-show="openEntity.space.endereco"><span class="label"><?php \MapasCulturais\i::_e("Endereço");?>:</span>{{openEntity.space.endereco}}</div>
                    <div><span class="label"><?php \MapasCulturais\i::_e("Acessibilidade");?>:</span> {{openEntity.space.acessibilidade || 'Não Informado'}}</div>
                    <div>
                        <span class="label">Tags:</span>
                        <span ng-repeat="tags in openEntity.space.terms.tag">
                            <a class="tag tag-space" href="<?php echo $app->createUrl('site', 'search') ?>##(space:(keyword:'{{tags}}'),global:(enabled:(space:!t),filterEntity:space,viewMode:list))">{{tags}}</a>
                        </span>
                    </div>
                </div>
            </div>
        </article>

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
    </div><!--#infobox-->

    <div id="search-map-container" ng-controller="SearchMapController" ng-show="data.global.viewMode!=='list'" ng-animate="{show:'animate-show', hide:'animate-hide'}" class="js-map" data-options='{"dragging":true, "zoomControl":true, "doubleClickZoom":true, "scrollWheelZoom":true }'>
    </div><!--#search-map-container-->

<!-- Here ends the map view and starts the list view -->
    <div id="lista" ng-show="data.global.viewMode==='list'" ng-animate="{show:'animate-show', hide:'animate-hide'}">
        <header id="project-list-header" class="entity-list-header clearfix" ng-show="data.global.filterEntity == 'project'">
            <div class="clearfix">
                <h1><span class="icon icon-project"></span> <?php \MapasCulturais\i::_e("Projetos");?></h1>
                <a class="btn btn-accent add" href="<?php echo $app->createUrl('project', 'create') ?>"><?php \MapasCulturais\i::_e("Adicionar projeto");?></a>
            </div>
        </header>
        <div id="lista-dos-projetos" class="lista project" infinite-scroll="data.global.filterEntity === 'project' && addMore('project')" ng-show="data.global.filterEntity === 'project'">
            <article class="objeto clearfix"  ng-repeat="project in projects" id="agent-result-{{project.id}}">
                <h1><a href="{{project.singleUrl}}">{{project.name}}</a></h1>
                <div class="objeto-content clearfix">
                    <a href="{{project.singleUrl}}" class="js-single-url">
                        <img class="objeto-thumb" ng-src="{{project['@files:avatar.avatarMedium'].url||assetsUrl.avatarProject}}">
                    </a>
                    <p class="objeto-resumo">
                        {{project.shortDescription}}
                    </p>
                    <div class="objeto-meta">
                        <div><span class="label"><?php \MapasCulturais\i::_e("Tipo");?>:</span> <a href="#">{{project.type.name}}</a></div>
                        <div ng-if="readableProjectRegistrationDates(project)"><span class="label"><?php \MapasCulturais\i::_e("Inscrições");?>:</span> {{readableProjectRegistrationDates(project)}}</div>
                        <div>
                            <span class="label">Tags:</span>
                            <span ng-repeat="tags in project.terms.tag">
                                <a class="tag tag-project" href="<?php echo $app->createUrl('site', 'search') ?>##(project:(keyword:'{{tags}}'),global:(enabled:(project:!t),filterEntity:project,viewMode:list))">{{tags}}</a>
                            </span>
                        </div>
                    </div>
                </div>
            </article>
            <!--.objeto-->
        </div>

        <header id="agent-list-header" class="entity-list-header clearfix" ng-show="data.global.filterEntity == 'agent'">
            <h1><span class="icon icon-agent"></span> <?php \MapasCulturais\i::_e("Agentes");?></h1>
            <a class="btn btn-accent add" href="<?php echo $app->createUrl('agent', 'create'); ?>"><?php \MapasCulturais\i::_e("Adicionar agente");?></a>
        </header>

        <div id="lista-dos-agentes" class="lista agent" infinite-scroll="data.global.filterEntity === 'agent' && addMore('agent')" ng-show="data.global.filterEntity === 'agent'">
            <article class="objeto clearfix" ng-repeat="agent in agents" id="agent-result-{{agent.id}}">
                <h1><a href="{{agent.singleUrl}}">{{agent.name}}</a></h1>
                <div class="objeto-content clearfix">
                    <a href="{{agent.singleUrl}}" class="js-single-url">
                        <img class="objeto-thumb" ng-src="{{agent['@files:avatar.avatarMedium'].url||defaultImageURL.replace('avatar','avatar--agent')}}">
                    </a>
                    <p class="objeto-resumo">{{agent.shortDescription}}</p>
                    <div class="objeto-meta">
                        <div><span class="label"><?php \MapasCulturais\i::_e("Tipo");?>:</span> <a ng-click="data.agent.type=agent.type.id">{{agent.type.name}}</a></div>
                        <div>
                            <span class="label"><?php \MapasCulturais\i::_e("Área de atuação");?>:</span>
                            <span ng-repeat="area in agent.terms.area">
                                <a ng-click="toggleSelection(data.agent.areas, getId(areas, area))">{{area}}</a>{{$last ? '' : ', '}}
                            </span>
                        </div>
                        <div>
                            <span class="label">Tags:</span>
                            <span ng-repeat="tags in agent.terms.tag">
                                <a class="tag tag-agent" href="<?php echo $app->createUrl('site', 'search') ?>##(agent:(keyword:'{{tags}}'),global:(enabled:(agent:!t),filterEntity:agent,viewMode:list))">{{tags}}</a>
                            </span>
                        </div>
                    </div>
                </div>
            </article>
        </div>
        <header id="space-list-header" class="entity-list-header clearfix" ng-show="data.global.filterEntity == 'space'">
            <h1><span class="icon icon-space"></span> <?php $this->dict('entities: Spaces') ?></h1>
            <a class="btn btn-accent add" href="<?php echo $app->createUrl('space', 'create'); ?>"><?php \MapasCulturais\i::_e("Adicionar");?> <?php $this->dict('entities: space') ?></a>
        </header>
        <div id="lista-dos-espacos" class="lista space" infinite-scroll="data.global.filterEntity === 'space' && addMore('space')" ng-show="data.global.filterEntity === 'space'">
            <article class="objeto clearfix" ng-repeat="space in spaces" id="space-result-{{space.id}}">
                <h1><a href="{{space.singleUrl}}">{{space.name}}</a></h1>
                <div class="objeto-content clearfix">
                    <a href="{{space.singleUrl}}" class="js-single-url">
                        <img class="objeto-thumb" ng-src="{{space['@files:avatar.avatarMedium'].url||defaultImageURL.replace('avatar','avatar--space')}}">
                    </a>
                    <p class="objeto-resumo">{{space.shortDescription}}</p>
                    <div class="objeto-meta">
                        <div>
                            <span class="label"><?php \MapasCulturais\i::_e("Tipo");?>:</span>
                            <a ng-click="toggleSelection(data.space.types, getId(types.space, space.type.name))">{{space.type.name}}</a>
                        </div>
                        <div>
                            <span class="label"><?php \MapasCulturais\i::_e("Área de atuação");?>:</span>
                            <span ng-repeat="area in space.terms.area">
                                <a ng-click="toggleSelection(data.space.areas, getId(areas, area))">{{area}}</a>{{$last ? '' : ', '}}
                            </span>
                        </div>
                        <div>
                            <span class="label">Tags:</span>
                            <span ng-repeat="tags in space.terms.tag">
                                <a class="tag tag-space" href="<?php echo $app->createUrl('site', 'search') ?>##(space:(keyword:'{{tags}}'),global:(enabled:(space:!t),filterEntity:space,viewMode:list))">{{tags}}</a>
                            </span>
                        </div>
                        <div ng-show="space.endereco"><span class="label"><?php \MapasCulturais\i::_e("Endereço");?>:</span> {{space.endereco}}</div>
                        <div><span class="label"><?php \MapasCulturais\i::_e("Acessibilidade");?>:</span> {{space.acessibilidade || 'Não informado'}}</div>
                    </div>
                </div>
            </article>
        </div>
        <header id="event-list-header" class="entity-list-header clearfix" ng-show="data.global.filterEntity == 'event'">
            <h1><span class="icon icon-event"></span> <?php \MapasCulturais\i::_e("Eventos");?></h1>
            <a class="btn btn-accent add" href="<?php echo $app->createUrl('event', 'create'); ?>"><?php \MapasCulturais\i::_e("Adicionar evento");?></a>
        </header>

        <div id="lista-dos-eventos" class="lista event" infinite-scroll="data.global.filterEntity === 'event' && addMore('event')" ng-show="data.global.filterEntity === 'event'">

            <article class="objeto clearfix" ng-repeat="event in events">
                <h1>
                    <a href="{{event.singleUrl}}">
                        {{event.name}}
                        <span class="event-subtitle">{{event.subTitle}}</span>
                    </a>
                </h1>
                <div class="objeto-content clearfix">
                    <a href="{{event.singleUrl}}" class="js-single-url">
                        <img class="objeto-thumb" ng-src="{{event['@files:avatar.avatarMedium'].url||defaultImageURL.replace('avatar','avatar--event')}}">
                    </a>
                    <div class="objeto-resumo">
                        <p>{{event.shortDescription}}</p>
                        <ul class="event-ocurrences">
                            <li ng-repeat="occ in event.occurrences">
                                <a href="{{occ.space.singleUrl}}">{{occ.space.name}}</a>
                                {{occ.space.endereco.trim()}}
                                {{occ.rule.description.trim()}}<span ng-show="occ.rule.price.length" >. {{occ.rule.price.trim()}}</span>.
                            </li>
                        </ul>
                    </div>
                    <div class="objeto-meta">
                        <div ng-if="event.project.name">
                            <span class="label"><?php \MapasCulturais\i::_e("Projeto");?>:</span>
                            <a href="{{event.project.singleUrl}}">{{event.project.name}}</a>
                        </div>
                        <div>
                            <span ng-show="event.terms.linguagem" class="label"><?php \MapasCulturais\i::_e("Linguagem");?>:</span>
                            <span ng-repeat="linguagem in event.terms.linguagem">
                                <a>{{linguagem}}</a>{{$last ? '' : ', '}}
                            </span>
                        </div>
                        <div><span class="label"><?php \MapasCulturais\i::_e("Classificação");?>:</span> <a ng-click="toggleSelection(data.event.classificacaoEtaria, getId(classificacoes, event.classificacaoEtaria))">{{event.classificacaoEtaria}}</a></div>
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
    </div>
