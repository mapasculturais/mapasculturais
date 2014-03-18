
(function(angular) {
    var app = angular.module('SearchMap', ['ng-mapasculturais', 'FindOneService']);
    app.controller('SearchMapController', ['$window', '$scope', '$rootScope', 'FindOneService', function($window, $scope, $rootScope, FindOneService) {

        $scope.init = function (){

            if($scope.data.global.map && $scope.data.global.map.zoom){
                MapasCulturais.mapCenter = $scope.data.global.map.center;
            }else{
                MapasCulturais.mapCenter = null;
            }

            $scope.map = null;
            $scope.resultLayer = null;
            $scope.markers = [];

            angular.element(document).ready(function(){
                $scope.map = $window.leaflet.map;
                $scope.map.removeLayer($window.leaflet.marker);

                $scope.map.on('load', function(){
                    $scope.setMapView();
                });

                $scope.map.on('zoomend moveend', function(){
                    $scope.data.global.map = {
                        center : $window.leaflet.map.getCenter(),
                        zoom : $window.leaflet.map.getZoom()
                    };
                    $scope.$apply();
                });

                $scope.updateMap();
                $scope.setMapView();
                if($scope.data.global.openEntity && $scope.data.global.openEntity.id){
                    FindOneService($scope.data);
                }
            });



            $rootScope.$on('searchResultsReady', function(ev, results){
                delete $scope.markers;
                $scope.markers = [];
                if(results.agent) $scope.createMarkers('agent', results.agent);
                if(results.event) $scope.createMarkers('event', results.event);
                if(results.space) $scope.createMarkers('space', results.space);

                $scope.updateMap();
            });

            $rootScope.$on('searchDataChange', function(ev, data){
                $scope.map.invalidateSize();
                $scope.setMapView();
            });

        };

        $scope.setMapView = function(){
            if($scope.map && $scope.data.global.map && $scope.data.global.map.zoom) {
                $scope.map.setZoom($scope.data.global.map.zoom);
                $scope.map.panTo($scope.data.global.map.center);
            }
        };

        $scope.createMarkers = function(entity, results) {
            results.forEach(function(item) {
                var marker;
                //TEMPORARY PATCH FOR EVENTS... WITHOUT LOCATION
                if(!item.location) return;

                marker = new L.marker(
                    new L.LatLng(item.location.latitude, item.location.longitude),
                    $window.leaflet.iconOptions[entity]
                ).bindLabel(
                    item.name
                ).on('click', function() {

                    var infobox = document.querySelector('#infobox');
                    var infoboxContent = infobox.querySelector('article');

                    $scope.data.global.openEntity = {
                        id: item.id,
                        type: entity
                    };

                    $scope.openEntity = {};
                    $scope.openEntity[entity] = {name: item.name};

                    $scope.$apply();

                    FindOneService($scope.data);
                });

                if (item.location && (item.location.latitude !== 0 && item.location.longitude !== 0)) {
                    marker.entityType = entity;
                    $scope.markers.push(marker);

                }
            });
        };

        $scope.updateMap = function(){
            if($scope.resultLayer){
                $scope.resultLayer.clearLayers();

                //remove drawing if more than one
                if($scope.map.drawnItems){
                    if(!$scope.data.global.locationFilters.enabled)  {
                        $scope.map.drawnItems.clearLayers();
                        if(window.leaflet.locationMarker) { $scope.map.removeLayer(window.leaflet.locationMarker);}
                    }else if(Object.keys($scope.map.drawnItems._layers).length > 1) {
                        var lastLayer = $scope.map.drawnItems.getLayers().pop();
                        $scope.map.drawnItems.clearLayers();
                        $scope.map.drawnItems.addLayer(lastLayer);
                    }
                }

            }
            $scope.resultLayer = new L.markerClusterGroup({
                spiderfyOnMaxZoom: true,
                showCoverageOnHover: false,
                zoomToBoundsOnClick: false,
                spiderfyDistanceMultiplier:1.3,
                maxClusterRadius: 60,
                iconCreateFunction: function (cluster) {
                    var iconClass = 'leaflet-cluster',
                        markers = getChildMarkers(cluster),
                        hasAgent = false,
                        hasEvent = false,
                        hasSpace = false;

                    function getChildMarkers(cluster){
                        var markers = cluster._markers.slice();
                        cluster._childClusters.forEach(function(child_cluster){
                            markers = markers.concat(getChildMarkers(child_cluster));
                        });
                        return markers;
                    };

                    for(var i in markers){
                        if(markers[i].entityType === 'agent') hasAgent = true;
                        if(markers[i].entityType === 'event') hasEvent = true;
                        if(markers[i].entityType === 'space') hasSpace = true;
                        if(hasAgent && hasEvent && hasSpace) break;
                    }

                    if(hasAgent) iconClass += ' cluster-agent';
                    if(hasEvent) iconClass += ' cluster-event';
                    if(hasSpace) iconClass += ' cluster-space';

                    return L.divIcon({ html: cluster.getChildCount(), className: iconClass, iconSize: L.point(40, 40) });
                }
            });

            $scope.resultLayer.addLayers($scope.markers);

            var __c = 0;
            var _addLayer = $scope.resultLayer._addLayer;

            $scope.resultLayer._addLayer = function(layer, zoom){
                var r = _addLayer.apply(this,[layer, zoom]);// console.log(layer, zoom, __c++)

                var p = layer.__parent;

                while(p){
                    p.hasEntityType = p.hasEntityType || {};
                    p.hasEntityType[layer.entityType] = true;
                    p = p.__parent;
                }

                return r;
            };

            $scope.resultLayer.addTo($scope.map);

            $scope.resultLayer.on('clusterclick', function (a) {
                if(a.layer._childCount <= 6)
                    a.layer.spiderfy();
                else{
                    a.layer.zoomToBounds();
                }
            });


        };


        $scope.init();

    }]);

})(angular);
