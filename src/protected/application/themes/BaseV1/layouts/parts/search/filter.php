<div id="filter-<?php echo $entity_name; ?>s" class="entity-filter clearfix" ng-show="data.global.filterEntity === '<?php echo $entity_name; ?>'">
    <header class="clearfix">
        <a href="<?php echo $app->getBaseUrl() ?>" class="icon icon-go-back"></a>
        <?php echo $display_name; ?>
        <a class="icon icon-show-advanced-search" ng-click="toggleAdvancedFilters()"></a>
    </header>
    <div ng-show="showSearch()">
        <form class="form-palavra-chave filter search-filter--keyword">
            <label for="palavra-chave-<?php echo strtolower($entity_name); ?>"><?php \MapasCulturais\i::_e("Palavra-chave");?></label>
            <input  ng-model="data.<?php echo $entity_name; ?>.keyword"
                    class="search-field"
                    type="text"
                    name="palavra-chave-<?php echo strtolower($entity_name); ?>"
                    placeholder="<?php \MapasCulturais\i::esc_attr_e("Buscar");?> <?php echo strtolower($display_name); ?>" />
        </form>
        <?php if($entity_name === 'event'): ?>
            <div class="filter search-filter--date">
                <label class="show-label" for="data-de-inicio">De</label>
                <input id="data-de-inicio" class="data" ng-model="data.event.from" ui-date="dateOptions" ui-date-format="yy-mm-dd" placeholder="00/00/0000" /> <label class="show-label"><?php \MapasCulturais\i::_e("a");?></label>
                <input class="data" ng-model="data.event.to" ui-date="dateOptions" ui-date-format="yy-mm-dd" placeholder="00/00/0000" />
            </div>
        <?php endif; ?>
        <span ng-repeat="entity in ['<?php echo $entity_name; ?>']">
            <div ng-repeat-start="filter in filters['<?php echo $entity_name ?>']" ng-if="filter.isInline" class="filter search-filter--{{filter.filter.param.toLowerCase()}} {{filter.addClass}}">
                <?php $this->part('search/filter-field') ?>
            </div>
            <span ng-repeat-end></span>
        </span>

        <div ng-repeat="entity in ['<?php echo $entity_name; ?>']" class="show-advanced-filters">
            <?php $this->part('search/advanced-filters') ?>
        </div>
    </div>
</div>