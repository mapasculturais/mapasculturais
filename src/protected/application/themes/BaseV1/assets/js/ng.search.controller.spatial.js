(function(angular) {
    var app = angular.module('search.controller.spatial', ['ng-mapasculturais']);
    app.controller('SearchSpatialController', ['$window', '$scope', '$location', "$rootScope", "$timeout", function($window, $scope, $location, $rootScope, $timeout) {

        var map = null;

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


            L.drawLocal.draw.handlers.circle.tooltip.start = 'Cliquee y arrastre para diseñar el círculo';
            L.drawLocal.draw.handlers.circle.tooltip.end = 'Suelte el mouse para finalizar el diseño';
            L.drawLocal.draw.toolbar.actions.title = 'Cancelar diseño';
            L.drawLocal.draw.toolbar.actions.text = 'Cancelar';
            L.drawLocal.draw.toolbar.buttons.circle = 'Diseñar un círculo';

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
                            subtext: showRadius ? 'Radio: ' + L.GeometryUtil.readableDistance(radius, useMetric).replace('.',',') : ''
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

                var marker = L.marker(e.latlng, $window.leaflet.iconOptions['location']).addTo(map)
                    .bindPopup("Según su navegador, usted está aproximadamente en este punto con un margen de error de " + radius.toString().replace('.',',') + " metros. Buscando resultados dentro de un radio de " + neighborhoodRadius/1000 + "KM de este punto. <a onclick='document.querySelector(\".leaflet-draw-draw-circle\").click()'>Modificar</a>")
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


        var geocoder = null;
        if(typeof google !== 'undefined')
            geocoder =  new google.maps.Geocoder();

        // callback to handle google geolocation result
        function geocode_callback(results, status) {
            if(typeof google === 'undefined'){
                return false;
            }
            if (status == google.maps.GeocoderStatus.OK) {
                var location = results[0].geometry.location;
                var foundLocation = new L.latLng(location.lat(), location.lng());
                map.setView(foundLocation, 15);
                marker.setLatLng(foundLocation);
            }
        }

        function filterAddress () {
            var geocoder = null;
            var addressString = $scope.data.global.locationFilters.address.text + ', Brasil';

            if (!google){
                return;
            }else{
                geocoder = new google.maps.Geocoder();
            }
            geocoder.geocode({'address': addressString}, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    $window.$timout.cancel($window.dataTimeout);
                    var location = results[0].geometry.location;
                    var foundLocation = new L.latLng(location.lat(), location.lng());

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
                                lat: location.lat(),
                                lng: location.lng()
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
