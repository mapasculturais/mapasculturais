<div id="filter-spaces" class="entity-filter clearfix" ng-show="data.global.filterEntity === 'space'">
    <header class="clearfix">
        <a href="<?php echo $app->getBaseUrl() ?>" class="icon icon-go-back"></a>
        <?php $this->dict('entities: Spaces') ?>
        <a class="icon icon-show-advanced-search" ng-click="toggleAdvancedFilters()"></a>
    </header>
    <div class="simple-filters" ng-show="showSearch()">
        <form class="form-palavra-chave filter search-filter--keyword">
            <label for="palavra-chave-espaco"><?php \MapasCulturais\i::_e("Palavra-chave");?></label>
            <input ng-model="data.space.keyword" class="search-field" type="text" name="palavra-chave-espaco" placeholder="<?php \MapasCulturais\i::esc_attr_e("Buscar");?> <?php $this->dict('entities: spaces') ?>" />
        </form>
        <!--.filter-->
        <div class="filter search-filter--area">
            <span class="label"><?php $this->dict('taxonomies:area: name') ?></span>
            <div class="dropdown">
                <div class="placeholder"><?php $this->dict('taxonomies:area: select') ?></div>
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
            <span class="label"><?php \MapasCulturais\i::_e("Tipo");?></span>
            <div class="dropdown">
                <div class="placeholder"><?php \MapasCulturais\i::_e("Selecione os tipos");?></div>
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
            <span class="label show-label" ng-click="data.space.acessibilidade = !data.space.acessibilidade"><?php \MapasCulturais\i::_e("Acessibilidade");?></span>
        </div>
        <!--.filter-->
        
        <div class="filter verified-filter search-filter--verified">
            <a class="hltip btn btn-verified" ng-class="{'selected':data.space.isVerified}" title="<?php \MapasCulturais\i::esc_attr_e("Exibir somente resultados Verificados");?>" ng-click="toggleVerified('space')"><?php $this->dict('search: verified results') ?></a>
        </div>
        <!-- div.verified-filter -->
        
        <div ng-repeat="entity in ['space']" class="show-advanced-filters ">
            <?php $this->part('search/advanced-filters') ?>
        </div>
        <!--.filter-->
    </div>
</div>
<!--#filter-spaces-->
