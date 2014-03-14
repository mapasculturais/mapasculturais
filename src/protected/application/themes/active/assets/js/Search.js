(function(angular){
    "use strict";

    var skeletonData = {
            global: {
                isVerified: false,
                isCombined: false,
                viewMode: 'map',
                filterEntity: null,
                locationFilters: {
                    enabled: null, // circle, address, neighborhood
                    circle: {
                        center: {
                            lat: null,
                            lng: null
                        },
                        radius: null,
                    },
                    neighborhood: {
                        center: {
                            lat: null,
                            lng: null
                        },
                        radius: 2000
                    },
                    address: {
                        text: '',
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
                    agent: false,
                    space: false,
                    event: false
                }
            },
            agent: {
                isVerified: false,
                keyword: '',
                areas: [],
                type: null,
                id: null
            },
            space: {
                keyword: '',
                areas: [],
                types: [],
                acessibilidade: false,
                id: null
            },
            event: {
                keyword: '',
                linguagens: [],
                from: null,
                to: null,
                classificacaoEtaria: null
            }
        };

    var diffFilter = function (input) {
        return _diffFilter(input, skeletonData);
    };

    var isEmpty = function (value) {
        if(typeof value === 'undefined' ||
           value === null) return true;

        if(angular.isObject(value)) {
            if(angular.equals(value, {}) ||
               angular.equals(value, []))
                return true;
        }

        return false;
    };

    var _diffFilter = function (input, skeleton) {
        // returns the difference from the input structure and skeleton
        // don't include nulls

        if(typeof input === 'undefined' || typeof skeleton === 'undefined' || input === skeleton) return;

        if(!angular.isObject(input)|| angular.isArray(skeleton)) {
            return input;
        }

        var output = {};

        angular.forEach(input, function(value, key){
            var currVal = _diffFilter(value, skeleton[key]);

            if(isEmpty(currVal)) return;
            this[key] = currVal;
        }, output);

        return output;
    };

    var app = angular.module('search', ['ng-mapasculturais', 'SearchService', 'SearchMap', 'SearchSpatial', 'rison']);

    app.controller('SearchController', ['$scope', '$rootScope', '$location', '$rison', '$window', '$timeout', 'SearchService', function($scope, $rootScope, $location, $rison, $window, $timeout, SearchService){
        $scope.data = angular.copy(skeletonData);
        $scope.data.global.filterEntity = 'agent';
        $scope.data.global.enabled.agent = true;

        $scope.areas = MapasCulturais.taxonomyTerms.area.map(function(el, i){ return {id: i, name: el}; });
        $scope.linguagens = MapasCulturais.taxonomyTerms.linguagem.map(function(el, i){ return {id: i, name: el}; });
        MapasCulturais.entityTypes.agent.push({id:null, name: 'Todos'});
        $scope.types = MapasCulturais.entityTypes;

        $scope.dataChange = function(newValue, oldValue){
            newValue = $scope.data;
            if(newValue === undefined) return;
            var serialized = $rison.stringify(diffFilter(newValue));

            if($location.hash() !== serialized){
                $location.hash(serialized);
                $timeout.cancel($scope.timer);
                $scope.timer = $timeout(function() {
                    $rootScope.$emit('searchDataChange', $scope.data);
                }, 1000);
            }
        };
        $scope.$watch('data', $scope.dataChange, true);

        $rootScope.$on('$locationChangeSuccess', function(){
            var newValue = $location.hash();

            if(newValue && newValue !== $rison.stringify(diffFilter($scope.data))){
                $scope.data = angular.extend(angular.copy(skeletonData), $rison.parse(newValue));
                $rootScope.$emit('searchDataChange', $scope.data);

            }
        });

        $rootScope.$on('searchDataChange', function(ev, data) {
            console.log('searchDataChange emitted', data);
            SearchService($scope.data);
        });

        $scope.getName = function(valores, id){
            return valores.filter(function(e){if(e.id === id) return true;})[0].name;
        };

        $scope.isSelected = function(array, id){
            return (array.indexOf(id) !== -1);
        };

        $scope.toggleSelection = function(array, id){
            var index = array.indexOf(id);
            if(index !== -1){
                array.splice(index, 1);
            } else {
                array.push(id);
            }
        };

        $scope.hasFilter = function() {
            var ctx = {has: false};
            angular.forEach($scope.data, function(value, key) {
                if(key === 'global') return;
                this.has = this.has || !angular.equals(_diffFilter($scope.data[key], skeletonData[key]), {});
            }, ctx);

            return ctx.has ||
                   $scope.data.global.isVerified ||
                   $scope.data.global.locationFilters.enabled !== null;
        };

        $scope.cleanAllFilters = function () {
            angular.forEach($scope.data, function(value, key) {
                if(key === 'global') return;
                $scope.data[key] = angular.copy(skeletonData[key]);
            });
            $scope.data.global.isVerified = false;
            $scope.data.global.locationFilters = angular.copy(skeletonData.global.locationFilters);
        };

        $scope.cleanLocationFilters = function() {
            $scope.data.global.locationFilters = angular.copy(skeletonData.global.locationFilters);
        };

        $scope.tabClick = function(entity){
            var g = $scope.data.global;
            if(g.isCombined) {
                // combined search click:
                if(g.enabled[entity]){
                    // if the entity is already enabled and it's the last one enabled, avoid disabling
                    if(Object.keys(g.enabled).filter(function(e){if(g.enabled[e]) return e;}).length==1){
                        return;
                    }else{
                        g.enabled[entity] = false;
                    }
                }else{
                    g.enabled[entity] = true;
                }
            }else{
                g.filterEntity = entity;
                angular.forEach(g.enabled, function(value, key) {
                    g.enabled[key] = (key===entity);
                });
            }
        };

        $scope.tabOver = function(entity){
            if($scope.data.global.isCombined){
                $scope.data.global.filterEntity = entity;
            }
        };

        $scope.toggleCombined = function () {
            var g = $scope.data.global;
            if(g.isCombined) {
                g.isCombined = false;
                if(Object.keys(g.enabled).length > 1){
                    angular.forEach(g.enabled, function(value, key) {
                        g.enabled[key] = key===g.filterEntity;
                    });
                }
            }else{
                g.isCombined = true;
                angular.forEach(g.enabled, function(value, key) {
                    g.enabled[key] = true;
                });
            }
        };

        $scope.numAgents = 0;
        $scope.numSpaces = 0;
        $scope.numEvents = 0;

        $rootScope.$on('searchCountResultsReady', function(ev, results){
            console.log('================= ', results);
            $scope.numAgents = results.agent;
            $scope.numSpaces = results.space;
            $scope.numEvents = results.event;
        });

    }]);
})(angular);
