<span ng-if="hasAdvancedFilters(entity)" ng-click="data[entity].showAdvancedFilters = !data[entity].showAdvancedFilters" ng-class="{selected: data[entity].showAdvancedFilters}" class="icon icon-show-advanced-filters hltip" title="Exibir opções avançadas"></span>

<div ng-show="data[entity].showAdvancedFilters" class="advanced-filters" >
    <div><strong> Filtros Avançados </strong></div>
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