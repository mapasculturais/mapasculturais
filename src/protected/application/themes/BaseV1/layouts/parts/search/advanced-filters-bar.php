<div ng-repeat-start="filter in filters[entity]" ng-if="filter.isInline" class="filter">
    <div ng-if="filter.fieldType === 'text'">
        <span class="label">{{filter.label}}</span>
        <input ng-model="data[entity].filters[filter.filter.param]" placeholder="{{filter.placeholder}}"/>
    </div>
    <div ng-if="filter.fieldType === 'checklist'">
        <span class="label">{{filter.label}}</span>
        <div class="dropdown">
            <div class="placeholder">{{filter.placeholder}}</div>
            <div class="submenu-dropdown">
                <ul class="filter-list">
                    <li style="whiteentity nowrap" ng-repeat="option in filter.options" ng-class="{'selected':isSelected(data[entity].filters[filter.filter.param], option.value)}"
                    ng-click="toggleSelection(data[entity].filters[filter.filter.param], option.value)">
                        <span>{{option.label}}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<span ng-repeat-end></span>