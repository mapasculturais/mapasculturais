(function(angular) {
    "use strict";

    var app = angular.module('SearchService', ['angularSpinner']);
    app.factory('searchService', ['$http', '$rootScope', '$q', function($http, $rootScope, $q){
        $rootScope.lastResult = $rootScope.lastResult || { agent:null, space:null, event:null };
        var activeRequests = 0,
            canceler = null,
            lastEmitedResult = 'null',
            lastEmitedCountResult = 'null',
            lastQueries = {enabledEntities: null, space: null, agent: null, event: null, project: null, listedEntity: null, list:null, page: null};

        $rootScope.spinnerCount = $rootScope.spinnerCount || 0;

        $rootScope.$on('searchDataChange', search);
        $rootScope.$on('resultPagination', search);

        $rootScope.searchArgs = {
            list: {},
            map: {}
        };

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

            if(data.global.viewMode === 'map'){
                var compareEnabledEntities = angular.equals(lastQueries.enabledEntities, data.global.enabled);
                if(data.global.enabled.agent){
                    var agentQueryData = data2searchData(data.agent);
                    if(!angular.equals(agentQueryData, lastQueries.agent) || !compareEnabledEntities){
                        lastQueries.agent = angular.copy(agentQueryData);
                        callApi('agent', agentQueryData);
                    }else{
                        results.agent = $rootScope.lastResult.agent;
                    }
                }

                if(data.global.enabled.event){
                    var eventQueryData = data2searchData(data.event);
                    if(!angular.equals(eventQueryData, lastQueries.event) || !compareEnabledEntities){
                        lastQueries.event = angular.copy(eventQueryData);
                        callApi('event', eventQueryData);
                    }else{
                        results.event = $rootScope.lastResult.event;
                    }
                }

                if(data.global.enabled.space){
                    var spaceQueryData = data2searchData(data.space);
                    if(!angular.equals(spaceQueryData, lastQueries.space) || !compareEnabledEntities){
                        lastQueries.space = angular.copy(spaceQueryData);
                        callApi('space', spaceQueryData);
                    }else{
                        results.space = $rootScope.lastResult.space;
                    }
                }

                lastQueries.enabledEntities = angular.copy(data.global.enabled);
            }else{
                var activeEntity = data.global.filterEntity;
                var listQueryData = data2searchData(data[activeEntity]);

                if(activeEntity !== lastQueries.listedEntity)
                    $rootScope.pagination[activeEntity] = 1;

                var isDiff = (paginating && $rootScope.pagination[activeEntity] !== lastQueries.page) || (!angular.equals(listQueryData, lastQueries.list) || lastQueries.listedEntity !== activeEntity);

                if( isDiff ){
                    $rootScope.isPaginating = true;
                    lastQueries.listedEntity = activeEntity;
                    lastQueries.list = angular.copy(listQueryData);
                    callApi(activeEntity, angular.copy(listQueryData));
                }else{
                    $rootScope.isPaginating = false;
                }
            }

            endCountRequest();
            endRequest();

            function callApi(entity, sData){
                var requestEntity = entity,
                    requestAction = 'find';

                if(entity === 'event'){
                    if(data.global.viewMode === 'list'){
                        requestAction = 'findByLocation';
                    }else{
                        requestEntity = 'space' ;
                        requestAction = 'findByEvents';
                    }

                }

                $rootScope.searchArgs[data.global.viewMode][entity] = sData;


                //Counting XX events in YY spaces (events in map mode)
                if(requestEntity === 'space' && requestAction === 'findByEvents'){

                   var otherRequestEntity = 'event';
                   var otherRequestAction = 'findByLocation';

                   numCountRequests+=2;
                   activeRequests+=2;
                   $rootScope.spinnerCount+=2;

                   countResults['event'] = {};

                   apiCount(otherRequestEntity, sData, otherRequestAction).success(function(rs){
                       numCountSuccessRequests++;
                       activeRequests--;
                       $rootScope.spinnerCount--;

                       countResults['event'].events = rs;
                       endCountRequest();
                   });

                   apiCount(requestEntity, sData, requestAction).success(function(rs){
                       numCountSuccessRequests++;
                       activeRequests--;
                       $rootScope.spinnerCount--;

                       countResults['event'].spaces = rs;
                       endCountRequest();
                   });

                }else{
                    // DEFAULT CASE
                    numCountRequests++;
                    activeRequests++;
                    $rootScope.spinnerCount ++ ;
                    apiCount(requestEntity, sData, requestAction).success(function(rs){
                        numCountSuccessRequests++;
                        activeRequests--;
                        $rootScope.spinnerCount--;
                        countResults[entity] = rs;
                        endCountRequest();
                    });
                }

                numRequests++;
                activeRequests++;
                $rootScope.spinnerCount++;
                apiFind(requestEntity, sData, $rootScope.pagination[entity], requestAction).success(function(rs){
                    numSuccessRequests++;
                    activeRequests--;
                    $rootScope.spinnerCount--;

                    results[entity] = rs;

                    endRequest();
                });

            }

            function countAndRemoveResultsNotInMap(entity, results){
                results[entity].forEach(function(item, index) {
                    if (!item.location || (item.location.latitude == 0 && item.location.longitude == 0)) {
                        $rootScope.resultsNotInMap[entity]++;
                    }
                });
            }

            function endRequest(){
                if(numRequests > 0 && numSuccessRequests === numRequests && lastEmitedResult !== JSON.stringify(results)){
                    if(data.global.viewMode === 'map') {
                        $rootScope.resultsNotInMap = {agent: 0, space: 0, event: 0};
                        if(results.agent) {
                            countAndRemoveResultsNotInMap('agent', results);
                        }
                        if(results.space) {
                            countAndRemoveResultsNotInMap('space', results);
                        }
                        if(results.event) {
                            countAndRemoveResultsNotInMap('event', results);
                        }
                    }

                    lastEmitedResult = JSON.stringify(results);
                    results.paginating = paginating;
                    $rootScope.lastResult = results;
                    $rootScope.$emit('searchResultsReady', results);
                }
            }

            function endCountRequest(){
                if(numCountRequests > 0 && numCountSuccessRequests === numCountRequests && lastEmitedCountResult !== JSON.stringify(countResults)){
                    $rootScope.$emit('searchCountResultsReady', countResults);
                }
            }

            function data2searchData(entityData){
                var searchData = {};

                if(entityData.keyword){
                    searchData['@keyword'] = entityData.keyword;
                }

                if(entityData.areas && entityData.areas.length){
                    var selectedAreas = entityData.areas.map(function(e){
                        return MapasCulturais.taxonomyTerms.area[e];
                    });
                    selectedAreas = selectedAreas.map(function(e){ return e.replace(',','\,'); });
                    searchData['term:area'] = 'IN(' + selectedAreas  + ')';
                }

                if(entityData.linguagens && entityData.linguagens.length){
                    var selectedLinguagens = entityData.linguagens.map(function(e){
                        return MapasCulturais.taxonomyTerms.linguagem[e];
                    });
                    selectedLinguagens = selectedLinguagens.map(function(e){ return e.replace(',','\\,'); });

                    searchData['term:linguagem'] = 'IN(' + selectedLinguagens + ')';
                }

                if(entityData.type){
                    searchData.type = 'EQ(' + entityData.type + ')';
                }

                if(entityData.types && entityData.types.length){
                    searchData.type = 'IN(' + entityData.types + ')';
                }

                if(entityData.classificacaoEtaria && entityData.classificacaoEtaria.length){
                    var selectedClassificacoesEtarias = entityData.classificacaoEtaria.map(function(e){
                        return MapasCulturais.classificacoesEtarias[e];
                    });
                    searchData.classificacaoEtaria = 'IN(' + selectedClassificacoesEtarias + ')';
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

                // project registration is open?
                if(entityData.ropen){
                    var today = moment().format('YYYY-MM-DD');
                    searchData.registrationFrom = 'LTE(' + today + ')';
                    searchData.registrationTo   = 'GTE(' + today + ')';
                }


                return searchData;
            }

            function apiFind(entity, searchData, page, action) {
                if(data.global.viewMode === 'list'){
                    searchData['@select'] = 'id,singleUrl,name,type,shortDescription,terms';
                    if(entity === 'space')
                        searchData['@select'] += ',endereco,acessibilidade';
                    else if(entity === 'project')
                        searchData['@select'] += ',registrationFrom,registrationTo';
                    else if(entity === 'event')
                        searchData['@select'] += ',classificacaoEtaria,project.name,project.singleUrl,occurrences';

                    searchData['@files'] = '(avatar.avatarMedium):url';
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
                return $http({method: 'GET', timeout: canceler.promise, url: MapasCulturais.baseURL + 'api/' + entity + '/' + action + '/?'+querystring, data:searchData});
            }

            function apiCount(entity, searchData, action) {

                action = action || 'find';
                var querystring = "";

                for(var att in searchData) {
                    querystring += "&"+att+"="+searchData[att];
                }
                return $http({method: 'GET', timeout: canceler.promise, url: MapasCulturais.baseURL + 'api/'+entity+'/' + action + '/?@count=1&'+querystring, data:searchData});
            }
        }

        return 'done';
    }]);

})(angular);




