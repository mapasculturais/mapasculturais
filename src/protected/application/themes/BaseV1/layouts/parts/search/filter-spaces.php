<div id="filter-spaces" class="entity-filter clearfix" ng-show="data.global.filterEntity === 'space'">
    <header class="clearfix">
        <a href="<?php echo $app->getBaseUrl() ?>" class="icon icon-go-back"></a>
        <?php $this->dict('entities: Spaces') ?>
        <a class="icon icon-show-advanced-search" ng-click="toggleAdvancedFilters()"></a>
    </header>
    <div ng-show="showSearch()">
        <form class="form-palavra-chave filter search-filter--keyword">
            <label for="palavra-chave-espaco">Palabra clave</label>
            <input ng-model="data.space.keyword" class="search-field" type="text" name="palavra-chave-espaco" placeholder="Buscar <?php $this->dict('entities: spaces') ?>" />
        </form>
        <!--.filter-->
        <div class="filter search-filter--area">
            <span class="label">Área de actuación</span>
            <div class="dropdown">
                <div class="placeholder">Seleccione las áreas</div>
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
        <div class="filter search-filter--tipo">
            <span class="label">Tipo</span>
            <div class="dropdown">
                <div class="placeholder">Seleccione los tipos</div>
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
        <div class="filter search-filter--acessibilidade">
            <span class="icon icon-check" ng-click="data.space.acessibilidade = !data.space.acessibilidade" ng-class="{'selected':data.space.acessibilidade}"></span>
            <span class="label show-label" ng-click="data.space.acessibilidade = !data.space.acessibilidade">Accesibilidad</span>
        </div>
        <!--.filter-->
        <div class="filter verified-filter search-filter--verified">
            <a class="hltip btn btn-verified" ng-class="{'selected':data.space.isVerified}" title="Exhibir solamente resultados Verificados" ng-click="toggleVerified('space')"><?php $this->dict('search: verified results') ?></a>
        </div>
        <!-- div.verified-filter -->
    </div>
</div>
<!--#filter-spaces-->