        <header id="agent-list-header" class="entity-list-header clearfix" ng-show="data.global.filterEntity == 'agent'">
            <h1><span class="icon icon-agent"></span> <?php \MapasCulturais\i::_e("Agentes");?></h1>
            <a class="btn btn-accent add" href="<?php echo $app->createUrl('agent', 'create'); ?>"><?php \MapasCulturais\i::_e("Adicionar agente");?></a>
        </header>
        
        <div id="lista-dos-agentes" class="lista agent" infinite-scroll="data.global.filterEntity === 'agent' && addMore('agent')" ng-show="data.global.filterEntity === 'agent'">
        <?php $this->part('search/list-agent-item'); ?>
        </div>
        
