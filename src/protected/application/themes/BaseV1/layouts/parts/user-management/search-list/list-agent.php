<header id="agent-list-header" class="entity-list-header clearfix" ng-show="data.global.filterEntity == 'agent'">
  <h1><span class="icon icon-agent"></span> <?php \MapasCulturais\i::_e("Agentes");?></h1>
</header>

<div id="lista-dos-agentes" class="lista agent" infinite-scroll="data.global.filterEntity === 'agent' && addMore('agent')" ng-show="data.global.filterEntity === 'agent'">
  <?php $this->part('user-management/search-list/list-agent-item'); ?>
  <span ng-show="spinnerShow" class="clearfix">
    <img src="<?php $this->asset('img/spinner.gif') ?>" />
    <span><?php \MapasCulturais\i::_e("obtendo agentes..."); ?></span>
  </span>
</div>