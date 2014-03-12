(function(angular){
    "use strict";

    var emptyFilter = function (input, levels) {
        // return a filtered object without nulls
        if(levels === undefined) levels = 4;
        if(levels === 0) return angular.copy(input);
        var output = {};
        if(angular.isArray(input)) {
            output = [];
        }

        var currVal;
        angular.forEach(input, function(value, key){
            if(typeof value === 'undefined' ||
               value === null ||
               value === '') {
                return;
            }

            if(angular.isObject(value)) {
                currVal = emptyFilter(value, levels - 1);
            } else {
                currVal = value;
            }

            if(angular.isArray(output)) {
                this.push(currVal);
            } else {
                this[key] = currVal;
            }
        }, output);

        return output;
    };

    var skeletonData = {
            global: {
                isCombined: true,
                viewMode: 'map',
                filterEntity: 'event',
                locationFilters: {
                    enabled: null, // circle, address, neighborhood
                    circle: {
                        center: {
                            lat: null,
                            lng: null
                        },
                        radius: null,
                    },
                    address: {
                        text: '',
                        center: {
                            lat: null,
                            lng: null
                        }
                    },
                    neighborhood: {
                        center: {
                            lat: null,
                            lng: null
                        }
                    }
                },
                map: {
                    zoom: null,
                    center: {
                        lat: null,
                        lng: null
                    }
                },
                enabled: {
                    agent: true,
                    space: true,
                    event: false
                }
            },
            agent: {
                isVerified: false,
                keyword: '',
                areas: [],
                type: null
            },
            space: {
                keyword: 'biblioteca',
                areas: [],
                types: [],
                acessibilidade: false
            },
            // event: {
            //     keyword: '',
            //     linguagens: [],
            //     between: {
            //         start: null,
            //         end: null
            //     },
            //     classificacaoEtaria: null
            // }
        };

    var app = angular.module('search', ['ng-mapasculturais', 'SearchService', 'SearchMap', 'SearchSpatial', 'rison']);

    app.controller('SearchController', ['$scope', '$rootScope', '$location', '$rison', '$window', '$timeout', 'SearchService', function($scope, $rootScope, $location, $rison, $window, $timeout, SearchService){
        $scope.data = angular.copy(skeletonData);

        $scope.areas = MapasCulturais.taxonomyTerms.area.map(function(el, i){ return {id: i, name: el}; });
        $scope.types = MapasCulturais.entityTypes;

        $scope.$watch('data', function(newValue, oldValue){
            if(newValue === undefined) return;
            var serialized = $rison.stringify(emptyFilter(newValue));

            if($location.hash() !== serialized){
                $location.hash(serialized);
                $timeout.cancel($scope.timer);
                $scope.timer = $timeout(function() {
                    $rootScope.$emit('searchDataChange', $scope.data);
                }, 1000);
            }
        }, true);

        $rootScope.$on('$locationChangeSuccess', function(){
            var newValue = $location.hash();

            if(newValue && newValue !== $rison.stringify(emptyFilter($scope.data))){
                $scope.data = angular.extend(angular.copy(skeletonData), $rison.parse(newValue));
                $rootScope.$emit('searchDataChange', $scope.data);

            }
        });

        $rootScope.$on('searchDataChange', function(ev, data) {
            console.log('searchDataChange emitted', data);
            SearchService($scope.data);
        });

        $scope.getName = function(valores, id){
            try{
                return valores.filter(function(e){if(e.id === id) return true;})[0].name;
            }catch(e){
                return false;
            }
        };

        angular.element(document).ready(function(){
            $window.leaflet.map.removeLayer($window.leaflet.marker);
            $window.leaflet.map.on('zoomend dragend', function(){
                $scope.data.global.map = {
                    center : $window.leaflet.map.getCenter(),
                    zoom : $window.leaflet.map.getZoom()
                };
                $scope.$apply();
            });
        });


        $scope.tabClick = function(entity){
            $scope.data.global.filterEntity = entity;
        }


    }]);
})(angular);
