
(function(angular) {
    var app = angular.module('SearchMap', ['ng-mapasculturais']);
    app.controller('SearchMapController', ['$window', '$scope', '$rootScope', function($window, $scope, $rootScope) {

        $scope.init = function (){

            $scope.map = null;
            $scope.resultLayer = null;

            //$scope.markers = [];
            $scope.markers = {
                agent : null,
                space : null,
                event : null
            };


            $rootScope.$on('searchResultsReady', function(ev, results){
                console.log('ON searchResultsReady', results);
                if(results.agents) $scope.createMarkers('agent', results.agents);
                if(results.events) $scope.createMarkers('event', results.events);
                if(results.spaces) $scope.createMarkers('space', results.spaces);

                $scope.updateMap();
            });

            $rootScope.$on('searchDataChange', function(ev, data){
                if($scope.map && data.global.map.zoom) {
                    $scope.map.setZoom(data.global.map.zoom);
                    $scope.map.panTo(data.global.map.center);
                }
            });

            angular.element(document).ready(function(){
                $scope.map = $window.leaflet.map;
                $scope.updateMap();
            });

        };

        $scope.createMarkers = function(entity, results){
            //console.log('process results', results);

            delete searchEntity.markers;

            results.forEach(function(item){
                var icon = '';
                var label = '';
                var mi = entity + '-' + item.id;

                window.lmarkers = window.lmarkers || {};

                if(true || !window.lmarkers[mi]){
                    if(item.location.latitude == 0 && item.location.longitude == 0){
                        searchEntity.resultsWithoutMarker++;
                        return;
                    }
                    label = item.name
                    icon = entity;
                    window.lmarkers[mi] = new L.marker(
                                new L.LatLng(item.location.latitude,item.location.longitude),
                                window.leaflet.iconOptions[icon]
                            )
                            .bindLabel(label)
                            .on('click', function(){
                                var listItem = document.querySelector('#'+entity+'-result-'+item.id);
                                var itemURL = listItem.querySelector(' a.js-single-url');
                                var infobox = document.querySelector('#infobox');
                                var infoboxContent = infobox.querySelector('article');
                                infoboxContent.innerHTML = listItem.innerHTML;
                                infobox.style.display = 'block';
                                infobox.className = 'objeto';
                                infobox.classList.add(searchEntity.cssClass);
                                console.log(listItem);
                                //itemURL.setAttribute('target', '_blank');
                                //a.click();
                            });

                    window.lmarkers[mi].entityType = entity;
                }
                $scope.markers.push(window.lmarkers[mi]);
            });
        };

        $scope.updateMap = function(){
            if($scope.resultLayer){
                $scope.resultLayer.clearLayers();
                $scope.resultLayer.addLayers($scope.markers);
                //$scope.viewLoading = false;
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



        };

        $scope.initMapFORA = function() {

            var self = this;
            window.leaflet.map.removeLayer(window.leaflet.marker);

            // CLEAR REMOVE LAYERS
            if($scope.resultLayer){

                //remove all markers clearing layers
                $scope.resultLayer.clearLayers();

                //remove drawing if more than one
                if(window.leaflet.map.drawnItems){
                    if(!$scope.searchManager.filterLocation) {
                        window.leaflet.map.drawnItems.clearLayers();
                        if(window.leaflet.locationMarker) { window.leaflet.map.removeLayer(window.leaflet.locationMarker);}
                    }else if(Object.keys(window.leaflet.map.drawnItems._layers).length > 1) {
                        var lastLayer = window.leaflet.map.drawnItems.getLayers().pop();
                        window.leaflet.map.drawnItems.clearLayers();
                        window.leaflet.map.drawnItems.addLayer(lastLayer);
                    }
                }

                return;
            }


            $scope.resultLayer = new L.markerClusterGroup({
                spiderfyOnMaxZoom: true,
                showCoverageOnHover: false,
                zoomToBoundsOnClick: false,
                spiderfyDistanceMultiplier:1.3,
                maxClusterRadius: 60,
                iconCreateFunction: function (cluster) {
                    var getChildMarkers = function getChildMarkers(cluster){
                        var markers = cluster._markers.slice();
                        cluster._childClusters.forEach(function(child_cluster){
                            markers = markers.concat(getChildMarkers(child_cluster));
                        });
                        return markers;
                    };

                    var markers = getChildMarkers(cluster);
                    var hasAgent = false, hasEvent = false, hasSpace = false;

                    for(var i in markers){
                        if(markers[i].entityType === 'agent') hasAgent = true;
                        if(markers[i].entityType === 'event') hasEvent = true;
                        if(markers[i].entityType === 'space') hasSpace = true;
                        if(hasAgent && hasSpace) break;
                    }

                    var iconClass = 'leaflet-cluster';
                    if(hasAgent) iconClass += ' cluster-agent';
                    if(hasEvent) iconClass += ' cluster-event';
                    if(hasSpace) iconClass += ' cluster-space';

                    return L.divIcon({ html: cluster.getChildCount(), className: iconClass, iconSize: L.point(40, 40) });
                }
            });

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
                // for (i in a.layer._group._featureGroup._layers) {
                //     a.layer._group._featureGroup._layers[i].showLabel();
                // };
            });
            $scope.resultLayer.on('clustermouseover', function(a){
                console.log(a.layer);
                //a.layer.spiderfy()
            });

        };

        $scope.init();

    }]);

})(angular);