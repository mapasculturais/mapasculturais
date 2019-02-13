<header id="opportunity-list-header" class="entity-list-header clearfix" ng-show="data.global.filterEntity == 'opportunity'">
    <div class="clearfix">
        <h1><span class="icon icon-opportunity"></span> <?php $this->dict('entities: Opportunities') ?></h1>
    </div>
</header>

<div id="lista-dos-oportunidades" class="lista opportunity" infinite-scroll="data.global.filterEntity === 'opportunity' && addMore('opportunity')" ng-show="data.global.filterEntity === 'opportunity'">
    <?php $this->part('search/list-opportunity-item'); ?>
</div>
