<?php
    $this->layout = 'interna';

    add_taxonoy_terms_to_js('area');
    add_taxonoy_terms_to_js('linguagem');
    add_entity_types_to_js('MapasCulturais\Entities\Space');
    add_entity_types_to_js('MapasCulturais\Entities\Agent');

    //add_map_icon_marker_assets();

    $app->enqueueScript('vendor', 'angular', '/vendor/angular.min.js');
    $app->enqueueScript('vendor', 'angular-rison', '/vendor/angular-rison/angular-rison.min.js');
    $app->enqueueScript('app', 'ng-mapasculturais', '/js/ng-mapasculturais.js');
    $app->enqueueScript('app', 'SearchService', '/js/SearchService.js');
    $app->enqueueScript('app', 'FindOneService', '/js/FindOneService.js');
    $app->enqueueScript('app', 'SearchMapController', '/js/SearchMap.js');
    $app->enqueueScript('app', 'SearchSpatial', '/js/SearchSpatial.js');
    $app->enqueueScript('app', 'Search', '/js/Search.js');

    $app->enqueueScript('vendor', 'spin.js', '/vendor/spin.min.js', array('angular'));
    $app->enqueueScript('vendor', 'angularSpinner', '/vendor/angular-spinner.min.js', array('spin.js'));



?>


<?php add_map_assets(); ?>
    <div id="infobox" style="display:block" ng-show="data.global.openEntity.id>0 && data.global.viewMode==='map'">

        <a class="icone icon_close" ng-click="data.global.openEntity=null"></a>

        <article class="objeto agente clearfix" ng-if="openEntity.agent">
            <h1><a href="{{openEntity.agent.singleUrl}}">{{openEntity.agent.name}}</a></h1>
            <img class="objeto-thumb" ng-src="{{openEntity.agent['@files:avatar.avatarBig'].url||defaultImageURL}}">
            <p class="objeto-resumo">{{openEntity.agent.shortDescription}}</p>
            <div class="objeto-meta">
                <div><span class="label">Tipo:</span> <a href="#">{{openEntity.agent.type.name}}</a></div>
                <div>
                    <span class="label">Áreas de atuação:</span>
                        <span ng-repeat="area in openEntity.agent.terms.area">
                            <a href="#">{{area}}</a>{{$last ? '' : ', '}}
                        </span>
                </div>
            </div>

        </article>

        <article class="objeto espaco clearfix" ng-if="openEntity.space">
            <article class="objeto espaco clearfix">
                <h1><a href="{{openEntity.space.singleUrl}}">{{openEntity.space.name}}</a></h1>
                <div class="objeto-content clearfix">
                    <a href="{{openEntity.space.singleUrl}}" class="js-single-url">
                        <img class="objeto-thumb" ng-src="{{openEntity.space['@files:avatar.avatarBig'].url||defaultImageURL}}">
                    </a>
                    <p class="objeto-resumo">{{openEntity.space.shortDescription}}</p>
                    <div class="objeto-meta">
                        <div><span class="label">Tipo:</span> <a ng-click="data.space.types.push(openEntity.space.type.id)">{{openEntity.space.type.name}}</a></div>
                        <div>
                            <span class="label">Área de atuação:</span>
                            <span ng-repeat="area in openEntity.space.terms.area">
                                <a>{{area}}</a>{{$last ? '' : ', '}}
                            </span>
                        </div>
                        <div><span class="label">Local:</span>{{openEntity.space.metadata.endereco}}</div>
                        <div><span class="label">Acessibilidade:</span>{{openEntity.space.metadata.acessibilidade}}</div>
                    </div>
                </div>
            </article>
        </article>

        <article class="objeto evento clearfix" ng-if="openEntity.event">
            <h1>{{openEntity.event.name}}</h1>
            evento
            <img class="objeto-thumb" ng-src="{{openEntity.event['@files:avatar.avatarBig'].url||defaultImageURL}}">
        </article>
        <!--
        ABAIXO O HTML DOS EVENTOS!!!
        O LOOP É IGUALZINHO AO LOOP DO RESULTADO DA BUSCA EM LISTA, PORÉM SEM O LOCAL, POIS ESTE JÁ VEM ANTES.
        QUANDO FOR FAZER A PROGRAMAÇÃO FALAR COM A CÁTIA POIS PRECISO EXPLICAR E VAI PRECISAR ALTERAR O JS!!!!!!!
        E DEPOIS DE PRONTO NÃO ESQUEÇA DE REMOVER ESSE COMENTÁRIO
        <p class="espaco-dos-eventos">Eventos encontrados em:<br>
        <a href="#">Nome do Espaço<br>
        Rua Fulano de Tal, 200 - CEP 00000-000</a></p>
        <article class="objeto evento clearfix">
            <h1><a href="#">Nome do evento</a></h1>
            <div class="objeto-content clearfix">
                <a href="#" class="js-single-url">
                    <img class="objeto-thumb" ng-src="{{space['@files:avatar.avatarBig'].url||defaultImageURL}}">
                </a>
                <p class="objeto-resumo">descrição curta do evento</p>
                <div class="objeto-meta">
                    <div><span class="label">Linguagem:</span> <a href="#">Música</a></div>
                    <div><span class="label">Horário:</span> <time>00h00</time></div>
                    <div><span class="label">Classificação:</span> livre</div>
                </div>
            </div>
        </article>
        -->
    </div>
    <div id="mapa" ng-controller="SearchMapController"  ng-show="data.global.viewMode!=='list'" ng-animate="{show:'animate-show', hide:'animate-hide'}" class="js-map" data-options='{"dragging":true, "zoomControl":true, "doubleClickZoom":true, "scrollWheelZoom":true }'>

    </div>
    <div id="lista" ng-show="data.global.viewMode==='list'" ng-animate="{show:'animate-show', hide:'animate-hide'}">
        <header id="header-dos-agentes" class="header-do-objeto clearfix">
            <h1><span class="icone icon_profile"></span> Agentes</h1>
            <a class="botao adicionar" href="<?php echo $app->createUrl('agent', 'create'); ?>">Adicionar agente</a>
            <a class="icone arrow_carrot-down" href="#"></a>
        </header>
        <div id="lista-dos-agentes" class="lista">
            <article class="objeto agente clearfix" ng-repeat="agent in agentSearch.results" id="agent-result-{{agent.id}}">
                <h1><a href="{{agent.singleUrl}}">{{agent.name}}</a></h1>
                <div class="objeto-content clearfix">
                    <a href="{{agent.singleUrl}}" class="js-single-url">
                        <img class="objeto-thumb" ng-src="{{agent['@files:avatar.avatarBig'].url||defaultImageURL}}">
                    </a>
                    <p class="objeto-resumo">{{agent.shortDescription}}</p>
                    <div class="objeto-meta">
                        <div><span class="label">Tipo:</span> <a href="#">{{agent.type.name}}</a></div>
                        <div>
                            <span class="label">Área de atuação:</span>
                            <span ng-repeat="area in agent.terms.area">
                                <a href="#">{{area}}</a>{{$last ? '' : ', '}}
                            </span>
                        </div>
                    </div>
                </div>
            </article>
        </div>
        <header id="header-dos-espacos" class="header-do-objeto clearfix">
            <h1><span class="icone icon_building"></span> Espaços</h1>
            <a class="botao adicionar" href="<?php echo $app->createUrl('space', 'create'); ?>">Adicionar espaço</a>
            <a class="icone arrow_carrot-down" href="#"></a>
        </header>
        <div id="lista-dos-espacos" class="lista">
            <article class="objeto espaco clearfix" ng-repeat="space in spaceSearch.results" id="space-result-{{space.id}}">
                <h1><a href="{{space.singleUrl}}">{{space.name}}</a></h1>
                <div class="objeto-content clearfix">
                    <a href="{{agent.singleUrl}}" class="js-single-url">
                        <img class="objeto-thumb" ng-src="{{space['@files:avatar.avatarBig'].url||defaultImageURL}}">
                    </a>
                    <p class="objeto-resumo">{{space.shortDescription}}</p>
                    <div class="objeto-meta">
                        <div><span class="label">Tipo:</span> <a href="#">{{space.type.name}}</a></div>
                        <div>
                            <span class="label">Área de atuação:</span>
                            <span ng-repeat="area in space.terms.area">
                                <a href="#">{{area}}</a>{{$last ? '' : ', '}}
                            </span>
                        </div>
                        <div><span class="label">Local:</span>{{space.metadata.endereco}}</div>
                        <div><span class="label">Acessibilidade:</span>{{space.metadata.acessibilidade}}</div>
                    </div>
                </div>
            </article>
        </div>
    </div>