(function(angular) {
    var app = angular.module('search.controller.spatial', ['ng-mapasculturais']);
    app.controller('SearchSpatialController', ['$window', '$scope', '$location', "$rootScope", "$timeout", function($window, $scope, $location, $rootScope, $timeout) {

        var map = null;
        
        var labels = MapasCulturais.gettext.controllerSpatial;

        angular.element(document).ready(function() {
            map = $window.leaflet.map;
            if(!map) return;
            map.invalidateSize();
            var drawnItems = new L.FeatureGroup();
            map.addLayer(drawnItems);
            $window.leaflet.map.drawnItems = drawnItems;


            if($scope.data.global.locationFilters.enabled){
                var lf = $scope.data.global.locationFilters;
                (new L.Circle(
                    new L.LatLng(lf[lf.enabled].center.lat, lf[lf.enabled].center.lng),
                    lf[lf.enabled].radius,
                    {className : 'vetorial-padrao'}
                ).addTo(map.drawnItems));

                if($scope.data.global.locationFilters.enabled == 'address'){
                    filterAddress ();
                }
            }


            L.drawLocal.draw.handlers.circle.tooltip.start = labels['tooltip.start'];
            L.drawLocal.draw.handlers.circle.tooltip.end = labels['tooltip.end'];
            L.drawLocal.draw.toolbar.actions.title = labels['title'];
            L.drawLocal.draw.toolbar.actions.text = labels['text'];
            L.drawLocal.draw.toolbar.buttons.circle = labels['circle'];

            L.Draw.Circle = L.Draw.Circle.extend({
                _onMouseMove: function(e) {
                    var latlng = e.latlng,
                        showRadius = this.options.showRadius,
                        useMetric = this.options.metric,
                        radius;

                    this._tooltip.updatePosition(latlng);
                    if (this._isDrawing) {
                        this._drawShape(latlng);

                        // Get the new radius (rounded to 1 dp)
                        radius = this._shape.getRadius().toFixed(1);

                        this._tooltip.updateContent({
                            text: L.drawLocal.draw.handlers.circle.tooltip.end,
                            subtext: showRadius ? labels['radius'] + ': ' + L.GeometryUtil.readableDistance(radius, useMetric).replace('.',',') : ''
                        });
                    }
                }
            });

            var drawControl = new L.Control.Draw({
                draw: {
                    position: 'topleft',
                    polygon: false,
                    rectangle: false,
                    marker: false,
                    polyline: false,
                    circle: {
                        shapeOptions: {
                            className : 'vetorial-padrao'
                        }
                    }
                },
                edit: false,
            });
            map.addControl(drawControl);

            map.on('draw:created', function(e) {
                var type = e.layerType,
                    layer = e.layer;

                if (type === 'circle') {
                    $scope.data.global.locationFilters = {
                        enabled : 'circle',
                        circle : {
                            center : {
                                lat: layer._latlng.lat,
                                lng: layer._latlng.lng
                            },
                            radius: parseInt(layer._mRadius),
                        }
                    };
                    $scope.$apply();
                }


                if (type === 'marker') {
                    layer.bindPopup('A popup!');
                }

                if($window.leaflet.locationMarker) $window.leaflet.map.removeLayer($window.leaflet.locationMarker);
                drawnItems.addLayer(layer);

                //ESCONDE O CONTROLE PARA POSTERIORMENTE USAR O BOTÃO (NÃO CONSEGUI SETAR OS EVENTOS DO DRAW CIRCLE SEM ESTE CONTROLE
                //document.querySelector('.leaflet-draw-draw-circle').style.display = 'none';

            });


            //ESCONDE O CONTROLE PARA POSTERIORMENTE USAR O BOTÃO (NÃO CONSEGUI SETAR OS EVENTOS DO DRAW CIRCLE SEM ESTE CONTROLE
//            document.querySelector('.leaflet-draw-draw-circle').style.display = 'none';


            map.on('locationfound', function(e) {
                $window.$timout.cancel($window.dataTimeout);
                var radius = e.accuracy / 2,
                    neighborhoodRadius = $scope.defaultLocationRadius;
                
                var currentLocationLabel = labels['currentLocation'];
                currentLocationLabel.replace('{{errorMargin}}', radius.toString().replace('.',','));
                currentLocationLabel.replace('{{radius}}', neighborhoodRadius/1000);
                
                var marker = L.marker(e.latlng, $window.leaflet.iconOptions['location']).addTo(map)
                    .bindPopup(currentLocationLabel)
                    .openPopup();

                var circle = L.circle(e.latlng, $scope.defaultLocationRadius, {className : 'vetorial-padrao'}).addTo(map.drawnItems);


                $scope.data.global.locationFilters = {
                    enabled : 'neighborhood',
                    neighborhood : {
                        center : {
                            lat: map.getCenter().lat,
                            lng: map.getCenter().lng
                        },
                        radius : $scope.defaultLocationRadius
                    }
                };
                $scope.$apply();

                if($window.leaflet.locationMarker) {
                    $window.leaflet.map.removeLayer($window.leaflet.locationMarker);
                    $window.leaflet.map.removeLayer($window.leaflet.locationCircle);
                }
                $window.leaflet.locationMarker = marker;
                $window.leaflet.locationCircle = circle;

            });

            map.on('locationerror', function(e) {
                /* @TODO alert de erro para o usuário */
                //console.log(e.message);
            });

            //to fix address field not getting focus on touch screens
            $('#endereco').on('click dblclick', function(){
                var $self = $(this);
                $self.focus();
                $self.stopPropagation();
                //loose focus on click outslide
                $('body').one('click', function(event){
                    if($self.parent().find(event.target).length == 0){
                        $self.blur();
                    }
                });
            });

        });


        $scope.filterNeighborhood = function (){
            $window.leaflet.map.locate({setView : true, maxZoom:13});
        };


        function filterAddress () {
            var addressString = $scope.data.global.locationFilters.address.text;

            MapasCulturais.geocoder.geocode({'fullAddress': addressString}, function(results) {
                if (results) {
                    $window.$timout.cancel($window.dataTimeout);
                    var foundLocation = new L.latLng(results.lat, results.lon);

                    $window.leaflet.map.setView(foundLocation, 13);

                    if($window.leaflet.locationMarker) {
                        $window.leaflet.map.removeLayer($window.leaflet.locationMarker);
                        $window.leaflet.map.removeLayer($window.leaflet.locationCircle);
                    }
                    $window.leaflet.locationMarker = new L.marker(foundLocation, $window.leaflet.iconOptions['location']).addTo($window.leaflet.map);
                    $window.leaflet.locationCircle = L.circle(foundLocation, $scope.defaultLocationRadius, {className : 'vetorial-padrao'})
                            .addTo($window.leaflet.map.drawnItems);

                    $scope.data.global.locationFilters = {
                        enabled : 'address',
                        address : {
                            text : $scope.data.global.locationFilters.address.text,
                            center : {
                                lat: results.lat,
                                lng: results.lng
                            },
                            radius : $scope.defaultLocationRadius
                        }
                    };
                    $scope.$apply();

                }
            });
        };

        $scope.$watch('data.global.locationFilters.address.text', function(newValue, oldValue){
            if(!newValue && !oldValue || newValue == oldValue){
                return;
            }
            //if(newValue === '' || newValue === oldValue) return;
            if(!newValue || !newValue.trim()) {
                $scope.data.global.locationFilters.enabled = null;
                if($rootScope.resultLayer.getBounds()._northEast)
                    map.fitBounds($rootScope.resultLayer.getBounds());
                return;
            }
            $timeout.cancel($scope.timer2);
            $scope.timer2 = $timeout(function() {
                filterAddress();
            }, 1000);
        });

    }]);
})(angular);
