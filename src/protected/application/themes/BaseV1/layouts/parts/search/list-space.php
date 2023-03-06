        <header id="space-list-header" class="entity-list-header clearfix" ng-show="data.global.filterEntity == 'space'">
            <h1><span class="icon icon-space"></span> <?php $this->dict('entities: Spaces') ?></h1>
            <?php $this->renderModalFor('space', false, \MapasCulturais\i::__("Adicionar espaço"), "btn btn-accent add"); ?>
        </header>
        
        <div id="lista-dos-espacos" class="lista space" infinite-scroll="data.global.filterEntity === 'space' && addMore('space')" ng-show="data.global.filterEntity === 'space'">
        <?php $this->part('search/list-space-item'); ?>
        </div>
