<div ng-if="filter.fieldType === 'checkbox'" class="hltip" title="{{filter.placeholder}}">
    <span class="icon icon-check" ng-class="{'selected':data[entity].filters[filter.filter.param]}" ng-click="data[entity].filters[filter.filter.param] = !data[entity].filters[filter.filter.param]"></span>
    <span class="label show-label"
            ng-click="data[entity].filters[filter.filter.param] = !data[entity].filters[filter.filter.param]">{{filter.label}}</span>
</div>

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
                <li ng-repeat="option in filter.options" ng-class="{'selected':isSelected(data[entity].filters[filter.filter.param], option.value)}"
                ng-click="toggleSelection(data[entity].filters[filter.filter.param], option.value)">
                    <span>{{option.label}}</span>
                </li>
            </ul>
        </div>
    </div>
</div>

<div ng-if="filter.fieldType === 'singleselect'">
    <span class="label">{{filter.label}}</span>
    <div class="dropdown" data-closeonclick="true">
        <div class="placeholder">{{filter.placeholder}}</div>
        <div class="submenu-dropdown">
            <ul>
                <li ng-repeat="option in filter.options"
                    ng-click="data[entity].filters[filter.filter.param] = (option.value ? [option.value] : null)">
                    <span>{{option.label}}</span>
                </li>
            </ul>
        </div>
    </div>
</div>

<div ng-if="filter.fieldType === 'date'">
    <label  class="show-label">{{filter.label}}</label>
    <input  class="data"
            ng-model="data[entity].filters[filter.filter.param]"
            ui-date="dateOptions"
            ui-date-format="yy-mm-dd"
            placeholder="{{filter.placeholder}}" />
</div>

<div ng-if="filter.fieldType === 'dateFromTo'">
    <label  class="show-label">{{filter.label[0]}}</label>
    <input  class="data"
            ng-model="data[entity].filters[filter.filter.param[0]]"
            ui-date="dateOptions"
            ui-date-format="yy-mm-dd"
            placeholder="{{filter.placeholder}}" />
    <label  class="show-label" for="data-de-inicio">{{filter.label[1]}}</label>
    <input  class="data"
            ng-model="data[entity].filters[filter.filter.param[1]]"
            ui-date="dateOptions"
            ui-date-format="yy-mm-dd"
            placeholder="{{filter.placeholder}}" />
</div>