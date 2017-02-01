        <header id="opportunity-list-header" class="entity-list-header clearfix" ng-show="data.global.filterEntity == 'opportunity'">
            <div class="clearfix">
                <h1><span class="icon icon-opportunity"></span> <?php \MapasCulturais\i::_e("Oportunidades");?></h1>
                <a class="btn btn-accent add" href="<?php echo $app->createUrl('opportunity', 'create') ?>"><?php \MapasCulturais\i::_e("Adicionar projeto");?></a>
            </div>
        </header>
        
        <div id="lista-dos-oportunidades" class="lista opportunity" infinite-scroll="data.global.filterEntity === 'opportunity' && addMore('opportunity')" ng-show="data.global.filterEntity === 'opportunity'">
        <?php $this->part('search/list-opportunity-item'); ?>
        </div>
