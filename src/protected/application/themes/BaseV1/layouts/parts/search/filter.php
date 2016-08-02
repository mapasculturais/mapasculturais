<div id="filter-<?php echo $entity_name; ?>s" class="entity-filter clearfix" ng-show="data.global.filterEntity === '<?php echo $entity_name; ?>'">
    <header class="clearfix">
        <a href="<?php echo $app->getBaseUrl() ?>" class="icon icon-go-back"></a>
        <?php echo $display_name; ?>
        <a class="icon icon-show-advanced-search" ng-click="toggleAdvancedFilters()"></a>
    </header>
    <div ng-show="showSearch()">
        <form class="form-palavra-chave filter search-filter--keyword">
            <label for="palavra-chave-<?php echo strtolower($entity_name); ?>">Palavra-chave</label>
            <input  ng-model="data.<?php echo $entity_name; ?>.keyword"
                    class="search-field"
                    type="text"
                    name="palavra-chave-<?php echo strtolower($entity_name); ?>"
                    placeholder="Buscar <?php echo strtolower($display_name); ?>" />
        </form>
        <span ng-repeat="entity in ['<?php echo $entity_name; ?>']">
            <div ng-repeat-start="filter in filters['<?php echo $entity_name ?>']" ng-if="filter.isInline" class="filter search-filter--{{filter.filter.param}}">
                <?php $this->part('search/filter-field') ?>
            </div>
            <span ng-repeat-end></span>
        </span>
        <div ng-repeat="entity in ['<?php echo $entity_name; ?>']" class="show-advanced-filters">
            <?php $this->part('search/advanced-filters') ?>
        </div>
    </div>
</div>