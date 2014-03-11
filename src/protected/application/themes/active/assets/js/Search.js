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
                // isVerified: true,
                // isCombined: false,
                // viewMode: 'map',
                filterEntity: 'agent',
                map: {
                    zoom: null,
                    center: { lat: null, lng: null },
                    // locationFilters: {
                    //     circle: {
                    //         center: null,
                    //         radius: null,
                    //         isNeighborhood: false
                    //     },
                    // },
                },
                // enabled: {
                //     agent: true,
                //     space: false,
                //     event: false
                // }
            },
            agent: {
                keyword: '',
                areas: [],
                type: null
            },
            // space: {
            //     keyword: '',
            //     areas: [],
            //     types: [],
            //     acessibilidade: false
            // },
            // event: {
            //     keyword: '',
            //     linguagem: [],
            //     between: {
            //         start: null,
            //         end: null
            //     },
            //     classificacaoEtaria: null
            // }
        };

    var app = angular.module('search', ['ng-mapasculturais', 'SearchService', 'rison']);

    app.controller('SearchController', ['$scope', '$rootScope', '$location', '$rison', '$window', '$timeout', function($scope, $rootScope, $location, $rison, $window, $timeout){
        $scope.data = angular.copy(skeletonData);

        $scope.areas = MapasCulturais.taxonomyTerms.area.map(function(el, i){ return {id: i, name: el}; });

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
    }]);
})(angular);
