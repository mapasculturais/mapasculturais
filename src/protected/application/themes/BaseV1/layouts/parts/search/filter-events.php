<div id="filter-events" class="entity-filter clearfix" ng-show="data.global.filterEntity === 'event'">
    <header class="clearfix">
        <a href="<?php echo $app->getBaseUrl() ?>" class="icon icon-go-back"></a>
        Eventos
        <a class="icon icon-show-advanced-search" ng-click="toggleAdvancedFilters()"></a>
    </header>
    <div ng-show="!collapsedFilters && !showInfobox()">
        <form class="form-palavra-chave filter searcj-filter--keyword">
            <label for="palavra-chave-evento">Palabra clave</label>
            <input ng-model="data.event.keyword" class="search-field" type="text" name="palavra-chave-evento" placeholder="Buscar eventos" />
        </form>
        <!--.filter-->
        <div class="filter search-filter--date">
            <label class="show-label" for="data-de-inicio">De</label>
            <input id="data-de-inicio" class="data" ng-model="data.event.from" ui-date="dateOptions" ui-date-format="yy-mm-dd" placeholder="00/00/0000" readonly="readonly" /> <label class="show-label">a</label>
            <input class="data" ng-model="data.event.to" ui-date="dateOptions" ui-date-format="yy-mm-dd" placeholder="00/00/0000" readonly="readonly" />
        </div>
        <!--.filter-->
        <div class="filter search-filter--linguagem">
            <label>Tipo de evento</label>
            <div class="dropdown">
                <div class="placeholder">Seleccione los tipos de eventos</div>
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
        <div class="filter search-filter--classificacao">
            <span class="label">Clasificación</span>
            <div id="classificacao" class="dropdown">
                <div class="placeholder">Seleccione la clasificación</div>
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
        <div class="filter verified-filter search-filter--verified">
            <a class="hltip btn btn-verified" ng-class="{'selected':data.event.isVerified}" title="Exhibir solamente resultados Verificados" ng-click="toggleVerified('event')"><?php $this->dict('search: verified results') ?></a>
        </div>
        <!-- div.verified-filter -->
    </div>
</div>
<!--#filter-events-->