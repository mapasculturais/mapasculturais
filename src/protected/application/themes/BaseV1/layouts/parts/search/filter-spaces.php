<div id="filter-spaces" class="entity-filter clearfix" ng-show="data.global.filterEntity === 'space'">
    <header class="clearfix">
        <a href="<?php echo $app->getBaseUrl() ?>" class="icon icon-go-back"></a>
        <?php $this->dict('entities: Spaces') ?>
        <a class="icon icon-show-advanced-search" ng-click="toggleAdvancedFilters()"></a>
    </header>
    <div class="simple-filters" ng-show="showSearch()">

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

        <div class="filter verified-filter search-filter--verified">
            <a class="hltip btn btn-verified" ng-class="{'selected':data.space.isVerified}" title="Exibir somente resultados Verificados" ng-click="toggleVerified('space')"><?php $this->dict('search: verified results') ?></a>
        </div>
        <!-- div.verified-filter -->

        <div ng-repeat="entity in ['space']" class="show-advanced-filters ">
            <?php $this->part('search/advanced-filters') ?>
        </div>
        <!--.filter-->
    </div>
</div>
<!--#filter-spaces-->
