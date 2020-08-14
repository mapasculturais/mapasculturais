<?php
$this->layout = 'search';

$this->bodyProperties['ng-app'] = "search.app";
$this->bodyProperties['ng-controller'] = "SearchController";
$this->bodyProperties['ng-class'] = "{'infobox-open': showInfobox()}";

$this->addTaxonoyTermsToJs('area');
$this->addTaxonoyTermsToJs('linguagem');

$this->addEntityTypesToJs('MapasCulturais\Entities\Space');
$this->addEntityTypesToJs('MapasCulturais\Entities\Agent');
$this->addEntityTypesToJs('MapasCulturais\Entities\Project');
// $this->addEntityTypesToJs('MapasCulturais\Entities\Opportunity');
$this->addEntityTypesToJs('MapasCulturais\Entities\Seal');

$this->includeSearchAssets();

$def = $app->getRegisteredMetadataByMetakey('classificacaoEtaria', 'MapasCulturais\Entities\Event');
$this->jsObject['classificacoesEtarias'] = array_values($def->config['options']);

$this->includeMapAssets();

?>
    <div id="filtro-local" class="clearfix js-leaflet-control" data-leaflet-target=".leaflet-top.leaflet-left" ng-controller="SearchSpatialController" ng-show="data.global.viewMode ==='map'">
        <form id="form-local" method="post">
            <label for="proximo-a"><?php \MapasCulturais\i::_e("Local");?>: </label>
            <input id="endereco" ng-model="data.global.locationFilters.address.text" type="text" class="proximo-a" name="proximo-a" placeholder="<?php \MapasCulturais\i::esc_attr_e("Digite um endereço");?>" />
            <input type="hidden" name="lat" />
            <input type="hidden" name="lng" />
        </form>
        <a id="near-me" class="control-infobox-open hltip btn-map" ng-click="filterNeighborhood()" title="<?php \MapasCulturais\i::esc_attr_e("Buscar somente resultados próximos a mim.");?>"></a>
        <!--<a class="btn btn-primary hltip" href="#" ng-click="drawCircle()" title="Buscar somente resultados em uma área delimitada" rel='noopener noreferrer'>delimitar área</a>-->
    </div>
    <!--#filtro-local-->
    <div id="mc-entity-layers" class="js-leaflet-control" data-leaflet-target=".leaflet-bottom.leaflet-right" ng-show="data.global.viewMode ==='map'">
        <div class="label"><?php \MapasCulturais\i::_e("Mostrar");?>:</div>
        <div>
            <?php if($app->isEnabled('events')): ?>
                <a class="hltip hltip-auto-update btn-map btn-map-event" ng-class="{active: data.global.enabled.event}" ng-click="data.global.enabled.event = !data.global.enabled.event" title="{{(data.global.enabled.event) && 'Ocultar' || 'Mostrar'}} <?php \MapasCulturais\i::_e("eventos");?>"></a>
            <?php endif; ?>

            <?php if($app->isEnabled('spaces')): ?>
                <a class="hltip hltip-auto-update btn-map btn-map-space" ng-class="{active: data.global.enabled.space}" ng-click="data.global.enabled.space = !data.global.enabled.space" title="{{(data.global.enabled.space) && 'Ocultar' || 'Mostrar'}} <?php $this->dict('entities: spaces') ?>"></a>
            <?php endif; ?>

            <?php if($app->isEnabled('agents')): ?>
                <a class="hltip hltip-auto-update btn-map btn-map-agent"  ng-class="{active: data.global.enabled.agent}" ng-click="data.global.enabled.agent = !data.global.enabled.agent" title="{{(data.global.enabled.agent) && 'Ocultar' || 'Mostrar'}} <?php \MapasCulturais\i::_e("agentes");?>"></a>
            <?php endif; ?>


            <?php if($app->isEnabled('seals') && $app->user->is('admin')): ?>
                <a class="hltip hltip-auto-update btn-map btn-map-seal"  ng-class="{active: data.global.enabled.seal}" ng-click="data.global.enabled.seal = !data.global.enabled.seal" title="{{(data.global.enabled.seal) && 'Ocultar' || 'Mostrar'}} <?php \MapasCulturais\i::_e("selos");?>"></a>
            <?php endif; ?>

        </div>
    </div>

    <div id="infobox" ng-show="showInfobox()" class="{{data.global.openEntity.type}}">
        <a class="icon icon-close" ng-click="data.global.openEntity.id=null" rel='noopener noreferrer'></a>
        
        <?php $this->part('search/infobox-agent'); ?>
        <?php $this->part('search/infobox-space'); ?>
        <?php $this->part('search/infobox-event'); ?>
        
    </div><!--#infobox-->

    <div id="search-map-container" ng-controller="SearchMapController" ng-show="data.global.viewMode!=='list'" ng-animate="{show:'animate-show', hide:'animate-hide'}" class="js-map" data-options='{"dragging":true, "zoomControl":true, "doubleClickZoom":true, "scrollWheelZoom":true }'>
    </div><!--#search-map-container-->

<!-- Here ends the map view and starts the list view -->
    <div id="lista" ng-show="data.global.viewMode==='list'" ng-animate="{show:'animate-show', hide:'animate-hide'}">
        
        <?php $this->part('search/list-opportunity'); ?>
        <?php $this->part('search/list-project'); ?>
        <?php $this->part('search/list-agent'); ?>
        <?php $this->part('search/list-space'); ?>
        <?php $this->part('search/list-event'); ?>
        
    </div>
