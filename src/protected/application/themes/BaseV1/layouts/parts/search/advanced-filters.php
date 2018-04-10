<span ng-if="hasAdvancedFilters(entity)" ng-click="data[entity].showAdvancedFilters = !data[entity].showAdvancedFilters" ng-class="{selected: data[entity].showAdvancedFilters}" class="icon icon-show-advanced-filters hltip" title="<?php \MapasCulturais\i::esc_attr_e("Exibir opções avançadas");?>"></span>

<div ng-show="data[entity].showAdvancedFilters" class="advanced-filters" >
    <div><strong> <?php \MapasCulturais\i::_e("Filtros Avançados");?> </strong></div>
    <div ng-repeat="filter in filters[entity]">
        <div ng-if="!filter.isInline" class="advanced-filter">
            <?php $this->part('search/filter-field') ?>
        </div>
    </div>
</div>