        <header id="space-list-header" class="entity-list-header clearfix" ng-show="data.global.filterEntity == 'space'">
            <h1><span class="icon icon-space"></span> <?php $this->dict('entities: Spaces') ?></h1>
            <a class="btn btn-accent add" href="<?php echo $app->createUrl('space', 'create'); ?>"><?php \MapasCulturais\i::_e("Adicionar");?> <?php $this->dict('entities: space') ?></a>
        </header>
        
        <div id="lista-dos-espacos" class="lista space" infinite-scroll="data.global.filterEntity === 'space' && addMore('space')" ng-show="data.global.filterEntity === 'space'">
        <?php $this->part('search/list-space-item'); ?>
        </div>
