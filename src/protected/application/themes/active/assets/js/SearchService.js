(function(angular) {
    "use strict";

    var app = angular.module('SearchService', ['angularSpinner']);
    app.factory('searchService', ['$http', '$rootScope', '$q', function($http, $rootScope, $q){
        var activeRequests= 0,
            canceler = null,
            apiCache = {
                list:{
                    agent: {
                        params: '',
                        result: []
                    },
                    space: {
                        params: '',
                        result: []
                    },
                    event: {
                        params: '',
                        result: []
                    },
                },
                map:{
                    agent: {
                        params: '',
                        result: []
                    },
                    space: {
                        params: '',
                        result: []
                    },
                    event: {
                        params: '',
                        result: []
                    },
                },
                agentCount: {
                    params: '',
                    num: 0
                },
                spaceCount: {
                    params: '',
                    num: 0
                },
                eventCount: {
                    params: '',
                    num: 0
                }
            },
            lastEmitedResult = 'null',
            lastEmitedCountResult = 'null';

        $rootScope.spinnerCount = $rootScope.spinnerCount || 0;

        $rootScope.$on('searchDataChange', search);
        $rootScope.$on('resultPagination', search);

        function search (ev, data){
            var results = {},
                numRequests = 0,
                numSuccessRequests = 0,
                numCountRequests = 0,
                numCountSuccessRequests = 0,
                countResults = {},
                paginating = ev.name === 'resultPagination';


            if(!paginating)
                $rootScope.resetPagination();

            // cancel all active requests
            if(canceler){
                canceler.resolve();
                $rootScope.spinnerCount -= activeRequests;
                activeRequests = 0;
            }

            canceler = $q.defer();

            if(data.global.enabled.agent){
                callApi('agent');
            }

            if(data.global.enabled.event){
                callApi('event');
            }

            if(data.global.enabled.space){
                callApi('space');
            }

            endCountRequest();
            endRequest();

            function callApi(entity){

                var sData = data2searchData(data[entity]),
                    apiCountParams = JSON.stringify(sData),
                    apiParams = JSON.stringify([sData,$rootScope.pagination[entity]]),
                    requestEntity = entity === 'event' ? 'space' : entity,
                    requestAction = entity === 'event' ? 'findByEvents' : 'find';

                if(apiCache[entity + 'Count'].params === apiCountParams){
                    countResults[entity] = apiCache[entity + 'Count'].num;

                }else{
                    numCountRequests++;
                    activeRequests++;
                    $rootScope.spinnerCount ++ ;
                    apiCount(requestEntity, sData, requestAction).success(function(rs){
                        numCountSuccessRequests++;
                        activeRequests--;
                        $rootScope.spinnerCount--;

                        countResults[entity] = rs;

                        apiCache[entity + 'Count'].num = rs;

                        endCountRequest();
                    });

                    apiCache[entity + 'Count'].params = apiCountParams;
                }

                if(apiCache[data.global.viewMode][entity].params === apiParams){
                    results[entity] = apiCache[data.global.viewMode][entity].result;

                }else{
                    numRequests++;
                    activeRequests++;
                    $rootScope.spinnerCount++;
                    apiFind(requestEntity, sData, $rootScope.pagination[entity], requestAction).success(function(rs){
                        numSuccessRequests++;
                        activeRequests--;
                        $rootScope.spinnerCount--;

                        results[entity] = rs;

                        apiCache[data.global.viewMode][entity].result = rs;

                        endRequest();
                    });

                    apiCache[data.global.viewMode][entity].params = apiParams;
                }
            }

            function endRequest(){
                if(numSuccessRequests === numRequests && lastEmitedResult !== JSON.stringify(results)){
                    lastEmitedResult = JSON.stringify(results);
                    results.paginating = paginating;
                    $rootScope.$emit('searchResultsReady', results);
                }
            }

            function endCountRequest(){
                if(numCountSuccessRequests === numCountRequests && lastEmitedCountResult !== JSON.stringify(countResults)){
                    $rootScope.$emit('searchCountResultsReady', countResults);
                }
            }

            function data2searchData(entityData){
                var searchData = {};

                if(entityData.keyword){
                    //searchData.'OR()'
                    searchData.name = 'ILIKE(*' + entityData.keyword.replace(' ', '*') + '*)';
                    //searchData['term:tag'] = 'IN(' + entityData.keyword.replace(' ', ',') + ')';
                }

                if(entityData.areas && entityData.areas.length){
                    var selectedAreas = entityData.areas.map(function(e){
                        return MapasCulturais.taxonomyTerms.area[e];
                    });

                    searchData['term:area'] = 'IN(' + selectedAreas  + ')';
                }

                if(entityData.linguagens && entityData.linguagens.length){
                    var selectedLinguagens = entityData.linguagens.map(function(e){
                        return MapasCulturais.taxonomyTerms.linguagem[e];
                    });

                    searchData['term:linguagem'] = 'IN(' + selectedLinguagens + ')';
                }

                if(entityData.type){
                    searchData.type = 'EQ(' + entityData.type + ')';
                }

                if(entityData.types && entityData.types.length){
                    searchData.type = 'IN(' + entityData.types + ')';
                }

                if(entityData.acessibilidade){
                    searchData.acessibilidade = 'EQ(Sim)';
                }

                if(entityData.isVerified){
                    searchData.isVerified = 'EQ(true)';
                }

                if(data.global.locationFilters.enabled !== null){
                    var type = data.global.locationFilters.enabled;
                    var center = data.global.locationFilters[type].center;
                    var radius = data.global.locationFilters[type].radius;
                    searchData._geoLocation = 'GEONEAR(' + center.lng + ',' + center.lat + ',' + radius + ')';
                }

                if(entityData.from)
                    searchData['@from'] = entityData.from;

                if(entityData.to)
                    searchData['@to'] = entityData.to;

                return searchData;
            }

            function apiFind(entity, searchData, page, action) {
                if(data.global.viewMode === 'list'){
                    searchData['@select'] = 'id,singleUrl,name,type,shortDescription,terms';
                    searchData['@files'] = '(avatar.avatarBig):url';
                    if(page) {
                        searchData['@page'] = page;
                        searchData['@limit'] = '10';
                    }
                }else{
                    searchData['@select'] = 'id,name,location';
                }

                action = action || 'find';
                searchData['@order'] = 'name ASC';
                delete searchData['@count'];
                var querystring = "";

                for(var att in searchData) {
                    querystring += "&"+att+"="+searchData[att];
                }
                console.log({method: 'GET', timeout: canceler.promise, url: MapasCulturais.baseURL + 'api/' + entity + '/' + action + '/?'+querystring, data:searchData});
                return $http({method: 'GET', timeout: canceler.promise, url: MapasCulturais.baseURL + 'api/' + entity + '/' + action + '/?'+querystring, data:searchData});
            }

            function apiCount(entity, searchData, action) {

                action = action || 'find';
                var querystring = "";

                for(var att in searchData) {
                    querystring += "&"+att+"="+searchData[att];
                }
                console.log({method: 'GET', timeout: canceler.promise, url: MapasCulturais.baseURL + 'api/'+entity+'/' + action + '/?@count=1&'+querystring, data:searchData});
                return $http({method: 'GET', timeout: canceler.promise, url: MapasCulturais.baseURL + 'api/'+entity+'/' + action + '/?@count=1&'+querystring, data:searchData});
            }
        }

        return 'done';
    }]);

})(angular);




