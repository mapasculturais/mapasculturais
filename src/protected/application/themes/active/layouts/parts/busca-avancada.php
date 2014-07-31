<div id="busca" ng-class="{'sombra':data.global.viewMode !== 'list'}">
    <div id="busca-avancada" class="clearfix">

        <div id="filtro-projetos" class="filtro-objeto clearfix" ng-show="data.global.filterEntity === 'project'">
            <form class="form-palavra-chave filtro">
                <label for="palavra-chave-evento">Palavra-chave</label>
                <input ng-model="data.project.keyword" class="campo-de-busca" type="text" name="palavra-chave-evento" placeholder="Buscar projetos" />
            </form>
            <!--#busca-->
            <div class="filtro">
                <span class="label">Tipo</span>
                <div class="dropdown">
                    <div class="placeholder">Selecione os tipos</div>
                    <div class="submenu-dropdown">
                        <ul class="lista-de-filtro">
                            <li ng-repeat="type in types.project" ng-class="{'selected':isSelected(data.project.types, type.id)}" ng-click="toggleSelection(data.project.types, type.id)">
                                <span>{{type.name}}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!--.filtro-->
            <div class="filtro">
                <span class="icone icon_check" ng-class="{'selected': data.project.ropen}" ng-click="data.project.ropen = !data.project.ropen"></span>
                <span class="label show-label" ng-click="data.project.ropen = !data.project.ropen">Inscrições Abertas</span>
            </div>
            <!--.filtro-->
            <div class="filtro filtro-prefeitura">
                <a class="hltip botao" ng-class="{'selected':data.project.isVerified}" title="Exibir somente resultados da Secretaria Municipal de Cultura" ng-click="toggleVerified('project')">Resultados da SMC</a>
            </div>
            <!-- div.filtro-prefeitura -->

        </div>
        <!--#filtro-projetos-->

        <div id="filtro-eventos" class="filtro-objeto clearfix" ng-show="data.global.filterEntity === 'event'">
            <form class="form-palavra-chave filtro">
                <label for="palavra-chave-evento">Palavra-chave</label>
                <input ng-model="data.event.keyword" class="campo-de-busca" type="text" name="palavra-chave-evento" placeholder="Buscar eventos" />
            </form>
            <!--#busca-->
            <div class="filtro">
                <label class="show-label" for="data-de-inicio">Entre</label>
                <input id="data-de-inicio" class="data" ng-model="data.event.from" ui-date="dateOptions" ui-date-format="yy-mm-dd" placeholder="00/00/0000" readonly="readonly" /> <label class="show-label">a</label>
                <input class="data" ng-model="data.event.to" ui-date="dateOptions" ui-date-format="yy-mm-dd" placeholder="00/00/0000" readonly="readonly" />
            </div>
            <!--.filtro-->
            <div class="filtro">
                <label>Linguagem</label>
                <div class="dropdown">
                    <div class="placeholder">Selecione as linguagens</div>
                    <div class="submenu-dropdown">
                        <ul class="lista-de-filtro select">
                            <li ng-repeat="linguagem in linguagens" ng-class="{'selected':isSelected(data.event.linguagens, linguagem.id)}" ng-click="toggleSelection(data.event.linguagens, linguagem.id)">
                                <span>{{linguagem.name}}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!--.filtro-->
            <div class="filtro">
                <span class="label">Classificação</span>
                <div id="classificacao" class="dropdown">
                    <div class="placeholder">Selecione a classificação</div>
                    <div class="submenu-dropdown">
                        <ul class="lista-de-filtro select">
                            <li ng-repeat="classificacao in classificacoes" ng-class="{'selected':isSelected(data.event.classificacaoEtaria, classificacao.id)}" ng-click="toggleSelection(data.event.classificacaoEtaria, classificacao.id)">
                                <span>{{classificacao.name}}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!--.filtro-->
            <div class="filtro filtro-prefeitura">
                <a class="hltip botao" ng-class="{'selected':data.event.isVerified}" title="Exibir somente resultados da Secretaria Municipal de Cultura" ng-click="toggleVerified('event')">Resultados da SMC</a>
            </div>
            <!-- div.filtro-prefeitura -->
        </div>
        <!--#filtro-eventos-->
        <div id="filtro-agentes" class="filtro-objeto clearfix" ng-show="data.global.filterEntity === 'agent'">
            <form class="form-palavra-chave filtro">
                <label>Palavra-chave</label>
                <input ng-model="data.agent.keyword" class="campo-de-busca" type="text" name="busca" placeholder="Buscar agentes" />
            </form>
            <!--#busca-->
            <div class="filtro">
                <span class="label">Área de Atuação</span>
                <div class="dropdown">
                    <div class="placeholder">Selecione as áreas</div>
                    <div class="submenu-dropdown">
                        <ul class="lista-de-filtro">
                            <li ng-repeat="area in areas" ng-class="{'selected':isSelected(data.agent.areas, area.id)}" ng-click="toggleSelection(data.agent.areas, area.id)">
                                <span>{{area.name}}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!--.filtro-->
            <div class="filtro">
                <span class="label">Tipo</span>
                <div id="tipo-de-agente" class="dropdown">
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
            <!--.filtro-->
            <div class="filtro filtro-prefeitura">
                <a class="hltip botao" ng-class="{'selected':data.agent.isVerified}" title="Exibir somente resultados da Secretaria Municipal de Cultura" ng-click="toggleVerified('agent')">Resultados da SMC</a>
            </div>
            <!-- div.filtro-prefeitura -->
        </div>
        <!--#filtro-agentes-->
        <div id="filtro-espacos" class="filtro-objeto clearfix" ng-show="data.global.filterEntity === 'space'">
            <form class="form-palavra-chave filtro">
                <label for="palavra-chave-espaco">Palavra-chave</label>
                <input ng-model="data.space.keyword" class="campo-de-busca" type="text" name="palavra-chave-espaco" placeholder="Buscar espaços" />
            </form>
            <!--#busca-->
            <div class="filtro">
                <span class="label">Área de Atuação</span>
                <div class="dropdown">
                    <div class="placeholder">Selecione as áreas</div>
                    <div class="submenu-dropdown">
                        <ul class="lista-de-filtro">
                            <li ng-repeat="area in areas" ng-class="{'selected':isSelected(data.space.areas, area.id)}" ng-click="toggleSelection(data.space.areas, area.id)">
                                <span>{{area.name}}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!--.filtro-->
            <div class="filtro">
                <span class="label">Tipo</span>
                <div class="dropdown">
                    <div class="placeholder">Selecione os tipos</div>
                    <div class="submenu-dropdown">
                        <ul class="lista-de-filtro">
                            <li ng-repeat="type in types.space" ng-class="{'selected':isSelected(data.space.types, type.id)}" ng-click="toggleSelection(data.space.types, type.id)">
                                <span>{{type.name}}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!--.filtro-->
            <div class="filtro">
                <span class="icone icon_check" ng-click="data.space.acessibilidade = !data.space.acessibilidade" ng-class="{'selected':data.space.acessibilidade}"></span>
                <span class="label show-label" ng-click="data.space.acessibilidade = !data.space.acessibilidade">Acessibilidade</span>
            </div>
            <!--.filtro-->
            <div class="filtro filtro-prefeitura">
                <a class="hltip botao" ng-class="{'selected':data.space.isVerified}" title="Exibir somente resultados da Secretaria Municipal de Cultura" ng-click="toggleVerified('space')">Resultados da SMC</a>
            </div>
            <!-- div.filtro-prefeitura -->
        </div>
        <!--#filtro-espacos-->
        <!--#busca-avancada-->
        <div id="header-dos-resultados" class="clearfix">
            <style>#resultados{width:auto; float:left; position:static;} #filtros-selecionados{float:left; margin-left: auto;}</style>
            <div id="resultados">
                <span ng-show="spinnerCount > 0" style="display:inline">
                    <span style="display:inline" us-spinner="{radius:2, width:2, length: 10, lines:11, top:0, left:1, speed:2}"></span>
                    <span style="margin-left:35px">obtendo resultados...</span>
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
                    <span ng-if="data.global.viewMode === 'map' && numResults(numEvents.events, 'event')">{{numResults(numEvents.events, 'event')}} evento<span ng-show="numResults(numEvents.events, 'event')!==1">s</span>
                        em {{numResults(numEvents.spaces, 'event')}} espaço<span ng-show="numResults(numEvents.spaces, 'event')!==1">s</span>
                        <span ng-if="data.global.viewMode === 'map' && resultsNotInMap.event" style="cursor:default" class="hltip hltip-auto-update" title="{{resultsNotInMap.event}} eventos sem localização">
                            ({{resultsNotInMap.event}})
                        </span>
                    </span>
                    <span ng-if="data.global.viewMode === 'list' && numEventsInList">{{numEventsInList}} evento<span ng-show="numEventsInList!==1">s</span> </span>

                    <!--,--><span ng-if="data.global.viewMode === 'map' && (numResults(numAgents, 'agent') || numResults(numSpaces, 'space') || numResults(numEvents.events, 'event')) && numResults(numProjects, 'project')">,</span>
                    <span ng-if="numProjects">{{numProjects}} projeto<span ng-show="numProjects!==1">s</span> </span>
                </span>
                <span ng-if="data.global.viewMode === 'map'" ng-show="spinnerCount===0 && (numResults(numEvents.events, 'event') === 0 || !showFilters('event')) && (numResults(numAgents, 'agent') === 0 || !showFilters('agent')) && (numResults(numSpaces, 'space') === 0 || !showFilters('space')) && (numProjects === 0 || !showFilters('project'))">Nenhum resultado encontrado</span>
                <span ng-if="data.global.viewMode === 'list'" ng-show="spinnerCount===0 && numEventsInList == 0 || !showFilters('event') && (numAgents == 0 || !showFilters('agent')) && (numSpaces == 0 || !showFilters('space')) && (numProjects == 0 || !showFilters('project'))">Nenhum resultado encontrado</span>

            </div>
            <!--#resultados-->
            <div id="filtros-selecionados">
                <a class="tag tag-evento" ng-if="showFilters('event') && data.event.keyword !== ''" ng-click="data.event.keyword = ''">{{ data.event.keyword}}</a>
                <a class="tag tag-agente" ng-if="showFilters('agent') && data.agent.keyword !== ''" ng-click="data.agent.keyword = ''">{{ data.agent.keyword}}</a>
                <a class="tag tag-espaco" ng-if="showFilters('space') && data.space.keyword !== ''" ng-click="data.space.keyword = ''">{{ data.space.keyword}}</a>
                <a class="tag tag-projeto" ng-if="showFilters('project') && data.project.keyword !== ''" ng-click="data.project.keyword = ''">{{ data.project.keyword}}</a>

                <a class="tag tag-agente" ng-if="showFilters('agent') && data.agent.type !== null" ng-click="data.agent.type = null">{{ getName(types.agent, data.agent.type)}}</a>
                <a class="tag tag-espaco" ng-if="showFilters('space')" ng-repeat="typeId in data.space.types" ng-click="toggleSelection(data.space.types, typeId)">{{ getName(types.space, typeId)}}</a>
                <a class="tag tag-projeto" ng-if="showFilters('project')" ng-repeat="typeId in data.project.types" ng-click="toggleSelection(data.project.types, typeId)">{{ getName(types.project, typeId)}}</a>

                <a class="tag tag-evento" ng-if="showFilters('event')" ng-repeat="linguagemId in data.event.linguagens" ng-click="toggleSelection(data.event.linguagens, linguagemId)">{{ getName(linguagens, linguagemId)}}</a>
                <a class="tag tag-evento" ng-if="showFilters('event')" ng-repeat="clasificacaoId in data.event.classificacaoEtaria" ng-click="toggleSelection(data.event.classificacaoEtaria, clasificacaoId)">{{ getName(classificacoes, clasificacaoId)}}</a>

                <a class="tag tag-agente" ng-if="showFilters('agent')" ng-repeat="areaId in data.agent.areas" ng-click="toggleSelection(data.agent.areas, areaId)">{{ getName(areas, areaId)}}</a>
                <a class="tag tag-espaco" ng-if="showFilters('space')" ng-repeat="areaId in data.space.areas" ng-click="toggleSelection(data.space.areas, areaId)">{{ getName(areas, areaId)}}</a>

                <a class="tag tag-espaco" ng-if="showFilters('space') && data.space.acessibilidade" ng-click="data.space.acessibilidade = !data.space.acessibilidade">acessibilidade</a>

                <a class="tag tag-projeto" ng-if="showFilters('project') && data.project.ropen" ng-click="data.project.ropen = !data.project.ropen">inscrições abertas</a>

                <a class="tag tag-evento" ng-if="showFilters('event') && data.event.isVerified" ng-click="toggleVerified('event')">SMC</a>
                <a class="tag tag-agente" ng-if="showFilters('agent') && data.agent.isVerified" ng-click="toggleVerified('agent')">SMC</a>
                <a class="tag tag-espaco" ng-if="showFilters('space') && data.space.isVerified" ng-click="toggleVerified('space')">SMC</a>
                <a class="tag tag-projeto" ng-if="showFilters('project') && data.project.isVerified" ng-click="toggleVerified('project')">SMC</a>

                <a class="tag tag-evento" ng-if="showFilters('event') && showEventDateFilter()" ng-click="cleanEventDateFilters()">{{eventDateFilter()}}</a>

                <a class="tag" ng-if="data.global.locationFilters.enabled === 'circle'" ng-click="cleanLocationFilters()">Área Delimitada</a>
                <a class="tag" ng-if="data.global.locationFilters.enabled === 'neighborhood'" ng-click="cleanLocationFilters()">Próximo a mim</a>
                <a class="tag" ng-if="data.global.locationFilters.enabled === 'address'" ng-click="cleanLocationFilters()">{{data.global.locationFilters.address.text}}</a>

                <a class="tag remover-tudo" ng-if="hasFilter()" ng-click="cleanAllFilters()">Remover todos filtros</a>
            </div>
            <!--#filtros-selecionados-->
            <div id="ferramentas">
                <div id="compartilhar">
                    <a class="botao-de-icone icone social_share"></a>
                    <form id="compartilhar-url">
                        <div class="setinha"></div>
                        <label for="url-da-busca">Compartilhar esse resultado: </label>
                        <input id="url-da-busca" name="url-da-busca" type="text" ng-value="location.absUrl()" />
                        <a target="_blank" ng-href="https://twitter.com/share?url={{location.absUrl()}}" class="icone social_twitter"></a>
                        <a target="_blank" ng-href="https://www.facebook.com/sharer/sharer.php?u={{location.absUrl()}}" class="icone social_facebook"></a>
                        <a target="_blank" ng-href="https://plus.google.com/share?url={{location.absUrl()}}" class="icone social_googleplus"></a>
                        <span class="info">Você também pode copiar o endereço do seu navegador</span>
                    </form>
                </div>
                <div id="views" class="clearfix" ng-if="!showFilters('project')">
                    <a class="hltip botao-de-icone icone icon_menu-square_alt"  ng-click="data.global.viewMode='list'" ng-class="{'selected':data.global.viewMode === 'list'}" title="Ver resultados em lista"></a>
                    <a class="hltip botao-de-icone icone icon_map"              ng-click="data.global.viewMode='map'"  ng-class="{'selected':data.global.viewMode === 'map'}" title="Ver resultados no mapa"></a>
                </div>
            </div>
            <!--#ferramentas-->
        </div>
        <!--#header-dos-resultados-->
    </div>
    <!--#busca-avancada-->
</div>
<!--#busca-->