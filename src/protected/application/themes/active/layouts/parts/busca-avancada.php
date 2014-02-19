<div id="busca">
<div id="busca-avancada" class="clearfix">
    <div id="filtro-eventos" class="filtro-objeto clearfix" ng-show="entity=='event'">
		<form class="form-palavra-chave filtro">
			<label for="palavra-chave-evento">Palavra-chave</label>
			<input ng-keyup="searchTermKeyUp($event)" data-entity='event' class="campo-de-busca" type="text" name="palavra-chave-evento" placeholder="Digite um palavra-chave" />
		</form>
		<!--#busca-->
		<div class="filtro">
			<label>Intervalo entre</label>
			<a class="tag selected data">00/00/00</a>
			e
			<a class="tag data">00/00/00</a>
			<div id="busca-agenda-dia" class="hasDatepicker">
				<div class="ui-datepicker-inline ui-datepicker ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
					<div class="ui-datepicker-header ui-widget-header ui-helper-clearfix ui-corner-all">
						<a class="ui-datepicker-prev ui-corner-all" data-handler="prev" data-event="click" title="Anterior"><span class="icone arrow_carrot-left ui-icon ui-icon-circle-triangle-w"></span></a>
						<a class="ui-datepicker-next ui-corner-all" data-handler="next" data-event="click" title="Próximo"><span class="icone arrow_carrot-right ui-icon ui-icon-circle-triangle-e"></span></a>
						<div class="ui-datepicker-title"><span class="ui-datepicker-month">Setembro</span>&nbsp;<span class="ui-datepicker-year">2013</span></div>
					</div>
					<table class="ui-datepicker-calendar">
						<thead>
						<tr>
							<th class="ui-datepicker-week-end"><span title="Domingo">D</span>
							</th><th><span title="Segunda">S</span></th>
							<th><span title="Terça">T</span></th>
							<th><span title="Quarta">Q</span></th>
							<th><span title="Quinta">Q</span></th>
							<th><span title="Sexta">S</span></th>
							<th class="ui-datepicker-week-end"><span title="Sábado">S</span></th>
						</tr>
						</thead>
						<tbody>
						<tr>
							<td class=" ui-datepicker-week-end " data-handler="selectDay" data-event="click" data-month="8" data-year="2013"><a class="ui-state-default" href="#">1</a></td>
							<td class=" ui-datepicker-days-cell-over  ui-datepicker-current-day ui-datepicker-today" data-handler="selectDay" data-event="click" data-month="8" data-year="2013"><a class="ui-state-default ui-state-highlight ui-state-active" href="#">2</a></td>
							<td class=" " data-handler="selectDay" data-event="click" data-month="8" data-year="2013"><a class="ui-state-default" href="#">3</a></td>
							<td class=" " data-handler="selectDay" data-event="click" data-month="8" data-year="2013"><a class="ui-state-default" href="#">4</a></td>
							<td class=" " data-handler="selectDay" data-event="click" data-month="8" data-year="2013"><a class="ui-state-default" href="#">5</a></td>
							<td class=" " data-handler="selectDay" data-event="click" data-month="8" data-year="2013"><a class="ui-state-default" href="#">6</a></td>
							<td class=" ui-datepicker-week-end " data-handler="selectDay" data-event="click" data-month="8" data-year="2013"><a class="ui-state-default" href="#">7</a></td>
						</tr>
						<tr>
							<td class=" ui-datepicker-week-end " data-handler="selectDay" data-event="click" data-month="8" data-year="2013"><a class="ui-state-default" href="#">8</a></td>
							<td class=" " data-handler="selectDay" data-event="click" data-month="8" data-year="2013"><a class="ui-state-default" href="#">9</a></td>
							<td class=" " data-handler="selectDay" data-event="click" data-month="8" data-year="2013"><a class="ui-state-default" href="#">10</a></td>
							<td class=" " data-handler="selectDay" data-event="click" data-month="8" data-year="2013"><a class="ui-state-default" href="#">11</a></td>
							<td class=" " data-handler="selectDay" data-event="click" data-month="8" data-year="2013"><a class="ui-state-default" href="#">12</a></td>
							<td class=" " data-handler="selectDay" data-event="click" data-month="8" data-year="2013"><a class="ui-state-default" href="#">13</a></td>
							<td class=" ui-datepicker-week-end " data-handler="selectDay" data-event="click" data-month="8" data-year="2013"><a class="ui-state-default" href="#">14</a></td>
						</tr>
						<tr>
							<td class=" ui-datepicker-week-end " data-handler="selectDay" data-event="click" data-month="8" data-year="2013"><a class="ui-state-default" href="#">15</a></td>
							<td class=" " data-handler="selectDay" data-event="click" data-month="8" data-year="2013"><a class="ui-state-default" href="#">16</a></td>
							<td class=" " data-handler="selectDay" data-event="click" data-month="8" data-year="2013"><a class="ui-state-default" href="#">17</a></td>
							<td class=" " data-handler="selectDay" data-event="click" data-month="8" data-year="2013"><a class="ui-state-default" href="#">18</a></td>
							<td class=" " data-handler="selectDay" data-event="click" data-month="8" data-year="2013"><a class="ui-state-default" href="#">19</a></td>
							<td class=" " data-handler="selectDay" data-event="click" data-month="8" data-year="2013"><a class="ui-state-default" href="#">20</a></td>
							<td class=" ui-datepicker-week-end " data-handler="selectDay" data-event="click" data-month="8" data-year="2013"><a class="ui-state-default" href="#">21</a></td>
						</tr>
						<tr>
							<td class=" ui-datepicker-week-end " data-handler="selectDay" data-event="click" data-month="8" data-year="2013"><a class="ui-state-default" href="#">22</a></td>
							<td class=" " data-handler="selectDay" data-event="click" data-month="8" data-year="2013"><a class="ui-state-default" href="#">23</a></td>
							<td class=" " data-handler="selectDay" data-event="click" data-month="8" data-year="2013"><a class="ui-state-default" href="#">24</a></td>
							<td class=" " data-handler="selectDay" data-event="click" data-month="8" data-year="2013"><a class="ui-state-default" href="#">25</a></td>
							<td class=" " data-handler="selectDay" data-event="click" data-month="8" data-year="2013"><a class="ui-state-default" href="#">26</a></td>
							<td class=" " data-handler="selectDay" data-event="click" data-month="8" data-year="2013"><a class="ui-state-default" href="#">27</a></td>
							<td class=" ui-datepicker-week-end " data-handler="selectDay" data-event="click" data-month="8" data-year="2013"><a class="ui-state-default" href="#">28</a></td>
						</tr>
						<tr>
							<td class=" ui-datepicker-week-end " data-handler="selectDay" data-event="click" data-month="8" data-year="2013"><a class="ui-state-default" href="#">29</a></td>
							<td class=" " data-handler="selectDay" data-event="click" data-month="8" data-year="2013"><a class="ui-state-default" href="#">30</a></td>
							<td class=" ui-datepicker-other-month ui-datepicker-unselectable ui-state-disabled "><span class="ui-state-default">1</span></td>
							<td class=" ui-datepicker-other-month ui-datepicker-unselectable ui-state-disabled "><span class="ui-state-default">2</span></td>
							<td class=" ui-datepicker-other-month ui-datepicker-unselectable ui-state-disabled "><span class="ui-state-default">3</span></td>
							<td class=" ui-datepicker-other-month ui-datepicker-unselectable ui-state-disabled "><span class="ui-state-default">4</span></td>
							<td class=" ui-datepicker-week-end ui-datepicker-other-month ui-datepicker-unselectable ui-state-disabled "><span class="ui-state-default">5</span></td>
						</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<!--.filtro-->
		<div class="filtro">
			<label>Linguagem</label>
            <div class="dropdown">
                <div class="placeholder">Selecione as linguagens</div>
                <div class="submenu-dropdown">
                    <ul class="lista-de-filtro select">
                        <li>artes circenses</li>
                        <li>cultura digital</li>
                        <li class="selected">música</li>
                        <li>artes integradas</li>
                        <li>cultura tradicional</li>
                        <li>rádio</li>
                        <li>artes visuais</li>
                        <li>dança</li>
                        <li>teatro</li>
                        <li>audiovisual</li>
                        <li>hip hop</li>
                        <li>cultura indígena</li>
                        <li>livre e literatura</li>
                        <li>outros</li>
                    </ul>
                </div>
            </div>
		</div>
		<!--.filtro-->
		<div class="filtro">
			<span class="label">Classificação</span>
            <div id="classificacao" class="dropdown">
                <div class="placeholder">Livre</div>
                <div class="submenu-dropdown">
                    <ul>
                        <li>18 anos</li>
                        <li>16 anos</li>
                        <li>14 anos</li>
                        <li>12 anos</li>
                        <li>10 anos</li>
                        <li>Livre</li>
                    </ul>
                </div>
            </div>
		</div>
		<!--.filtro-->
    </div>
    <!--#filtro-eventos-->
    <div id="filtro-agentes" class="filtro-objeto clearfix" ng-show="agentSearch.showFilters">
		<form class="form-palavra-chave filtro">
			<label>Palavra-chave</label>
			<input ng-model="agentSearch.searchInput" data-entity="agent" ng-keyup="searchTermKeyUp($event)" class="campo-de-busca" type="text" name="busca" placeholder="Digite um palavra-chave" />
		</form>
		<!--#busca-->
		<div class="filtro">
			<span class="label">Área de Atuação</span>
            <div class="dropdown">
                <div class="placeholder">Selecione as áreas</div>
                <div class="submenu-dropdown">
                    <ul class="lista-de-filtro">
                        <li ng-repeat="area in agentSearch.areas" ng-class="{'selected':area.selected}" ng-click="area.selected=!area.selected;searchManager.update()">
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
                <div class="placeholder">{{(agentSearch.types[agentSearch.types.selected]) && (agentSearch.types[agentSearch.types.selected].name)||'Todos'}}&nbsp;</div>
                <div class="submenu-dropdown">
                    <ul>
                    	<li ng-click="selectAgentType();">
                            <span>Todos</span>
                        </li>
                        <li ng-repeat="type in agentSearch.types" ng-click="selectAgentType($index);">
                            <span>{{type.name}}</span>
                        </li>
                    </ul>
                </div>
            </div>
		</div>
		<!--.filtro-->
    </div>
    <!--#filtro-agentes-->
    <div id="filtro-espacos" class="filtro-objeto clearfix" ng-show="spaceSearch.showFilters">
		<form class="form-palavra-chave filtro">
			<label for="palavra-chave-espaco">Palavra-chave</label>
			<input ng-model="spaceSearch.searchInput" data-entity="space"  ng-keyup="searchTermKeyUp($event)" class="campo-de-busca" type="text" name="palavra-chave-espaco" placeholder="Digite um palavra-chave" />
		</form>
		<!--#busca-->
		<div class="filtro">
			<span class="label">Área de Atuação</span>
			<div class="dropdown">
                <div class="placeholder">Selecione as áreas</div>
                <div class="submenu-dropdown">
                    <ul class="lista-de-filtro">
                        <li ng-repeat="area in spaceSearch.areas" ng-class="{'selected':area.selected}" ng-click="area.selected=!area.selected;searchManager.update()">
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
                        <li ng-repeat="type in spaceSearch.types" ng-class="{'selected':type.selected}" ng-click="type.selected=!type.selected;searchManager.update()">
                            <span>{{type.name}}</span>
                        </li>
                    </ul>
				</div>
			</div>
		</div>
		<!--.filtro-->
		<div class="filtro">
			<span class="icone icon_check" ng-click="spaceAccessibility=!spaceAccessibility;searchManager.update();" ng-class="{'selected':spaceAccessibility}"></span>
			<span id="label-da-acessibilidade" class="label" ng-click="spaceAccessibility=!spaceAccessibility;searchManager.update();" style="cursor:default">
				Acessibilidade
			</span>
		</div>
		<!--.filtro-->
    </div>
    <!--#filtro-espacos-->
    <div class="wrap clearfix">
        <div id="filtro-local" class="filtro-geral clearfix" ng-controller="SearchSpatialController">
			<form id="form-local" method="post" action="#">
				<label for="proximo-a">Local: </label>
				<input id="endereco" type="text" class="proximo-a" name="proximo-a" placeholder="Digite um endereço" />
				<!--<p class="mensagem-erro-proximo-a-mim mensagens">Não foi possível determinar sua localização. Digite seu endereço, bairro ou CEP </p>-->
				<input type="hidden" name="lat" />
				<input type="hidden" name="lng" />
			</form>
			  ou
			<a class="hltip proximo-a-mim botao principal" href="#" ng-click="filterNeighborhood()" title="Buscar somente resultados próximos a mim.">Próximo a mim</a>
              ou
            <a class="hltip botao principal" href="#" ng-click="drawCircle()" title="Buscar somente resultados em uma área delimitada">Delimitar uma área</a>
		</div>
		<!--#filtro-local-->
        <!--
		<form id="form-projeto" class="filtro-geral">
			<label for="nome-do-projeto">Projeto: </label>
			<input class="autocomplete" name="nome-do-projeto" type="text" placeholder="Selecione um projeto" />
			<a class="hltip botao principal" href="#" title="Clique para ver a lista de projetos">Ver projetos</a>
		</form>-->
		<!-- #form-projeto-->
		<div id="filtro-prefeitura" class="filtro-geral">
            <a class="hltip botao principal selected" href="#" title="Exibir somente resultados da Secretaria Municipal de Cultura" ng-click="filterVerified=!filterVerified; searchManager.update();">Resultados da SMC</a>
		</div>
		<!-- #filtro-prefeitura-->
        <div id="busca-combinada" class="filtro-geral">
            <span class="icone icon_check"  ng-click="toggleCombined()" ng-class="{'selected':combinedSearch}" ></span>
            <span class="label hltip"  		ng-click="toggleCombined()" style="cursor:default" comentario="Cátia, mudei o cursor para não ficar cursor:text já que registrei o evento de clique também aqui no .label do checkbox"
            	title="Nesse modo é possível combinar agentes e espaços no mesmo resultado de busca">
            	Busca Combinada
            </span>
            <!---<input ng-model="combinedSearch" type="checkbox"/> <label style="display: inline-block;">Busca Combinada (Bó, o html correto tá comentado)</label>-->
        </div>
	</div>
	<!--.wrap-->
</div>
<!--#busca-avancada-->
<div id="header-dos-resultados" class="clearfix">
	<style>#resultados{width:auto; float:left; position:static;} #filtros-selecionados{float:left; margin-left: auto;}</style>
	<div id="resultados">
		<span ng-show="viewLoading" style="display:inline">
			<span style="display:inline" us-spinner="{radius:2, width:2, length: 10, lines:11, top:0, left:1, speed:2}"></span>
			<span style="margin-left:35px">obtendo resultados...</span>
		</span>
		<span ng-style="{visibility: viewLoading && 'hidden' || 'visible'}">
			<!--<strong>00</strong> eventos, -->
			<span ng-show="agentSearch.results.length>0">
                <strong>{{numberFixedLength(agentSearch.results.length,2)||'00'}}</strong> agente<span ng-show="agentSearch.results.length>1">s</span>
				<small style="font-weight:normal; cursor:help;" ng-show="agentSearch.results.length>0 && agentSearch.resultsWithoutMarker>0">
					<span class="hltip hltip-auto-update" title="{{agentSearch.results.length-agentSearch.resultsWithoutMarker}} agentes mostrados no mapa ({{agentSearch.resultsWithoutMarker}} sem localização)">
					({{agentSearch.results.length-agentSearch.resultsWithoutMarker}} <span class="icone icon_pin_alt"></span>)
				</small>
			</span>
			<span ng-show="combinedSearch && agentSearch.results.length>0 && spaceSearch.results.length>0">,</span>
			<span ng-show="spaceSearch.results.length>0">
				<strong>{{numberFixedLength(spaceSearch.results.length,2)||'00'}}</strong> espaço<span ng-show="spaceSearch.results.length>1">s</span>
				<small style="font-weight:normal; cursor:help;" ng-show="spaceSearch.results.length>0 && spaceSearch.resultsWithoutMarker>0">
					<span class="hltip  hltip-auto-update" title="{{spaceSearch.results.length-spaceSearch.resultsWithoutMarker}} espaços mostrados no mapa ({{spaceSearch.resultsWithoutMarker}} sem localização)">
						({{spaceSearch.results.length-spaceSearch.resultsWithoutMarker}} <span class="icone icon_pin_alt"></span>)
					</span>		
				</small>
			</span>
			<span ng-show="agentSearch.results.length>0&&spaceSearch.results.length>0">
				<strong>. Total: {{numberFixedLength(agentSearch.results.length+spaceSearch.results.length,2)||'00'}}</strong>
				<small style="font-weight:normal; cursor:help;" ng-show="(agentSearch.results.length>0 && agentSearch.resultsWithoutMarker>0) || (spaceSearch.results.length>0 && spaceSearch.resultsWithoutMarker>0)">
					<span  class="hltip  hltip-auto-update" title="{{(agentSearch.results.length-agentSearch.resultsWithoutMarker)+(spaceSearch.results.length-spaceSearch.resultsWithoutMarker)}} agentes e espaços mostrados no mapa ({{agentSearch.resultsWithoutMarker+spaceSearch.resultsWithoutMarker}} sem localização)">
					  ({{(agentSearch.results.length-agentSearch.resultsWithoutMarker)+(spaceSearch.results.length-spaceSearch.resultsWithoutMarker)}}<span class="icone icon_pin_alt"></span>)
					</span>
				</small>
			</span>
            <span ng-show="combinedSearch && agentSearch.results.length==0 && spaceSearch.results.length==0">Nenhum resultado encontrado</span>
            <span ng-show="!combinedSearch && agentSearch.enabled && agentSearch.results.length==0">Nenhum resultado encontrado</span>
            <span ng-show="!combinedSearch && spaceSearch.enabled && spaceSearch.results.length==0">Nenhum resultado encontrado</span>
		</span>
	</div>
    <!--#resultados-->
	<div id="filtros-selecionados">
        <a class="tag tag-agente" href="#" ng-bind="agentSearch.searchTerm" ng-show="agentSearch.searchTerm" ng-click="agentResults=[];agentSearch.searchTerm='';agentSearch.searchInput='';searchManager.update();"></a>
        <a class="tag tag-espaco" href="#" ng-bind="spaceSearch.searchTerm" ng-show="spaceSearch.searchTerm" ng-click="spaceResults=[];spaceSearch.searchTerm='';spaceSearch.searchInput='';searchManager.update();"></a>
		<a class="tag tag-agente" href="#" ng-repeat="type in agentSearch.types | filter:isSelected" ng-click="type.selected=false; searchManager.update();">{{type.name}}</a>
        <a class="tag tag-espaco" href="#" ng-repeat="type in spaceSearch.types | filter:isSelected" ng-click="type.selected=false; searchManager.update();">{{type.name}}</a>
        <a class="tag tag-agente" href="#" ng-repeat="area in agentSearch.areas | filter:isSelected" ng-click="area.selected=false; searchManager.update();">{{area.name}}</a>
		<a class="tag tag-espaco" href="#" ng-repeat="area in spaceSearch.areas | filter:isSelected" ng-click="area.selected=false; searchManager.update();">{{area.name}}</a>
		<a class="tag tag-espaco" href="#" ng-show="spaceAccessibility" ng-click="spaceAccessibility=false;searchManager.update()">Acessibilidade</a>
		<a class="tag" href="#" ng-show="filterVerified" ng-click="filterVerified=false;searchManager.update()">Resultados da SMC</a>
        <a class="tag" href="#" ng-show="searchManager.filterLocation" ng-click="searchManager.filterLocation=false;searchManager.update()">Área Delimitada</a>
		<a class="tag remover-tudo" href="#" ng-click="cleanAllFilters()" ng-show="agentSearch.hasFilters()|| spaceSearch.hasFilters()||filterVerified||searchManager.filterLocation">Remover todos filtros</a>
	</div>
    <!--#filtros-selecionados-->
	<div id="ferramentas">
		<div id="compartilhar">
			<a class="botao-de-icone icone social_share" href="#"></a>
			<form id="compartilhar-url"><div class="setinha"></div><label for="url-da-busca">Compartilhar esse resultado: </label><input id="url-da-busca" name="url-da-busca" type="text" value="http://lorem.ipsum.mussum/#filtro+filtro+filtro" /><a href="#" class="icone social_twitter"></a><a href="#" class="icone social_facebook"></a><a href="#" class="icone social_googleplus"></a></form>
		</div>
		<div id="views" class="clearfix">
			<a class="hltip botao-de-icone icone icon_menu-square_alt js-open-view" data-target="div#lista" href="#" title="Ver resultados em lista"></a>
			<a class="hltip botao-de-icone icone icon_map js-open-view selected" data-target="div#mapa" href="#" title="Ver resultados no mapa"></a>
		</div>
    </div>
    <!--#ferramentas-->
</div>
<!--#header-dos-resultados-->
</div>
<!--#busca-->
