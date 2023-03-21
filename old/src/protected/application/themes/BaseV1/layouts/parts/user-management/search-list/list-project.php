<header id="project-list-header" class="entity-list-header clearfix" ng-show="data.global.filterEntity == 'project'">
  <h1><span class="icon icon-project"></span> <?php \MapasCulturais\i::_e("Projetos");?></h1>
</header>

<div id="lista-dos-projetos" class="lista project" infinite-scroll="data.global.filterEntity === 'project' && addMore('project')" ng-show="data.global.filterEntity === 'project'">
  <?php $this->part('user-management/search-list/list-project-item'); ?>
  <span ng-show="spinnerShow" class="clearfix">
    <img src="<?php $this->asset('img/spinner.gif') ?>" />
    <span><?php \MapasCulturais\i::_e("obtendo projetos..."); ?></span>
  </span>
</div>