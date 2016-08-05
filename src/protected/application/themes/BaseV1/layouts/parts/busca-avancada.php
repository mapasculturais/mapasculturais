<div id="header-search-row" class="clearfix" ng-class="{'sombra':data.global.viewMode !== 'list'}">
    <?php if($app->isEnabled('events')): ?>
        <?php $this->part('search/filter', ['display_name' => 'Eventos', 'entity_name' => 'event']); ?>
    <?php endif; ?>
    <?php if($app->isEnabled('spaces')): ?>
        <?php
            ob_start();
            $this->dict('entities: Spaces');
            $show_name =  ob_get_clean();
            $this->part('search/filter', ['display_name' => $show_name, 'entity_name' => 'space']);
        ?>
    <?php endif; ?>
    <?php if($app->isEnabled('agents')): ?>
        <?php
            $this->part('search/filter', ['display_name' => 'Agentes', 'entity_name' => 'agent']);
        ?>
    <?php endif; ?>
    <?php if($app->isEnabled('projects')): ?>
        <?php $this->part('search/filter', ['display_name' => 'Projetos', 'entity_name' => 'project']); ?>
    <?php endif; ?>

    <div id="search-results-header" class="clearfix">
        <div id="search-tools" class="clearfix">
            <div id="view-tools" class="clearfix" ng-if="!showFilters('project')">
                <a class="hltip icon icon-show-search-on-list"  ng-click="data.global.viewMode='list'" ng-class="{'selected':data.global.viewMode === 'list'}" title="Ver resultados em lista"></a>
                <a class="hltip icon icon-show-search-on-map" ng-click="data.global.viewMode='map'"  ng-class="{'selected':data.global.viewMode === 'map'}" title="Ver resultados no mapa"></a>
            </div>
            <div id="export-tools" data-toggle="share-search-results">
                <a class="hltip icon icon-download" ng-href="{{apiURL}}&@type=excel" title="Exportar dados"></a>
            </div>
            <div id="share-tools">
                <a class="hltip icon icon-share" title="Compartilhar resultado"></a>
                <form id="share-url" class="share-search-results">
                    <label for="search-url">Compartilhar resultado: </label>
                    <input id="search-url" name="search-url" type="text" ng-value="location.absUrl()" />
                    <a target="_blank" ng-href="https://twitter.com/share?url={{location.absUrl()}}" class="icon icon-twitter"></a>
                    <a target="_blank" ng-href="https://www.facebook.com/sharer/sharer.php?u={{location.absUrl()}}" class="icon icon-facebook"></a>
                    <a target="_blank" ng-href="https://plus.google.com/share?url={{location.absUrl()}}" class="icon icon-googleplus"></a>
                </form>
            </div>
        </div>
        <!--#search-tools-->
        <div id="search-results">
            <span ng-show="spinnerCount > 0">
                <img src="<?php $this->asset('img/spinner.gif') ?>" />
                <span>obtendo resultados...</span>
            </span>
            <span ng-if="!spinnerCount">
                <span ng-if="numResults(numAgents, 'agent')">{{numResults(numAgents, 'agent')}} agente<span ng-show="numResults(numAgents, 'agent')!==1">s</span>
                    <span ng-if="data.global.viewMode === 'map' && resultsNotInMap.agent" style="cursor:default" class="hltip hltip-auto-update" title="{{resultsNotInMap.agent}} agentes sem localização">
                        ({{resultsNotInMap.agent}})
                    </span>
                </span>
                <!--,--><span ng-if="data.global.viewMode === 'map' && numResults(numAgents, 'agent') && (numResults(numSpaces, 'space') || numResults(numEvents.events, 'event'))">,</span>
                <span ng-if="numResults(numSpaces, 'space')">{{numResults(numSpaces, 'space')}} <?php $this->dict('entities: space') ?><span ng-show="numResults(numSpaces, 'space')!==1">s</span>
                    <span ng-if="data.global.viewMode === 'map' && resultsNotInMap.space" style="cursor:default" class="hltip hltip-auto-update" title="{{resultsNotInMap.space}} <?php $this->dict('entities: spaces') ?> sem localização">
                        ({{resultsNotInMap.space}})
                    </span>
                </span>
                <!--,--><span ng-if="data.global.viewMode === 'map' && numResults(numSpaces, 'space') && numResults(numEvents.events, 'event')">,</span>
                <span ng-if="data.global.viewMode === 'map' && numResults(numEvents.events, 'event')">{{numEvents.events}} evento<span ng-show="numEvents.events!==1">s</span>
                    em {{numResults(numEvents.spaces, 'event')}} <?php $this->dict('entities: space') ?><span ng-show="numResults(numEvents.spaces, 'event')!==1">s</span>
                    <span ng-if="data.global.viewMode === 'map' && resultsNotInMap.event" style="cursor:default" class="hltip hltip-auto-update" title="{{resultsNotInMap.event}} eventos sem localização">
                        ({{resultsNotInMap.event}})
                    </span>
                </span>
                <span ng-if="data.global.viewMode === 'list' && numEventsInList">{{numEventsInList}} evento<span ng-show="numEventsInList!==1">s</span> </span>

                <!--,--><span ng-if="data.global.viewMode === 'map' && (numResults(numAgents, 'agent') || numResults(numSpaces, 'space') || numResults(numEvents.events, 'event')) && numResults(numProjects, 'project')">,</span>
                <span ng-if="numProjects">{{numProjects}} projeto<span ng-show="numProjects!==1">s</span> </span>
            </span>
            <span ng-if="data.global.viewMode === 'map'" ng-show="spinnerCount===0 && (numResults(numEvents.events, 'event') === 0 || !showFilters('event')) && (numResults(numAgents, 'agent') === 0 || !showFilters('agent')) && (numResults(numSpaces, 'space') === 0 || !showFilters('space')) && (numProjects === 0 || !showFilters('project'))">Nenhum resultado encontrado
                    <span ng-if="resultsNotInMap.agent + resultsNotInMaps.space + resultsNotInMaps.event > 0" style="cursor:default" class="hltip hltip-auto-update" title="{{resultsNotInMap.agent + resultsNotInMaps.space + resultsNotInMaps.event}} resultados sem localização">
                        ({{resultsNotInMap.agent + resultsNotInMaps.space + resultsNotInMaps.event}})
                    </span>
            </span>

            <span ng-if="data.global.viewMode === 'list'" ng-show="spinnerCount===0 && numEventsInList == 0 || !showFilters('event') && (numAgents == 0 || !showFilters('agent')) && (numSpaces == 0 || !showFilters('space')) && (numProjects == 0 || !showFilters('project'))">Nenhum resultado encontrado</span>
        </div>
        <!--#search-results-->
        <div id="selected-filters">
            <a  class="tag-selected tag-{{data.global.filterEntity}}"
                ng-if="data[data.global.filterEntity].keyword !== ''"
                ng-click="data[data.global.filterEntity].keyword = ''">{{ data[data.global.filterEntity].keyword}}
            </a>
            <span   ng-repeat="(filter_k, filter_v) in data[data.global.filterEntity].filters">
                <a  class="tag-selected tag-{{data.global.filterEntity}}"
                    ng-repeat="value in filter_v"
                    ng-click="toggleSelection(filter_v, value)"
                    >
                    {{getFilterOptionLabel(filter_k, value)}}
                </a>
                <a  class="tag-selected tag-{{data.global.filterEntity}}"
                    ng-if="!getFilter(filter_k).isArray && filter_v"
                    ng-click="data[data.global.filterEntity].filters[filter_k] = !data[data.global.filterEntity].filters[filter_k]">
                    {{getFilterLabel(filter_k)}}
                </a>
            </span>

            <!-- Pesquisa através do Mapa do Google -->
            <a class="tag-selected" ng-if="data.global.locationFilters.enabled === 'circle'" ng-click="cleanLocationFilters()">Área Delimitada</a>
            <a class="tag-selected" ng-if="data.global.locationFilters.enabled === 'neighborhood'" ng-click="cleanLocationFilters()">Próximo a mim</a>
            <a class="tag-selected" ng-if="data.global.locationFilters.enabled === 'address'" ng-click="cleanLocationFilters()">{{data.global.locationFilters.address.text}}</a>

            <a class="tag-selected tag-clear" ng-if="hasFilter()" ng-click="cleanAllFilters()">Remover todos filtros</a>
        </div>
    </div>
    <!--#header-search-results-->
</div>
<!--#header-search-row-->
