<div id="filter-agents" class="entity-filter clearfix" ng-show="data.global.filterEntity === 'agent'">
    <header class="clearfix">
        <a href="<?php echo $app->getBaseUrl() ?>" class="icon icon-go-back"></a>
        <?php \MapasCulturais\i::_e("Agentes");?>
        <a class="icon icon-show-advanced-search" ng-click="toggleAdvancedFilters()"></a>
    </header>
    <div ng-show="showSearch()">
        <form class="form-palavra-chave filter search-filter--keyword">
            <label><?php \MapasCulturais\i::_e("Palavra-chave");?></label>
            <input ng-model="data.agent.keyword" class="search-field" type="text" name="busca" placeholder="<?php \MapasCulturais\i::esc_attr_e("Buscar agentes");?>" />
        </form>
        <!--.filter-->
        <div class="filter search-filter--area">
            <span class="label"><?php $this->dict('taxonomies:area: name') ?></span>
            <div class="dropdown">
                <div class="placeholder"><?php $this->dict('taxonomies:area: select') ?></div>
                <div class="submenu-dropdown">
                    <ul class="filter-list">
                        <li ng-repeat="area in areas" ng-class="{'selected':isSelected(data.agent.areas, area.id)}" ng-click="toggleSelection(data.agent.areas, area.id)">
                            <span>{{area.name}}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!--.filter-->
        <div class="filter search-filter--tipo">
            <span class="label">Tipo</span>
            <div id="tipo-de-agente" class="dropdown" data-closeonclick="true">
                <div class="placeholder">{{getName(types.agent, data.agent.type)}}&nbsp;</div>
                <div class="submenu-dropdown">
                    <ul>
                        <li ng-repeat="type in types.agent" ng-click="data.agent.type = type.id">
                            <span>{{type.name}}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!--.filter-->
        <div class="filter verified-filter search-filter--acessibilidade">
            <a class="hltip btn btn-verified" ng-class="{'selected':data.agent.isVerified}" title="Exibir somente resultados Verificados" ng-click="toggleVerified('agent')"><?php $this->dict('search: verified results') ?></a>
        </div>
        <!-- div.verified-filter -->

        <div ng-repeat="entity in ['agent']" class="show-advanced-filters ">
            <?php $this->part('search/advanced-filters') ?>
        </div>
        <!--.filter-->
    </div>
</div>
<!--#filter-agents-->