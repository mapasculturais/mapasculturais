(function(angular){
    "use strict";

    var skeletonData = {
        global: {
            isVerified: false,
            isCombined: false,
            viewMode: 'map',
            filterEntity: null,
            openEntity: {
                id: null,
                type: null
            },
            locationFilters: {
                enabled: null, // circle, address, neighborhood
                circle: {
                    center: {
                        lat: null,
                        lng: null
                    },
                    radius: null
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
            keyword: '',
            areas: [],
            type: null,
            isVerified: false
        },
        space: {
            keyword: '',
            areas: [],
            types: [],
            acessibilidade: false,
            isVerified: false
        },
        event: {
            keyword: '',
            linguagens: [],
            from: null,
            to: null,
            classificacaoEtaria: null,
            isVerified: false
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

    var deepExtend = function (skeleton, extension) {
        angular.forEach(extension, function(value, key){
            if(angular.isObject(value) && !angular.isArray(value)) {
                deepExtend(skeleton[key], value);
                delete extension[key];
            }
        });
        angular.extend(skeleton, extension);
        return skeleton;
    };

    var app = angular.module('search', ['ng-mapasculturais', 'SearchService', 'SearchMap', 'SearchSpatial', 'rison', 'infinite-scroll', 'ui.date']);

    app.controller('SearchController', ['$scope', '$rootScope', '$location', '$log', '$rison', '$window', '$timeout', 'searchService', function($scope, $rootScope, $location, $log, $rison, $window, $timeout, searchService){

        $rootScope.resetPagination = function(){
            $rootScope.pagination = {
                agent: 1,
                space: 1,
                event: 1
            };
        }
        $rootScope.resetPagination();

        $scope.defaultImageURL = MapasCulturais.defaultAvatarURL;
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


        $scope.switchView = function (mode) {
            $scope.data.global.viewMode = mode;
            if(mode === 'map') {
                //temporary fixes to tim.js' adjustHeader()
                $window.scrollTo(0,1);
                $window.scrollTo(0,0);
            }
        };

        $scope.toggleVerified = function () {
            angular.forEach($scope.data, function(value, key) {
                $scope.data[key].isVerified = !$scope.data[key].isVerified;
            });
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

        $scope.parseHash = function(){
            var newValue = $location.hash();
            if(newValue === '') {
                $scope.tabClick('agent');
                return;
            }

            if(newValue !== $rison.stringify(diffFilter($scope.data))){
                $scope.data = deepExtend(angular.copy(skeletonData), $rison.parse(newValue));
                $rootScope.$emit('searchDataChange', $scope.data);
            }
        };

        $scope.dataChange = function(newValue, oldValue){
            if(newValue === undefined) return;
            var serialized = $rison.stringify(diffFilter(newValue));

            if($location.hash() !== serialized){
                $location.hash(serialized);
                $timeout.cancel($scope.timer);
                if(oldValue && !angular.equals(oldValue.global.enabled, newValue.global.enabled)) {
                    $rootScope.$emit('searchDataChange', $scope.data);
                } else {
                    $scope.timer = $timeout(function() {
                        $rootScope.$emit('searchDataChange', $scope.data);
                    }, 200);
                }
            }
        };

        $scope.data = angular.copy(skeletonData);

        $scope.areas = MapasCulturais.taxonomyTerms.area.map(function(el, i){ return {id: i, name: el}; });
        $scope.linguagens = MapasCulturais.taxonomyTerms.linguagem.map(function(el, i){ return {id: i, name: el}; });
        MapasCulturais.entityTypes.agent.push({id:null, name: 'Todos'});
        $scope.types = MapasCulturais.entityTypes;
        $scope.location = $location;

        $rootScope.$on('searchDataChange', function(ev, data) {
            console.log('ON searchDataChange', data);
        });

        $rootScope.$on('$locationChangeSuccess', $scope.parseHash);

        if($location.hash() === '') {
            $scope.tabClick('agent');
        } else {
            $scope.parseHash();
        }

        $scope.$watch('data', $scope.dataChange, true);


        $scope.agents = [];
        $scope.spaces = [];
        $scope.events = [];


        $rootScope.$on('searchResultsReady', function(ev, results){
            console.log(results);
            if(results.paginating){
                $scope.agents = $scope.agents.concat(results.agent ? results.agent : []);
                $scope.events = $scope.events.concat(results.event ? results.event : []);
                $scope.spaces = $scope.spaces.concat(results.space ? results.space : []);

                $scope.isPaginating = false;
            }else{
                $scope.agents = results.agent ? results.agent : [];
                $scope.events = results.event ? results.event : [];
                $scope.spaces = results.space ? results.space : [];
            }
        });

        var infiniteScrollTimeout = null;

        $scope.addMore = function(entity){
            if($scope.isPaginating)
                return;
            $scope.isPaginating = true;
            $rootScope.pagination[entity]++;
            $rootScope.$emit('resultPagination', $scope.data);
        };


        $scope.numAgents = 0;
        $scope.numSpaces = 0;
        $scope.numEvents = 0;

        $rootScope.$on('searchCountResultsReady', function(ev, results){
            console.log('================= SEARCH READY ', results);
            $scope.numAgents = parseInt(results.agent);
            $scope.numSpaces = parseInt(results.space);
            $scope.numEvents = parseInt(results.event);
        });

        $rootScope.$on('findOneResultReady', function(ev, result){
            console.log('================= FIND ONE READY', result);
            $scope.openEntity = result;
        });

        var formatDate = function(date){
            var d = date ? new Date(date + ' 12:00') : new Date();
            return d.toLocaleString('pt-BR',{ day: '2-digit', month:'2-digit', year:'numeric' });
        };

        $scope.dateOptions = {
            dateFormat: 'dd/mm/yy'
        };

        $scope.$watch('data.event.from', function(){
            if(new Date($scope.data.event.from) > new Date($scope.data.event.to))
                $scope.data.event.to = $scope.data.event.from;
        });

        $scope.$watch('data.event.to', function(newValue, oldValue){
            if(new Date($scope.data.event.to) < new Date($scope.data.event.from))
                $scope.data.event.from = $scope.data.event.to;
        });


       $scope.showEventDateFilter = function(){
            var from = $scope.data.event.from,
                to = $scope.data.event.to;

            return from && to && (formatDate(from) !== formatDate() || from !== to );
        };

        $scope.eventDateFilter = function(){
            var from = $scope.data.event.from,
                to = $scope.data.event.to;

            if(from === to)
                return formatDate(from);
            else
                return 'de ' + formatDate(from) + ' a ' + formatDate(to);
        };

        $scope.cleanEventDateFilters = function(){
            $scope.data.event.from = null;
            $scope.data.event.to = null;
        }
    }]);
})(angular);