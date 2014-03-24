(function(angular) {
    var app = angular.module('SearchSpatial', ['ng-mapasculturais']);
    app.controller('SearchSpatialController', ['$window', '$scope', '$location', "$rootScope", "$timeout", function($window, $scope, $location, $rootScope, $timeout) {

        angular.element(document).ready(function() {
            var map = window.leaflet.map;
            map.invalidateSize();
            var drawnItems = new L.FeatureGroup();
            map.addLayer(drawnItems);
            window.leaflet.map.drawnItems = drawnItems;
            

            if($scope.data.global.locationFilters.enabled){
                var lf = $scope.data.global.locationFilters;
                (new L.Circle(
                    new L.LatLng(lf[lf.enabled].center.lat, lf[lf.enabled].center.lng),
                    lf[lf.enabled].radius
                ).addTo(map.drawnItems));
                
                if($scope.data.global.locationFilters.enabled == 'address'){
                    filterAddress ();
                }        
            }


            L.drawLocal.draw.handlers.circle.tooltip.start = 'Clique e arraste para desenhar o círculo';
            L.drawLocal.draw.handlers.circle.tooltip.end = 'Solte o mouse para finalizar o desenho';
            L.drawLocal.draw.toolbar.actions.title = 'Cancelar desenho';
            L.drawLocal.draw.toolbar.actions.text = 'Cancelar';
            L.drawLocal.draw.toolbar.buttons.circle = 'Desenhar um círculo';

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
                            subtext: showRadius ? 'Raio: ' + L.GeometryUtil.readableDistance(radius, useMetric).replace('.',',') : ''
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
                    circleasd: {
                        shapeOptions: {
                            color: '#662d91',
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

                if(window.leaflet.locationMarker) window.leaflet.map.removeLayer(window.leaflet.locationMarker);
                drawnItems.addLayer(layer);

                //ESCONDE O CONTROLE PARA POSTERIORMENTE USAR O BOTÃO (NÃO CONSEGUI SETAR OS EVENTOS DO DRAW CIRCLE SEM ESTE CONTROLE
                //document.querySelector('.leaflet-draw-draw-circle').style.display = 'none';

            });


            //ESCONDE O CONTROLE PARA POSTERIORMENTE USAR O BOTÃO (NÃO CONSEGUI SETAR OS EVENTOS DO DRAW CIRCLE SEM ESTE CONTROLE
//            document.querySelector('.leaflet-draw-draw-circle').style.display = 'none';


            map.on('locationfound', function(e) {
                var radius = e.accuracy / 2,
                    neighborhoodRadius = $scope.defaultLocationRadius;

                var marker = L.marker(e.latlng).addTo(map)
                    .bindPopup("Segundo seu navegador, você está aproximadamente neste ponto com margem de erro de " + radius.toString().replace('.',',') + " metros. Buscando resultados dentro de um raio de " + neighborhoodRadius/1000 + "KM deste ponto. <a href='#' onclick='document.querySelector(\".leaflet-draw-draw-circle\").click()'>Modificar</a>")
                    .openPopup();

                var circle = L.circle(e.latlng, $scope.defaultLocationRadius).addTo(map.drawnItems);


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

                if(window.leaflet.locationMarker) {
                    window.leaflet.map.removeLayer(window.leaflet.locationMarker);
                    window.leaflet.map.removeLayer(window.leaflet.locationCircle);
                }
                window.leaflet.locationMarker = marker;
                window.leaflet.locationCircle = circle;

            });

            map.on('locationerror', function(e) {
                console.log(e.message);
            });


        });


        $scope.filterNeighborhood = function (){
            window.leaflet.map.locate({setView : true, maxZoom:13});
        };


        var geocoder = null;
        if(typeof google !== 'undefined')
            geocoder =  new google.maps.Geocoder();

        // callback to handle google geolocation result
        function geocode_callback(results, status) {
            if(typeof google === 'undefined'){
                console.log('Mapas Culturais: Google Maps API não encontrada.');
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
                console.log('Mapas Culturais: Não foi possível acessar a API do Google Maps');
                return;
            }else{
                geocoder = new google.maps.Geocoder();
            }
            geocoder.geocode({'address': addressString}, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    
                    var location = results[0].geometry.location;
                    var foundLocation = new L.latLng(location.lat(), location.lng());
                    
                    console.log(foundLocation);
                    window.leaflet.map.setView(foundLocation, 13);
                    
                    if(window.leaflet.locationMarker) {
                        window.leaflet.map.removeLayer(window.leaflet.locationMarker);
                        window.leaflet.map.removeLayer(window.leaflet.locationCircle);
                    }
                    window.leaflet.locationMarker = new L.marker(foundLocation).addTo(window.leaflet.map);
                    window.leaflet.locationCircle = L.circle(foundLocation, $scope.defaultLocationRadius)
                            .addTo(window.leaflet.map.drawnItems);
            
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
            if(!newValue || !newValue.trim()) {
                $scope.data.global.locationFilters.enabled = null;
                return;
            }
            $timeout.cancel($scope.timer2);
            $scope.timer2 = $timeout(function() {
                filterAddress();
            }, 500);
        });

    }]);
})(angular);
