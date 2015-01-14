<div id="header-search-row" class="clearfix" ng-class="{'sombra':data.global.viewMode !== 'list'}">
    <div id="filter-projects" class="entity-filter clearfix" ng-show="data.global.filterEntity === 'project'">
        <header class="clearfix">
            <a href="<?php echo $app->getBaseUrl() ?>" class="icone arrow_left"></a>
            Projetos
            <a class="icone icon_adjust-vert" ng-click="toggleAdvancedFilters()"></a>
        </header>
        <div ng-show="!collapsedFilters && !showInfobox()">
            <form class="form-palavra-chave filter">
                <label for="palavra-chave-evento">Palavra-chave</label>
                <input ng-model="data.project.keyword" class="search-field" type="text" name="palavra-chave-evento" placeholder="Buscar projetos" />
            </form>
            <!--.filter-->
            <div class="filter">
                <span class="label">Tipo</span>
                <div class="dropdown">
                    <div class="placeholder">Selecione os tipos</div>
                    <div class="submenu-dropdown">
                        <ul class="filter-list">
                            <li ng-repeat="type in types.project" ng-class="{'selected':isSelected(data.project.types, type.id)}" ng-click="toggleSelection(data.project.types, type.id)">
                                <span>{{type.name}}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!--.filter-->
            <div class="filter">
                <span class="icone icon_check" ng-class="{'selected': data.project.ropen}" ng-click="data.project.ropen = !data.project.ropen"></span>
                <span class="label show-label" ng-click="data.project.ropen = !data.project.ropen">Inscrições Abertas</span>
            </div>
            <!--.filter-->
            <div class="filter verified-filter">
                <a class="hltip btn btn-verified" ng-class="{'selected':data.project.isVerified}" title="Exibir somente resultados verificados" ng-click="toggleVerified('project')"><?php $this->dict('search: verified results') ?></a>
            </div>
            <!-- div.verified-filter -->
        </div>
    </div>
    <!--#filter-projects-->
    <div id="filter-events" class="entity-filter clearfix" ng-show="data.global.filterEntity === 'event'">
        <header class="clearfix">
            <a href="<?php echo $app->getBaseUrl() ?>" class="icone arrow_left"></a>
            Eventos
            <a class="icone icon_adjust-vert" ng-click="toggleAdvancedFilters()"></a>
        </header>
        <div ng-show="!collapsedFilters && !showInfobox()">
            <form class="form-palavra-chave filter">
                <label for="palavra-chave-evento">Palavra-chave</label>
                <input ng-model="data.event.keyword" class="search-field" type="text" name="palavra-chave-evento" placeholder="Buscar eventos" />
            </form>
            <!--.filter-->
            <div class="filter">
                <label class="show-label" for="data-de-inicio">De</label>
                <input id="data-de-inicio" class="data" ng-model="data.event.from" ui-date="dateOptions" ui-date-format="yy-mm-dd" placeholder="00/00/0000" readonly="readonly" /> <label class="show-label">a</label>
                <input class="data" ng-model="data.event.to" ui-date="dateOptions" ui-date-format="yy-mm-dd" placeholder="00/00/0000" readonly="readonly" />
            </div>
            <!--.filter-->
            <div class="filter">
                <label>Linguagem</label>
                <div class="dropdown">
                    <div class="placeholder">Selecione as linguagens</div>
                    <div class="submenu-dropdown">
                        <ul class="filter-list select">
                            <li ng-repeat="linguagem in linguagens" ng-class="{'selected':isSelected(data.event.linguagens, linguagem.id)}" ng-click="toggleSelection(data.event.linguagens, linguagem.id)">
                                <span>{{linguagem.name}}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!--.filter-->
            <div class="filter">
                <span class="label">Classificação</span>
                <div id="classificacao" class="dropdown">
                    <div class="placeholder">Selecione a classificação</div>
                    <div class="submenu-dropdown">
                        <ul class="filter-list select">
                            <li ng-repeat="classificacao in classificacoes" ng-class="{'selected':isSelected(data.event.classificacaoEtaria, classificacao.id)}" ng-click="toggleSelection(data.event.classificacaoEtaria, classificacao.id)">
                                <span>{{classificacao.name}}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!--.filter-->
            <div class="filter verified-filter">
                <a class="hltip btn btn-verified" ng-class="{'selected':data.event.isVerified}" title="Exibir somente resultados Verificados" ng-click="toggleVerified('event')"><?php $this->dict('search: verified results') ?></a>
            </div>
            <!-- div.verified-filter -->
        </div>
    </div>
    <!--#filter-events-->
    <div id="filter-agents" class="entity-filter clearfix" ng-show="data.global.filterEntity === 'agent'">
        <header class="clearfix">
            <a href="<?php echo $app->getBaseUrl() ?>" class="icone arrow_left"></a>
            Agentes
            <a class="icone icon_adjust-vert" ng-click="toggleAdvancedFilters()"></a>
        </header>
        <div ng-show="!collapsedFilters && !showInfobox()">
            <form class="form-palavra-chave filter">
                <label>Palavra-chave</label>
                <input ng-model="data.agent.keyword" class="search-field" type="text" name="busca" placeholder="Buscar agentes" />
            </form>
            <!--.filter-->
            <div class="filter">
                <span class="label">Área de Atuação</span>
                <div class="dropdown">
                    <div class="placeholder">Selecione as áreas</div>
                    <div class="submenu-dropdown">
                        <ul class="filter-list">
                            <li ng-repeat="area in areas" ng-class="{'selected':isSelected(data.agent.areas, area.id)}" ng-click="toggleSelection(data.agent.areas, area.id)">
                                <span>{{area.name}}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!--.filter-->
            <div class="filter">
                <span class="label">Tipo</span>
                <div id="tipo-de-agente" class="dropdown" data-closeonclick="true">
                    <div class="placeholder">{{getName(types.agent, data.agent.type)}}&nbsp;</div>
                    <div class="submenu-dropdown">
                        <ul>
                            <li ng-repeat="type in types.agent" ng-click="data.agent.type = type.id">
                                <span>{{type.name}}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!--.filter-->
            <div class="filter verified-filter">
                <a class="hltip btn btn-verified" ng-class="{'selected':data.agent.isVerified}" title="Exibir somente resultados Verificados" ng-click="toggleVerified('agent')"><?php $this->dict('search: verified results') ?></a>
            </div>
            <!-- div.verified-filter -->
        </div>
    </div>
    <!--#filter-agents-->
    <div id="filter-spaces" class="entity-filter clearfix" ng-show="data.global.filterEntity === 'space'">
        <header class="clearfix">
            <a href="<?php echo $app->getBaseUrl() ?>" class="icone arrow_left"></a>
            Espaços
            <a class="icone icon_adjust-vert" ng-click="toggleAdvancedFilters()"></a>
        </header>
        <div ng-show="!collapsedFilters && !showInfobox()">
            <form class="form-palavra-chave filter">
                <label for="palavra-chave-espaco">Palavra-chave</label>
                <input ng-model="data.space.keyword" class="search-field" type="text" name="palavra-chave-espaco" placeholder="Buscar espaços" />
            </form>
            <!--.filter-->
            <div class="filter">
                <span class="label">Área de Atuação</span>
                <div class="dropdown">
                    <div class="placeholder">Selecione as áreas</div>
                    <div class="submenu-dropdown">
                        <ul class="filter-list">
                            <li ng-repeat="area in areas" ng-class="{'selected':isSelected(data.space.areas, area.id)}" ng-click="toggleSelection(data.space.areas, area.id)">
                                <span>{{area.name}}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!--.filter-->
            <div class="filter">
                <span class="label">Tipo</span>
                <div class="dropdown">
                    <div class="placeholder">Selecione os tipos</div>
                    <div class="submenu-dropdown">
                        <ul class="filter-list">
                            <li ng-repeat="type in types.space" ng-class="{'selected':isSelected(data.space.types, type.id)}" ng-click="toggleSelection(data.space.types, type.id)">
                                <span>{{type.name}}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!--.filter-->
            <div class="filter">
                <span class="icone icon_check" ng-click="data.space.acessibilidade = !data.space.acessibilidade" ng-class="{'selected':data.space.acessibilidade}"></span>
                <span class="label show-label" ng-click="data.space.acessibilidade = !data.space.acessibilidade">Acessibilidade</span>
            </div>
            <!--.filter-->
            <div class="filter verified-filter">
                <a class="hltip btn btn-verified" ng-class="{'selected':data.space.isVerified}" title="Exibir somente resultados Verificados" ng-click="toggleVerified('space')"><?php $this->dict('search: verified results') ?></a>
            </div>
            <!-- div.verified-filter -->
        </div>
    </div>
    <!--#filter-spaces-->
    <div id="search-results-header" class="clearfix">
        <div id="search-tools" class="clearfix">
            <div id="view-tools" class="clearfix" ng-if="!showFilters('project')">
                <a class="hltip icone icon_menu-square_alt"  ng-click="data.global.viewMode='list'" ng-class="{'selected':data.global.viewMode === 'list'}" title="Ver resultados em lista"></a>
                <a class="hltip icone icon_map" ng-click="data.global.viewMode='map'"  ng-class="{'selected':data.global.viewMode === 'map'}" title="Ver resultados no mapa"></a>
            </div>
            <div id="export-tools" data-toggle="share-search-results">
                <a class="hltip icone icon_download" ng-href="{{apiURL}}&@type=excel" title="Exportar dados"></a>
            </div>
            <div id="share-tools">
                <a class="hltip icone social_share" title="Compartilhar resultado"></a>
                <form id="share-url" class="share-search-results">
                    <label for="search-url">Compartilhar resultado: </label>
                    <input id="search-url" name="search-url" type="text" ng-value="location.absUrl()" />
                    <a target="_blank" ng-href="https://twitter.com/share?url={{location.absUrl()}}" class="icone social_twitter"></a>
                    <a target="_blank" ng-href="https://www.facebook.com/sharer/sharer.php?u={{location.absUrl()}}" class="icone social_facebook"></a>
                    <a target="_blank" ng-href="https://plus.google.com/share?url={{location.absUrl()}}" class="icone social_googleplus"></a>
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
                <span ng-if="numResults(numSpaces, 'space')">{{numResults(numSpaces, 'space')}} espaço<span ng-show="numResults(numSpaces, 'space')!==1">s</span>
                    <span ng-if="data.global.viewMode === 'map' && resultsNotInMap.space" style="cursor:default" class="hltip hltip-auto-update" title="{{resultsNotInMap.space}} espaços sem localização">
                        ({{resultsNotInMap.space}})
                    </span>
                </span>
                <!--,--><span ng-if="data.global.viewMode === 'map' && numResults(numSpaces, 'space') && numResults(numEvents.events, 'event')">,</span>
                <span ng-if="data.global.viewMode === 'map' && numResults(numEvents.events, 'event')">{{numEvents.events}} evento<span ng-show="numEvents.events!==1">s</span>
                    em {{numResults(numEvents.spaces, 'event')}} espaço<span ng-show="numResults(numEvents.spaces, 'event')!==1">s</span>
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
            <a class="tag-selected tag-event" ng-if="showFilters('event') && data.event.keyword !== ''" ng-click="data.event.keyword = ''">{{ data.event.keyword}}</a>
            <a class="tag-selected tag-agent" ng-if="showFilters('agent') && data.agent.keyword !== ''" ng-click="data.agent.keyword = ''">{{ data.agent.keyword}}</a>
            <a class="tag-selected tag-space" ng-if="showFilters('space') && data.space.keyword !== ''" ng-click="data.space.keyword = ''">{{ data.space.keyword}}</a>
            <a class="tag-selected tag-project" ng-if="showFilters('project') && data.project.keyword !== ''" ng-click="data.project.keyword = ''">{{ data.project.keyword}}</a>

            <a class="tag-selected tag-agent" ng-if="showFilters('agent') && data.agent.type !== null" ng-click="data.agent.type = null">{{ getName(types.agent, data.agent.type)}}</a>
            <a class="tag-selected tag-space" ng-if="showFilters('space')" ng-repeat="typeId in data.space.types" ng-click="toggleSelection(data.space.types, typeId)">{{ getName(types.space, typeId)}}</a>
            <a class="tag-selected tag-project" ng-if="showFilters('project')" ng-repeat="typeId in data.project.types" ng-click="toggleSelection(data.project.types, typeId)">{{ getName(types.project, typeId)}}</a>

            <a class="tag-selected tag-event" ng-if="showFilters('event')" ng-repeat="linguagemId in data.event.linguagens" ng-click="toggleSelection(data.event.linguagens, linguagemId)">{{ getName(linguagens, linguagemId)}}</a>
            <a class="tag-selected tag-event" ng-if="showFilters('event')" ng-repeat="clasificacaoId in data.event.classificacaoEtaria" ng-click="toggleSelection(data.event.classificacaoEtaria, clasificacaoId)">{{ getName(classificacoes, clasificacaoId)}}</a>

            <a class="tag-selected tag-agent" ng-if="showFilters('agent')" ng-repeat="areaId in data.agent.areas" ng-click="toggleSelection(data.agent.areas, areaId)">{{ getName(areas, areaId)}}</a>
            <a class="tag-selected tag-space" ng-if="showFilters('space')" ng-repeat="areaId in data.space.areas" ng-click="toggleSelection(data.space.areas, areaId)">{{ getName(areas, areaId)}}</a>

            <a class="tag-selected tag-space" ng-if="showFilters('space') && data.space.acessibilidade" ng-click="data.space.acessibilidade = !data.space.acessibilidade">acessibilidade</a>

            <a class="tag-selected tag-project" ng-if="showFilters('project') && data.project.ropen" ng-click="data.project.ropen = !data.project.ropen">inscrições abertas</a>

            <a class="tag-selected tag-event" ng-if="showFilters('event') && data.event.isVerified" ng-click="toggleVerified('event')"><?php $this->dict('search: verified'); ?></a>
            <a class="tag-selected tag-agent" ng-if="showFilters('agent') && data.agent.isVerified" ng-click="toggleVerified('agent')"><?php $this->dict('search: verified'); ?></a>
            <a class="tag-selected tag-space" ng-if="showFilters('space') && data.space.isVerified" ng-click="toggleVerified('space')"><?php $this->dict('search: verified'); ?></a>
            <a class="tag-selected tag-project" ng-if="showFilters('project') && data.project.isVerified" ng-click="toggleVerified('project')"><?php $this->dict('search: verified'); ?></a>

            <a class="tag-selected tag-event" ng-if="showFilters('event') && showEventDateFilter()" ng-click="cleanEventDateFilters()">{{eventDateFilter()}}</a>

            <a class="tag-selected" ng-if="data.global.locationFilters.enabled === 'circle'" ng-click="cleanLocationFilters()">Área Delimitada</a>
            <a class="tag-selected" ng-if="data.global.locationFilters.enabled === 'neighborhood'" ng-click="cleanLocationFilters()">Próximo a mim</a>
            <a class="tag-selected" ng-if="data.global.locationFilters.enabled === 'address'" ng-click="cleanLocationFilters()">{{data.global.locationFilters.address.text}}</a>

            <a class="tag-selected tag-clear" ng-if="hasFilter()" ng-click="cleanAllFilters()">Remover todos filtros</a>
        </div>
    </div>
    <!--#header-search-results-->
</div>
<!--#header-search-row-->
