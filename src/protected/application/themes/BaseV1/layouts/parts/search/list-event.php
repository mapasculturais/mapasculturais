        <header id="event-list-header" class="entity-list-header clearfix" ng-show="data.global.filterEntity == 'event'">
            <h1><span class="icon icon-event"></span> <?php \MapasCulturais\i::_e("Eventos");?></h1>
            <a class="btn btn-accent add" href="<?php echo $app->createUrl('event', 'create'); ?>"><?php \MapasCulturais\i::_e("Adicionar evento");?></a>
        </header>
        
        <div id="lista-dos-eventos" class="lista event" infinite-scroll="data.global.filterEntity === 'event' && addMore('event')" ng-show="data.global.filterEntity === 'event'">
        <?php $this->part('search/list-event-item'); ?>
        </div>
