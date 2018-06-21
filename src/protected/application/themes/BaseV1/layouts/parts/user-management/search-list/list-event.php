<header id="event-list-header" class="entity-list-header clearfix" ng-show="data.global.filterEntity == 'event'">
  <h1><span class="icon icon-event"></span> <?php \MapasCulturais\i::_e("Eventos");?></h1>
</header>

<div id="lista-dos-eventos" class="lista event" infinite-scroll="data.global.filterEntity === 'event' && addMore('event')" ng-show="data.global.filterEntity === 'event'">
  <?php $this->part('user-management/search-list/list-event-item'); ?>
  <span ng-show="spinnerShow" class="clearfix">
    <img src="<?php $this->asset('img/spinner.gif') ?>" />
    <span><?php \MapasCulturais\i::_e("obtendo eventos..."); ?></span>
  </span>
</div>