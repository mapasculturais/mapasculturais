        <header id="project-list-header" class="entity-list-header clearfix" ng-show="data.global.filterEntity == 'project'">
            <div class="clearfix">
                <h1><span class="icon icon-project"></span> <?php \MapasCulturais\i::_e("Projetos");?></h1>
                <?php $this->renderModalFor('project', false, \MapasCulturais\i::__("Adicionar projeto"), "btn btn-accent add"); ?>
            </div>
        </header>
        
        <div id="lista-dos-projetos" class="lista project" infinite-scroll="data.global.filterEntity === 'project' && addMore('project')" ng-show="data.global.filterEntity === 'project'">
        <?php $this->part('search/list-project-item'); ?>
        </div>
