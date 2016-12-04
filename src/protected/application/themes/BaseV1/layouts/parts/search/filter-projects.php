<div id="filter-projects" class="entity-filter clearfix" ng-show="data.global.filterEntity === 'project'">
    <header class="clearfix">
        <a href="<?php echo $app->getBaseUrl() ?>" class="icon icon-go-back"></a>
        Projetos
        <a class="icon icon-show-advanced-search" ng-click="toggleAdvancedFilters()"></a>
    </header>
    <div ng-show="showSearch()">
        <form class="form-palavra-chave filter search-filter--keyword">
            <label for="palavra-chave-evento"><?php \MapasCulturais\i::_e("Palavra-chave");?></label>
            <input ng-model="data.project.keyword" class="search-field" type="text" name="palavra-chave-evento" placeholder="<?php \MapasCulturais\i::esc_attr_e("Buscar projetos");?>" />
        </form>
        <!--.filter-->
        <div class="filter">
            <span class="label search-filter--tipo"><?php \MapasCulturais\i::_e("Tipo");?></span>
            <div class="dropdown">
                <div class="placeholder"><?php \MapasCulturais\i::_e("Selecione os tipos");?></div>
                <div class="submenu-dropdown">
                    <ul class="filter-list">
                        <li ng-repeat="type in types.project" ng-class="{'selected':isSelected(data.project.types, type.id)}" ng-click="toggleSelection(data.project.types, type.id)">
                            <span>{{type.name}}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!--.filter-->
        <div class="filter search-filter--inscricoes-abertas">
            <span class="icon icon-check" ng-class="{'selected': data.project.ropen}" ng-click="data.project.ropen = !data.project.ropen"></span>
            <span class="label show-label" ng-click="data.project.ropen = !data.project.ropen"><?php \MapasCulturais\i::_e("Inscrições Abertas");?></span>
        </div>
        <!--.filter-->
        <div class="filter verified-filter search-filter--verified">
            <a class="hltip btn btn-verified" ng-class="{'selected':data.project.isVerified}" title="<?php \MapasCulturais\i::esc_attr_e("Exibir somente resultados verificados");?>" ng-click="toggleVerified('project')"><?php $this->dict('search: verified results') ?></a>
        </div>
        <!-- div.verified-filter -->
        
        <div ng-repeat="entity in ['project']" class="show-advanced-filters ">
            <?php $this->part('search/advanced-filters') ?>
        </div>
        <!--.filter-->
    </div>
</div>
<!--#filter-projects-->
