<header id="space-list-header" class="entity-list-header clearfix" ng-show="data.global.filterEntity == 'space'">
  <h1><span class="icon icon-space"></span> <?php $this->dict('entities: Spaces') ?></h1>  
</header>

<div id="lista-dos-espacos" class="lista space" infinite-scroll="data.global.filterEntity === 'space' && addMore('space')" ng-show="data.global.filterEntity === 'space'">
  <?php $this->part('user-management/search-list/list-space-item'); ?>
  <span ng-show="spinnerShow" class="clearfix">
    <img src="<?php $this->asset('img/spinner.gif') ?>" />
    <span><?php \MapasCulturais\i::_e("obtendo espaços..."); ?></span>
  </span>
</div>
