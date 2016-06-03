<div id="filter-spaces" class="entity-filter clearfix" ng-show="data.global.filterEntity === 'space'">
    <header class="clearfix">
        <a href="<?php echo $app->getBaseUrl() ?>" class="icon icon-go-back"></a>
        <?php $this->dict('entities: Spaces') ?>
        <a class="icon icon-show-advanced-search" ng-click="toggleAdvancedFilters()"></a>
    </header>
    <div class="simple-filters" ng-show="showSearch()">
        <form class="form-palavra-chave filter search-filter--keyword">
            <label for="palavra-chave-espaco">Palavra-chave</label>
            <input ng-model="data.space.keyword" class="search-field" type="text" name="palavra-chave-espaco" placeholder="Buscar <?php $this->dict('entities: spaces') ?>" />
        </form>
        <!--.filter-->
        <div class="filter search-filter--area">
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
        <div class="filter search-filter--tipo">
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
        <div class="filter search-filter--acessibilidade">
            <span class="icon icon-check" ng-click="data.space.acessibilidade = !data.space.acessibilidade" ng-class="{'selected':data.space.acessibilidade}"></span>
            <span class="label show-label" ng-click="data.space.acessibilidade = !data.space.acessibilidade">Acessibilidade</span>
        </div>
        <!--.filter-->
        
        <div class="filter verified-filter search-filter--verified">
            <a class="hltip btn btn-verified" ng-class="{'selected':data.space.isVerified}" title="Exibir somente resultados Verificados" ng-click="toggleVerified('space')"><?php $this->dict('search: verified results') ?></a>
        </div>
        <!-- div.verified-filter -->
        
        <div class="show-advanced-filters ">
            <span ng-if="hasAdvancedFilters('space')" ng-click="data.space.showAdvancedFilters = !data.space.showAdvancedFilters" ng-class="{selected: data.space.showAdvancedFilters}" class="icon icon-show-advanced-filters hltip" title="Exibir opções avançadas"></span>
            
            <div ng-show="data.space.showAdvancedFilters" ng-repeat="entity in ['space']" class="advanced-filters" >
                <div><strong> Filtros Avançados </strong></div>
                <!-- colocar este conteudo numa diretiva ou ng-include -->
                <div ng-repeat="filter in advancedFilters[entity]" class="advanced-filter">
                    <div ng-if="filter.fieldType === 'text'">
                        <span>{{filter.label}}</span><br>
                        <input ng-model="data[entity].advancedFilters[filter.filter.param]" placeholder="{{filter.placeholder}}"/>
                    </div>
                    <div ng-if="filter.fieldType === 'checklist'">
                        <span>{{filter.label}}</span><br>
                        <div class="dropdown">
                            <div class="placeholder">{{filter.placeholder}}</div>
                            <div class="submenu-dropdown">
                                <ul class="filter-list">
                                    <li style="white-space: nowrap" ng-repeat="option in filter.options" ng-class="{'selected':isSelected(data[entity].advancedFilters[filter.filter.param], option.value)}" ng-click="toggleSelection(data[entity].advancedFilters[filter.filter.param], option.value)">
                                        <span>{{option.label}}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--.filter-->
    </div>
</div>
<!--#filter-spaces-->
