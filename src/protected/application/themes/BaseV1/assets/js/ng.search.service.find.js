(function(angular) {
    "use strict";

    var app = angular.module('search.service.find', []);
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


            if(!paginating){
                $rootScope.resetPagination();
            }

            var activeEntity = data.global.filterEntity;
            if(data.global.viewMode === 'map'){
                var compareEnabledEntities = angular.equals(lastQueries.enabledEntities, data.global.enabled);
                
                var entityQueryData = data2searchData(activeEntity, data[activeEntity]);
                if(!angular.equals(entityQueryData, lastQueries[activeEntity]) || !compareEnabledEntities){
                    lastQueries[activeEntity] = angular.copy(entityQueryData);
                    callApi(activeEntity, entityQueryData);
                }else{
                    results[activeEntity] = $rootScope.lastResult[activeEntity];
                }
                
                lastQueries.enabledEntities = angular.copy(data.global.enabled);
            }else{
                
                var listQueryData = data2searchData(activeEntity, data[activeEntity]);

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

                   numCountRequests++;
                   activeRequests++;
                   $rootScope.spinnerCount++;

                   countResults['event'] = {};

                   apiCount(otherRequestEntity, sData, otherRequestAction).success(function(rs){
                       numCountSuccessRequests++;
                       activeRequests--;
                       $rootScope.spinnerCount--;

                       countResults['event'].events = rs;
                       endCountRequest();
                   });

                }

                numRequests++;
                activeRequests++;
                $rootScope.spinnerCount++;
                
                apiFind(requestEntity, sData, $rootScope.pagination[entity], requestAction).success(function(rs,status,header){
                    var metadata = JSON.parse(header('API-Metadata'));
                    numSuccessRequests++;
                    activeRequests--;
                    $rootScope.spinnerCount--;

                    results[entity] = rs;

                    endRequest();

                    if(requestEntity === 'space' && requestAction === 'findByEvents')
                        countResults[entity].spaces = metadata.count;
                    else
                        countResults[entity] = metadata.count;
                    numCountSuccessRequests++;
                    numCountRequests++;
                    endCountRequest();
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

            function data2searchData(entity, entityData){
                var searchData = {};

                if(entityData.keyword){

                    searchData['@keyword'] = entityData.keyword.replace(/ /g,'%25');
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
                    searchData['@from'] = moment(entityData.from).format('YYYY-MM-DD');

                if(entityData.to)
                    searchData['@to'] = moment(entityData.to).format('YYYY-MM-DD');

                // project registration is open?
                if(entityData.ropen){
                    var today = moment().format('YYYY-MM-DD');
                    searchData.registrationFrom = 'LTE(' + today + ')';
                    searchData.registrationTo   = 'GTE(' + today + ')';
                }
                
                Object.keys(data[entity].advancedFilters).forEach(function(key){
                    var val = data[entity].advancedFilters[key];
                    var filter = MapasCulturais.advancedFilters[entity].find(function(filter){
                        if(filter.filter.param === key){
                            return filter;
                        }
                    })

                    if(filter.parseValue && filter.parseValue.length > 0){
                        filter.parseValue.forEach(function(parser){
                            switch(parser){
                                case 'join':
                                    val = val.join(',');
                                break;
                            }
                        });
                    }

                    if(val){
                        var parsed = filter.filter.value.replace(/\{val\}/g, val);
                        searchData[key] = parsed;
                    }
                });

                console.log(entity, searchData, entityData);
                return searchData;
            }

            function apiFind(entity, searchData, page, action) {
                
                
                if(MapasCulturais.searchFilters && MapasCulturais.searchFilters[entity]){
                    angular.extend(searchData, MapasCulturais.searchFilters[entity]);
                    console.log(entity , searchData, MapasCulturais.searchFilters);
                }
                
                var selectData = 'id,singleUrl,name,type,shortDescription,terms';
                var apiExportURL = MapasCulturais.baseURL + 'api/';
                var exportEntity = entity;
                if(entity === 'space'){
                    if(action === 'find') {
                        selectData += ',endereco,acessibilidade';
                    }else{
                    	exportEntity = 'event';
                        selectData += ',classificacaoEtaria,project.name,project.singleUrl,occurrences';
                        apiExportURL += 'event/findByLocation/?';
                    }
                }else if (entity === 'project'){
                    selectData += ',registrationFrom,registrationTo';
                }else if(entity === 'event'){
                    selectData += ',classificacaoEtaria,project.name,project.singleUrl,occurrences';
                }

                if(data.global.viewMode === 'list'){
                    searchData['@select'] = selectData;
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

                var querystring = '';
                var Description = MapasCulturais.EntitiesDescription[exportEntity];
                Object.keys(Description).forEach(function(prop) {
                	if (!Description[prop].isEntityRelation && (MapasCulturais.allowedFields || !Description[prop].private)) {
                		if (Description[prop]['@select']) {
                			prop = Description[prop]['@select'];
                		}
                		selectData += "," + prop;
                	}
                })
                
                var queryString_apiExport = '@select='+selectData;

                //removes type column from event export
                if(apiExportURL.indexOf('event/findByLocation') !== -1)
                    queryString_apiExport = queryString_apiExport.replace(',type','');
                else
                    apiExportURL += entity + '/' + action + '/?';

                for(var att in searchData) {
                    querystring += "&"+att+"="+searchData[att];
                    if(att != '@select' && att!='@page' && att!='@limit')
                        queryString_apiExport += "&"+att+"="+searchData[att];
                }
                
                $rootScope.apiURL = apiExportURL+queryString_apiExport;

                return $http({method: 'GET', cache:true, url:MapasCulturais.baseURL + 'api/' + entity + '/' + action + '/?'+querystring , data:searchData});
            }

            function apiCount(entity, searchData, action) {

                action = action || 'find';
                var querystring = "";

                for(var att in searchData) {
                    querystring += "&"+att+"="+searchData[att];
                }
                return $http({method: 'GET', cache:true, url: MapasCulturais.baseURL + 'api/'+entity+'/' + action + '/?@count=1&'+querystring, data:searchData});
            }
        }

        return 'done';
    }]);

})(angular);




