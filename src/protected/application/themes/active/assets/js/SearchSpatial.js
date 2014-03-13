(function(angular) {
    var app = angular.module('SearchSpatial', ['ng-mapasculturais', 'SearchService', 'angularSpinner']);
    app.controller('SearchSpatialController', ['$window', '$scope', '$location', 'SearchService', "$rootScope", function($window, $scope, $location, SearchService, $rootScope) {

        angular.element(window).load(function() {
            var map = window.leaflet.map;

            var drawnItems = new L.FeatureGroup();
            map.addLayer(drawnItems);
            window.leaflet.map.drawnItems = drawnItems;

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



            });


            //ESCONDE O CONTROLE PARA POSTERIORMENTE USAR O BOTÃO (NÃO CONSEGUI SETAR OS EVENTOS DO DRAW CIRCLE SEM ESTE CONTROLE
            document.querySelector('.leaflet-draw-draw-circle').style.display = 'none';




            map.on('locationfound', function(e) {
                var radius = e.accuracy / 2,
                    neighborhoodRadius = $scope.data.global.locationFilters.neighborhood.radius;

                var marker = L.marker(e.latlng).addTo(map)
                    .bindPopup("Segundo seu navegador, você está aproximadamente neste ponto com margem de erro de " + radius.toString().replace('.',',') + " metros. Buscando resultados dentro de um raio de " + neighborhoodRadius/1000 + "KM deste ponto. <a href='#' onclick='document.querySelector(\".leaflet-draw-draw-circle\").click()'>Modificar</a>")
                    .openPopup();

                var circle = L.circle(e.latlng, neighborhoodRadius).addTo(map.drawnItems);


                $scope.data.global.locationFilters = {
                    enabled : 'neighborhood',
                    neighborhood : {
                        center : {
                            lat: map.getCenter().lat,
                            lng: map.getCenter().lng
                        },
                        radius : neighborhoodRadius
                    }
                };
                $scope.$apply();


                window.leaflet.locationMarker = marker;
                window.leaflet.locationCircle = circle;

                document.querySelector('.leaflet-draw-draw-circle').addEventListener('click', function(){
                    if(window.leaflet.locationMarker) {
                        window.leaflet.map.removeLayer(window.leaflet.locationMarker);
                        window.leaflet.map.removeLayer(window.leaflet.locationCircle);
                    }
                }, false);


            });

            map.on('locationerror', function(e) {
                console.log(e.message);
            });

        });




        $scope.drawCircle = function() {
            document.querySelector('.leaflet-draw-draw-circle').click();
        };

        $scope.filterNeighborhood = function (){
            window.leaflet.map.locate({setView : true, maxZoom:13});
        };


    }]);
})(angular);