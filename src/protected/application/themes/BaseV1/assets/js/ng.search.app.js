(function(angular){
    "use strict";

    window.apply = null;

    var timeoutTime = 300;

    var defaultLocationRadius = 2000;
    
    var labels = MapasCulturais.gettext.searchApp;
    
    var defaultViewMode = MapasCulturais.defaultViewModeToList ? 'list' : 'map';
    
    var skeletonData = {
        global: {
            isVerified: false,
            isCombined: false,
            viewMode: defaultViewMode,
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
                    radius: defaultLocationRadius
                },
                address: {
                    text: '',
                    center: {
                        lat: null,
                        lng: null
                    },
                    radius: defaultLocationRadius
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
            showAdvancedFilters:false,
            filters: {},
            sort: {
                type: 'DESC', //DESC or ASC
                sortBy: 'createTimestamp',
                sortFields: {
                    0: {"label":labels['name'],"field":"name"},
                    1: {"label":labels['CreateDate'],"field":"createTimestamp"}
                }
            }
        },
        space: {
            keyword: '',
            showAdvancedFilters:false,
            filters: {},
            sort: {
                type: 'DESC', //DESC or ASC
                sortBy: 'createTimestamp',
                sortFields: {
                    0: {"label":labels['name'],"field":"name"},
                    1: {"label":labels['CreateDate'],"field":"createTimestamp"}
                }
            }
        },
        event: {
            keyword: '',
            from: moment().format('YYYY-MM-DD'),
            to: moment().add(1, 'year').format('YYYY-MM-DD'),
            showAdvancedFilters:false,
            filters: {}
            
        },
        project: {
            keyword: '',
            linguagens: [],
            types: [],
            isVerified: false,
            showAdvancedFilters:false,
            filters: {},
            sort: {
                type: 'DESC', //DESC or ASC
                sortBy: 'createTimestamp',
                sortFields: {
                   0: {"label":labels['name'],"field":"name"},
                   1: {"label":labels['CreateDate'],"field":"createTimestamp"}
                }
            }
        },
        opportunity: {
            keyword: '',
            linguagens: [],
            types: [],
            isVerified: false,
            ropen: false,
            showAdvancedFilters:false,
            filters: {},
            sort: {
                type: 'DESC', //DESC or ASC
                sortBy: 'createTimestamp',
                sortFields: {
                   0: {"label":labels['name'],"field":"name"},
                   1: {"label":labels['CreateDate'],"field":"createTimestamp"}
                }
            }
        }
    };

    var entities = ['space', 'event', 'agent', 'project', 'opportunity'];

    // adiciona os filtros avançados utilizados pelo tema ao skeleton acima
    entities.forEach(function(entity){
        MapasCulturais.filters[entity].forEach(function(filter){
            if(filter.isArray){
                skeletonData[entity].filters[filter.filter.param] = [];
            } else {
                skeletonData[entity].filters[filter.filter.param] = null;
            }
        });
    });

    var diffFilter = function (input) {
        return _diffFilter(input, skeletonData);
    };

    var isEmpty = function (value) {
        if(typeof value === 'undefined' ||
           value === null) return true;

        if(angular.isObject(value)) {
            if(angular.equals(value, {}) ||
               angular.equals(value, [])
            )
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

    var app = angular.module('search.app', MapasCulturais.ngSearchAppDependencies);

    app.controller('SearchController', ['$scope', '$rootScope', '$location', '$log', '$rison', '$window', '$timeout', 'searchService', 'FindOneService', 'loginService',
        function($scope, $rootScope, $location, $log, $rison, $window, $timeout, searchService, FindOneService, loginService)
        {
            $scope.defaultLocationRadius = defaultLocationRadius;
            $scope.shareurl = $location.absUrl();
            $rootScope.resetPagination = function(){
                $rootScope.pagination = {
                    agent: 1,
                    space: 1,
                    event: 1,
                    project: 1,
                    opportunity: 1,
                };
            }

            $scope.filters = MapasCulturais.filters;

            $rootScope.resetPagination();

            $scope.assetsUrl = MapasCulturais.assets;

            $scope.getFilter = function (filter_key){
                return MapasCulturais.filters[$scope.data.global.filterEntity].filter(function(f){
                    return f['filter'].param === filter_key;
                })[0];
            };

            $scope.getFilterTag = function(filter_key){
                var filter = $scope.getFilter(filter_key);
                return filter.tag || filter.label;
            };

            $scope.getFilterOptionLabel = function(filter_key, filter_value){
                return $scope.getFilter(filter_key).options.filter(function(option){
                        return option.value === filter_value;
                    })[0].label;
            };

            $scope.getId = function(valores, name){
                return valores.filter(function(e){if(e.name === name) return true;})[0].id;
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

            $scope.toggleVerified = function (entity) {
                    $scope.data[entity].isVerified = !$scope.data[entity].isVerified;
            };

            $scope.showInfobox = function (){
                return $scope.collapsedFilters && $scope.data.global.openEntity.id>0 && $scope.data.global.viewMode==='map' && $scope.data.global.enabled[$scope.data.global.openEntity.type];
            };

            $scope.showFilters = function(entity){
                if($scope.data.global.viewMode === 'map')
                    return $scope.data.global.enabled[entity];
                else
                    return $scope.data.global.filterEntity === entity;
            };

            $scope.hasAdvancedFilters = function(entity){
                return MapasCulturais.filters[entity].filter(function(v){
                    return !v.isInline;
                }).length > 0;
            };

            $scope.hasFilter = function() {
                var ctx = {has: false};
                angular.forEach($scope.data, function(value, key) {
                    if(key === 'global') return;
                    this.has = this.has || !angular.equals(_diffFilter($scope.data[key], skeletonData[key]), {});
                }, ctx);

                return ctx.has || $scope.data.global.locationFilters.enabled !== null;
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
                g.filterEntity = entity;
                if(entity === 'project' || entity === 'opportunity')
                    g.viewMode = 'list';

                if(g.viewMode === 'map'){
                    var n = 0;
                    for(var e in g.enabled)
                        if(g.enabled[e])
                            n++;

                    if(n===0 || n === 1 && !g.enabled[entity]){
                        for(var e in g.enabled)
                            if(e === entity)
                                g.enabled[e] = true;
                            else
                                g.enabled[e] = false;
                    }else if(n > 1 && !g.enabled[entity]){
                        g.enabled[entity] = true;
                    }
                }
            };

            $scope.defaultTab = function(){
                $scope.tabClick('event');
            };

            $scope.parseHash = function(){
                var newValue = $location.hash();
                if(newValue === '') {
                    $scope.defaultTab();
                    return;
                }

                if(newValue !== $rison.stringify(diffFilter($scope.data))){
                    $scope.data = deepExtend(angular.copy(skeletonData), $rison.parse(newValue));
                    $timeout.cancel($scope.timer);
                    $scope.timer = $timeout(function() {
                        $rootScope.$emit('searchDataChange', $scope.data);
                    },timeoutTime);
                }
            };

            $scope.dataChange = function(newValue, oldValue){
                if(newValue === undefined) return;
                if(newValue.global.viewMode === 'map'){
                    var filterEntity = newValue.global.filterEntity;
                    if(!newValue.global.enabled[filterEntity]){
                        var enabledEntities = 0;

                        angular.forEach(newValue.global.enabled, function(v,k){ if(v) enabledEntities++; });

                        if(enabledEntities === 1){
                            var obj = {space:false, agent:false, event:false};
                            obj[filterEntity] = true;
                            newValue.global.enabled = obj;
                        }else{
                            newValue.global.enabled[filterEntity] = true;
                        }
                        return;
                    }
                }
                var serialized = $rison.stringify(diffFilter(newValue));
                $window.$timout = $timeout;
                if($location.hash() !== serialized){
                    $timeout.cancel($scope.timer);
                    if(oldValue && !angular.equals(oldValue.global.enabled, newValue.global.enabled)) {
                        $location.hash(serialized);
                        $scope.timer = $timeout(function() {
                            $rootScope.$emit('searchDataChange', $scope.data);
                        },timeoutTime);
                    } else {
                        $scope.timer = $timeout(function() {
                            $location.hash(serialized);
                            $rootScope.$emit('searchDataChange', $scope.data);
                        }, timeoutTime);
                        $window.dataTimeout = $scope.timer;
                    }
                }
                $window.scrollTo(0, $window.scrollY+1);
                $window.scrollTo(0, $window.scrollY-1);
            };

            $scope.data = angular.copy(skeletonData);

            // $scope.areas = MapasCulturais.taxonomyTerms.area.map(function(el, i){ return {id: i, name: el}; });
            // $scope.linguagens = MapasCulturais.taxonomyTerms.linguagem.map(function(el, i){ return {id: i, name: el}; });
            // $scope.classificacoes = MapasCulturais.classificacoesEtarias.map(function(el, i){ return {id: i, name: el}; });

            MapasCulturais.entityTypes.agent.unshift({id:null, name: labels['all']});
            // $scope.types = MapasCulturais.entityTypes;
            // $scope.location = $location;

            $rootScope.$on('$locationChangeSuccess', $scope.parseHash);

            if($location.hash() === '') {
                $scope.defaultTab();
            } else {
                $scope.parseHash();
            }

            $scope.$watch('data', $scope.dataChange, true);

            $scope.agents = [];
            $scope.spaces = [];
            $scope.events = [];
            $scope.projects = [];
            $scope.opportunities = [];


            $rootScope.$on('searchResultsReady', function(ev, results){
                if($scope.data.global.viewMode !== 'list')
                    return;

                $rootScope.isPaginating = false;

                if(results.paginating){
                    $scope.agents = $scope.agents.concat(results.agent ? results.agent : []);
                    $scope.events = $scope.events.concat(results.event ? results.event : []);
                    $scope.spaces = $scope.spaces.concat(results.space ? results.space : []);
                    $scope.projects = $scope.projects.concat(results.project ? results.project : []);
                    $scope.opportunities = $scope.opportunities.concat(results.opportunity ? results.opportunity : []);
                }else{
                    $scope.agents = results.agent ? results.agent : [];
                    $scope.events = results.event ? results.event : [];
                    $scope.spaces = results.space ? results.space : [];
                    $scope.projects = results.project ? results.project : [];
                    $scope.opportunities = results.opportunity ? results.opportunity : [];
                }
            });

            var infiniteScrollTimeout = null;

            $scope.addMore = function(entity){
                var entityName = "";
                if($scope.data.global.viewMode !== 'list')
                    return;

                if(entity !== $scope.data.global.filterEntity)
                    return;

                if($rootScope.isPaginating)
                    return;

                if(entity == 'opportunity') {
                    entityName = 'opportunities';
                } else {
                    entityName = entity + 's'
                }

                if($scope[entityName].length === 0 || $scope[entityName].length < 10)
                    return;

                $rootScope.pagination[entity]++;
                // para não chamar 2 vezes o search quando está carregando a primeira página (o filtro mudou)
                if($rootScope.pagination[entity] > 1)
                    $rootScope.$emit('resultPagination', $scope.data);
            };

            $scope.numResults = function (num, entity){
                if($scope.data.global.viewMode === 'map' && $scope.resultsNotInMap && $scope.resultsNotInMap[entity]){
                    return num - $scope.resultsNotInMap[entity];
                }else{
                    return num;
                }
            };

            $scope.numAgents = 0;
            $scope.numSpaces = 0;
            $scope.numEvents = {
                events: 0,
                spaces: 0
            };
            $scope.numEventsInList = 0;
            $scope.numProjects = 0;
            $scope.numOpportunities = 0;

            $rootScope.$on('searchCountResultsReady', function(ev, results){
                $scope.numAgents = parseInt(results.agent);
                $scope.numSpaces = parseInt(results.space);

                if($scope.data.global.viewMode === 'list'){
                    $scope.numEventsInList = results.event;
                }else{
                    if(results.event){
                        $scope.numEvents = {
                            events: parseInt(results.event.events),
                            spaces: parseInt(results.event.spaces)
                        };
                    }else{
                        $scope.numEvents = {
                            events: 0,
                            spaces: 0
                        };
                    };
                }
                $scope.numProjects = parseInt(results.project);
                $scope.numOpportunities = parseInt(results.opportunity);
            });

            $rootScope.$on('findOneResultReady', function(ev, result){
                $scope.openEntity = result;
            });

            var formatDate = function(date){
                return moment(date).format('DD/MM/YYYY');
            };

            $scope.dateOptions = {
                dateFormat: 'dd/mm/yy'
            };

            $scope.$watch('data.event.from', function(){
                if(!/^[0-9]{4}(\-[0-9]{2}){2}$/.test($scope.data.event.from)){
                    $scope.data.event.from = moment($scope.data.event.from).format('YYYY-MM-DD');
                }

                if(new Date($scope.data.event.from) > new Date($scope.data.event.to)){
                    $scope.data.event.to = $scope.data.event.from;
                }
            });

            $scope.$watch('data.event.to', function(){
                if(!/^[0-9]{4}(\-[0-9]{2}){2}$/.test($scope.data.event.to)){
                    $scope.data.event.to = moment($scope.data.event.to).format('YYYY-MM-DD');
                }

                if(new Date($scope.data.event.to) < new Date($scope.data.event.from)){
                    $scope.data.event.from = $scope.data.event.to;
                }
            });

            $scope.showEventDateFilter = function(){
                var from = $scope.data.event.from,
                    to = $scope.data.event.to;

                return from !== skeletonData.event.from || to !== skeletonData.event.to;
            };

            $scope.eventDateFilter = function(){
                var from = $scope.data.event.from,
                    to = $scope.data.event.to;

                if(from === to)
                    return formatDate(from);
                else
                    return labels['dateFrom'] + ' ' + formatDate(from) + ' ' + labels['dateTo'] + ' ' + formatDate(to);
            };

            $scope.cleanEventDateFilters = function(){
                $scope.data.event.from = skeletonData.event.from;
                $scope.data.event.to = skeletonData.event.to;
            }

            $scope.readableProjectRegistrationDates = function(project){
                if(!project.registrationFrom)
                    return false;

                var from = moment(project.registrationFrom.date).format('DD/MM/YYYY');
                var to = moment(project.registrationTo.date).format('DD/MM/YYYY');

                return from !== to ? labels['dateFrom'] + ' ' + from + ' ' + labels['dateTo'] + ' ' + to : from;
            };

            $scope.readableOpportunitytRegistrationDates = function(opportunity){
                if(!opportunity.registrationFrom)
                    return false;

                var from = moment(opportunity.registrationFrom.date).format('DD/MM/YYYY');
                var to = moment(opportunity.registrationTo.date).format('DD/MM/YYYY');

                return from !== to ? labels['dateFrom'] + ' ' + from + ' ' + labels['dateTo'] + ' ' + to : from;
            };

            $scope.collapsedFilters = true;

            $scope.toggleAdvancedFilters = function(){

                $scope.collapsedFilters = !$scope.collapsedFilters;
                setTimeout(function(){
                    window.adjustHeader();
                }, 10);
            };

            $scope.showSearch = function(){

                if (document.body.clientWidth > 768) {
                    return true;
                } else {
                    return !$scope.collapsedFilters && !$scope.showInfobox();
                }
            };

            $scope.setRedirectUrl = function() {
                loginService.setLastUrl();
            };

            $scope.toggleSortOrder = function(orde) {
                if (orde != $scope.data[$scope.data.global.filterEntity].sort.type ) {
                    $scope.data[$scope.data.global.filterEntity].sort.type = $scope.data[$scope.data.global.filterEntity].sort.type == 'ASC' ? 'DESC' : 'ASC';
                }
            }
        }
    ]);
})(angular);
