<div id="busca" ng-class="{'sombra':data.global.viewMode !== 'list'}">
    <div id="busca-avancada" class="clearfix">
        <div id="filtro-eventos" class="filtro-objeto clearfix" ng-show="data.global.filterEntity === 'event'">
            <form class="form-palavra-chave filtro">
                <label for="palavra-chave-evento">Palavra-chave</label>
                <input ng-model="data.event.keyword" class="campo-de-busca" type="text" name="palavra-chave-evento" placeholder="Digite um palavra-chave" />
            </form>
            <!--#busca-->
            <div class="filtro">
                <label>Intervalo entre</label>
                <input class="data" ng-model="data.event.from" ui-date="dateOptions" ui-date-format="yy-mm-dd" placeholder="00/00/0000" readonly="readonly" /> e
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
                <a class="hltip botao principal" ng-class="{'selected':data.global.isVerified}" title="Exibir somente resultados da Secretaria Municipal de Cultura" ng-click="toggleVerified()">Resultados da SMC</a>
            </div>
            <!-- div.filtro-prefeitura -->
        </div>
        <!--#filtro-eventos-->
        <div id="filtro-agentes" class="filtro-objeto clearfix" ng-show="data.global.filterEntity === 'agent'">
            <form class="form-palavra-chave filtro">
                <label>Palavra-chave</label>
                <input ng-model="data.agent.keyword" class="campo-de-busca" type="text" name="busca" placeholder="Digite um palavra-chave" />
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
                <a class="hltip botao principal" ng-class="{'selected':data.global.isVerified}" title="Exibir somente resultados da Secretaria Municipal de Cultura" ng-click="toggleVerified()">Resultados da SMC</a>
            </div>
            <!-- div.filtro-prefeitura -->
        </div>
        <!--#filtro-agentes-->
        <div id="filtro-espacos" class="filtro-objeto clearfix" ng-show="data.global.filterEntity === 'space'">
            <form class="form-palavra-chave filtro">
                <label for="palavra-chave-espaco">Palavra-chave</label>
                <input ng-model="data.space.keyword" class="campo-de-busca" type="text" name="palavra-chave-espaco" placeholder="Digite um palavra-chave" />
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
                <span id="label-da-acessibilidade" class="label">
                    Acessibilidade
                </span>
            </div>
            <!--.filtro-->
            <div class="filtro filtro-prefeitura">
                <a class="hltip botao principal" ng-class="{'selected':data.global.isVerified}" title="Exibir somente resultados da Secretaria Municipal de Cultura" ng-click="toggleVerified()">Resultados da SMC</a>
            </div>
            <!-- div.filtro-prefeitura -->
        </div>
        <!--#filtro-espacos-->
        <!--#busca-avancada-->
        <div id="header-dos-resultados" class="clearfix">
            <style>#resultados{width:auto; float:left; position:static;} #filtros-selecionados{float:left; margin-left: auto;}</style>
            <div id="resultados">
                <span ng-if="!spinnerCount">
                    <span ng-if="numAgents">{{numAgents}} agentes</span><span ng-if="numAgents && (numSpaces || numEvents)">,</span>
                    <span ng-if="numSpaces">{{numSpaces}} espaços</span><span ng-if="numSpaces && numEvents">,</span>
                    <span ng-if="numEvents">{{numEvents}} eventos</span>
                    <span ng-if="!numAgents && !numSpaces && !numEvents">Nenhum resultado encontrado</span>
                </span>

                <span ng-show="spinnerCount > 0" style="display:inline">
                    <span style="display:inline" us-spinner="{radius:2, width:2, length: 10, lines:11, top:0, left:1, speed:2}"></span>
                    <span style="margin-left:35px">obtendo resultados...</span>
                </span>
                <span ng-style="{visibility: viewLoading && 'hidden' || 'visible'}">
                    <!--<strong>00</strong> eventos, -->
                    <span ng-show="agentSearch.results.length > 0">
                        <strong>{{numberFixedLength(agentSearch.results.length, 2) || '00'}}</strong> agente<span ng-show="agentSearch.results.length > 1">s</span>
                        <small style="font-weight:normal; cursor:help;" ng-show="agentSearch.results.length > 0 && agentSearch.resultsWithoutMarker > 0">
                            <span class="hltip hltip-auto-update" title="{{agentSearch.results.length - agentSearch.resultsWithoutMarker}} agentes mostrados no mapa ({{agentSearch.resultsWithoutMarker}} sem localização)">
                                ({{agentSearch.results.length - agentSearch.resultsWithoutMarker}} <span class="icone icon_pin_alt"></span>)
                        </small>
                    </span>
                    <span ng-show="combinedSearch && agentSearch.results.length > 0 && spaceSearch.results.length > 0">,</span>
                    <span ng-show="spaceSearch.results.length > 0">
                        <strong>{{numberFixedLength(spaceSearch.results.length, 2) || '00'}}</strong> espaço<span ng-show="spaceSearch.results.length > 1">s</span>
                        <small style="font-weight:normal; cursor:help;" ng-show="spaceSearch.results.length > 0 && spaceSearch.resultsWithoutMarker > 0">
                            <span class="hltip  hltip-auto-update" title="{{spaceSearch.results.length - spaceSearch.resultsWithoutMarker}} espaços mostrados no mapa ({{spaceSearch.resultsWithoutMarker}} sem localização)">
                                ({{spaceSearch.results.length - spaceSearch.resultsWithoutMarker}} <span class="icone icon_pin_alt"></span>)
                            </span>
                        </small>
                    </span>
                    <span ng-show="agentSearch.results.length > 0 && spaceSearch.results.length > 0">
                        <strong>. Total: {{numberFixedLength(agentSearch.results.length + spaceSearch.results.length, 2) || '00'}}</strong>
                        <small style="font-weight:normal; cursor:help;" ng-show="(agentSearch.results.length > 0 && agentSearch.resultsWithoutMarker > 0) || (spaceSearch.results.length > 0 && spaceSearch.resultsWithoutMarker > 0)">
                            <span  class="hltip  hltip-auto-update" title="{{(agentSearch.results.length - agentSearch.resultsWithoutMarker) + (spaceSearch.results.length - spaceSearch.resultsWithoutMarker)}} agentes e espaços mostrados no mapa ({{agentSearch.resultsWithoutMarker + spaceSearch.resultsWithoutMarker}} sem localização)">
                                ({{(agentSearch.results.length - agentSearch.resultsWithoutMarker) + (spaceSearch.results.length - spaceSearch.resultsWithoutMarker)}}<span class="icone icon_pin_alt"></span>)
                            </span>
                        </small>
                    </span>
                    <span ng-show="combinedSearch && agentSearch.results.length == 0 && spaceSearch.results.length == 0">Nenhum resultado encontrado</span>
                    <span ng-show="!combinedSearch && agentSearch.enabled && agentSearch.results.length == 0">Nenhum resultado encontrado</span>
                    <span ng-show="!combinedSearch && spaceSearch.enabled && spaceSearch.results.length == 0">Nenhum resultado encontrado</span>
                </span>
            </div>
            <!--#resultados-->
            <div id="filtros-selecionados">
                <a class="tag tag-evento" ng-if="data.event.keyword !== ''" ng-click="data.event.keyword = ''">{{ data.event.keyword}}</a>
                <a class="tag tag-agente" ng-if="data.agent.keyword !== ''" ng-click="data.agent.keyword = ''">{{ data.agent.keyword}}</a>
                <a class="tag tag-espaco" ng-if="data.space.keyword !== ''" ng-click="data.space.keyword = ''">{{ data.space.keyword}}</a>

                <a class="tag tag-agente" ng-if="data.agent.type !== null" ng-click="data.agent.type = null">{{ getName(types.agent, data.agent.type)}}</a>
                <a class="tag tag-espaco" ng-repeat="typeId in data.space.types" ng-click="toggleSelection(data.space.types, typeId)">{{ getName(types.space, typeId)}}</a>

                <a class="tag tag-evento" ng-repeat="linguagemId in data.event.linguagens" ng-click="toggleSelection(data.event.linguagens, linguagemId)">{{ getName(linguagens, linguagemId)}}</a>

                <a class="tag tag-agente" ng-repeat="areaId in data.agent.areas" ng-click="toggleSelection(data.agent.areas, areaId)">{{ getName(areas, areaId)}}</a>
                <a class="tag tag-espaco" ng-repeat="areaId in data.space.areas" ng-click="toggleSelection(data.space.areas, areaId)">{{ getName(areas, areaId)}}</a>

                <a class="tag tag-espaco" ng-if="data.space.acessibilidade" ng-click="data.space.acessibilidade = false">Acessibilidade</a>

                <a class="tag" ng-if="data.global.isVerified" ng-click="toggleVerified()">Resultados da SMC</a>
                <a class="tag" ng-if="data.global.locationFilters.enabled === 'circle'" ng-click="cleanLocationFilters()">Área Delimitada</a>
                <a class="tag" ng-if="data.global.locationFilters.enabled === 'neighborhood'" ng-click="cleanLocationFilters()">Próximo a mim</a>
                <a class="tag" ng-if="data.global.locationFilters.enabled === 'address'" ng-click="cleanLocationFilters()">{{data.global.locationFilters.address.text}}</a>
                <a class="tag" ng-if="showEventDateFilter()" ng-click="cleanEventDateFilters()">{{eventDateFilter()}}</a>

                <a class="tag remover-tudo" ng-if="hasFilter()" ng-click="cleanAllFilters()">Remover todos filtros</a>
            </div>
            <!--#filtros-selecionados-->
            <div id="ferramentas">
                <div id="compartilhar">
                    <a class="botao-de-icone icone social_share" href="#"></a>
                    <form id="compartilhar-url"><div class="setinha"></div><label for="url-da-busca">Compartilhar esse resultado: </label><input id="url-da-busca" name="url-da-busca" type="text" ng-value="location.absUrl()" /><a href="#" class="icone social_twitter"></a><a href="#" class="icone social_facebook"></a><a href="#" class="icone social_googleplus"></a></form>
                </div>
                <div id="views" class="clearfix">
                    <a class="hltip botao-de-icone icone icon_menu-square_alt" ng-click="switchView('list')" ng-class="{'selected':data.global.viewMode === 'list'}" title="Ver resultados em lista"></a>
                    <a class="hltip botao-de-icone icone icon_map"  ng-click="switchView('map')"  ng-class="{'selected':data.global.viewMode === 'map'}" title="Ver resultados no mapa"></a>
                </div>
            </div>
            <!--#ferramentas-->
        </div>
        <!--#header-dos-resultados-->
    </div>
    <!--#busca-avancada-->
</div>
<!--#busca-->