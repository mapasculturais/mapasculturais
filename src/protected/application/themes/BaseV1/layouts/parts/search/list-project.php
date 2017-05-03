        <header id="project-list-header" class="entity-list-header clearfix" ng-show="data.global.filterEntity == 'project'">
            <div class="clearfix">
                <h1><span class="icon icon-project"></span> <?php \MapasCulturais\i::_e("Projetos");?></h1>
                <a class="btn btn-accent add" href="<?php echo $app->createUrl('project', 'create') ?>"><?php \MapasCulturais\i::_e("Adicionar projeto");?></a>
            </div>
        </header>
        
        <div id="lista-dos-projetos" class="lista project" infinite-scroll="data.global.filterEntity === 'project' && addMore('project')" ng-show="data.global.filterEntity === 'project'">
        <?php $this->part('search/list-project-item'); ?>
        </div>
