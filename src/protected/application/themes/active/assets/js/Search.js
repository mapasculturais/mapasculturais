(function(angular){
    "use strict";
    var app = angular.module('search', ['ng-mapasculturais', 'SearchService', 'rison']);

    app.controller('SearchController', ['$scope', '$rootScope', '$location', '$rison', function($scope, $rootScope, $location, $rison){
        $scope.data = {
            global: {
                isVerified: true,
                isCombined: false,
                viewMode: 'map',
                filterEntity: 'agent',
                map: {
                    center: {
                        lat: null,
                        lng: null
                    },
                    zoom: null,
                    locationFilters: {
                        circle: {
                            center: null,
                            radius: null,
                            isNeighborhood: false
                        },
                    },
                },
                enabled: {
                    agent: true,
                    space: false,
                    event: false
                }
            },
            agent: {
                keyword: '',
                areas: [],
                type: null
            },
            space: {
                keyword: '',
                areas: [],
                types: [],
                acessibilidade: false
            },
            event: {
                keyword: '',
                linguagem: [],
                between: {
                    start: null,
                    end: null
                },
                classificacaoEtaria: null
            }
        };

        $scope.areas = MapasCulturais.taxonomyTerms.area.map(function(el, i){ return {id: i, name: el}; });

        $scope.$watch('data', function(newValue, oldValue){
            console.log('calling watch for ', newValue);
            if(newValue === undefined) return;
            console.log('zuado');
            if($location.hash() !== $rison.stringify(newValue)){
                console.log('changing location.hash');
                $location.hash($rison.stringify(newValue));
            }
        }, true);

        $rootScope.$on('$locationChangeSuccess', function(newValue, oldValue){
            if($location.hash() !== $rison.stringify($scope.data)){
                console.log('changing data object from ',$location.hash());
                $scope.data = $rison.parse($location.hash());
            }
        });

        $scope.getName = function(valores, id){
            try{
                return valores.filter(function(e){if(e.id === id) return true })[0].name;
            }catch(e){
                return false;
            }
        }
    }]);




})(angular);