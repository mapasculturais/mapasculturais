<header id="space-list-header" class="entity-list-header clearfix" ng-show="data.global.filterEntity == 'space'">
  <h1><span class="icon icon-space"></span> <?php $this->dict('entities: Spaces') ?></h1>  
</header>

<div id="lista-dos-espacos" class="lista space" infinite-scroll="data.global.filterEntity === 'space' && addMore('space')" ng-show="data.global.filterEntity === 'space'">
  <?php $this->part('user-management/search-list/list-space-item'); ?>
</div>
