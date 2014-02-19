<?php
    $this->layout = 'interna';

    add_taxonoy_terms_to_js('area');
    add_taxonoy_terms_to_js('linguagem');
    add_entity_types_to_js('MapasCulturais\Entities\Space');
    add_entity_types_to_js('MapasCulturais\Entities\Agent');

    //add_map_icon_marker_assets();

    $app->enqueueScript('vendor', 'angular', '/vendor/angular.min.js');
    $app->enqueueScript('app', 'ng-mapasculturais', '/js/ng-mapasculturais.js');
    $app->enqueueScript('app', 'SearchService', '/js/SearchService.js');
    $app->enqueueScript('app', 'SearchSpatial', '/js/SearchSpatial.js');
    $app->enqueueScript('app', 'busca', '/js/buscanova.js');
    //$app->enqueueScript('vendor', 'angular-sham-spinner' '/vendor/angular-sham-spinner-master/angular-sham.spinner.js', array('angular'));
    //$app->enqueueScript('vendor', 'ngProgress', '/vendor/ngProgress.min.js', array('angular'));
    $app->enqueueScript('vendor', 'spin.js', '/vendor/spin.min.js', array('angular'));
    $app->enqueueScript('vendor', 'angularSpinner', '/vendor/angular-spinner.min', array('spin.js'));
?>


<?php add_map_assets(); ?>
    <div id="infobox" class="objeto">
        <a class="icone icon_close" href="#" onclick="this.parentElement.style.display='none'"></a>
        <article></article>
    </div>
    <div id="mapa" class="js-map" data-options='{"dragging":true, "zoomControl":true, "doubleClickZoom":true, "scrollWheelZoom":true }'>

    </div>
    <div id="lista">
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
            <h1><span class="icone icon_profile"></span> Espaços</h1>
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