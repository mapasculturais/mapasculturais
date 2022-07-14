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
            var entities = ['space', 'event', 'agent'];
            if(data.global.viewMode === 'map'){
                var compareEnabledEntities = angular.equals(lastQueries.enabledEntities, data.global.enabled);
                var entityQueryData = {};

                // adiciona os filtros avançados utilizados pelo tema ao skeleton acima
                entities.forEach(function(entity){
                    if(data.global.enabled[entity]) {
                        entityQueryData = data2searchData(entity, data[entity]);
                        if(!angular.equals(entityQueryData, lastQueries[entity]) || !compareEnabledEntities){
                            lastQueries[entity] = angular.copy(entityQueryData);
                            callApi(entity, entityQueryData);
                        }else{
                            results[entity] = $rootScope.lastResult[entity];
                        }

                        if(entity !== lastQueries.listedEntity)
                            $rootScope.pagination[activeEntity] = 1;
                        }
                });

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
                        if(data.userManagerment){
                            requestAction = 'find';
                        }else{
                            requestAction = 'findByLocation';
                        }
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
                   
                    if(requestEntity === 'space' && requestAction === 'findByEvents') {
                        countResults[entity].spaces = metadata.count;
                    }else if(requestEntity === 'event' && requestAction === 'findByLocation' && metadata == null){
                        countResults['event'] = 0;
                    }else{
                        countResults[entity] = metadata.count;
                    }
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

                for (var search_filter in entityData.filters){

                    if(entityData.from)
                        searchData['@from'] = moment(entityData.from).format('YYYY-MM-DD');
                    if(entityData.to)
                        searchData['@to'] = moment(entityData.to).format('YYYY-MM-DD');

                    if(entityData.ropen){
                        var today = moment().format('YYYY-MM-DD HH:mm');
                        searchData.registrationFrom = 'LTE(' + today + ')';
                        searchData.registrationTo   = 'GTE(' + today + ')';
                    }
                    
                    if(search_filter == 'documento') {
                        searchData['documento'] = entityData.filters['documento']; 
                    }
                    if(entityData.filters[search_filter]){
                        var filter = MapasCulturais.filters[entity].filter(function(f){
                            return f.filter.param === search_filter;
                        })[0];
                        if(filter) {
                            if (!filter.isArray) {
                                searchData[filter.prefix + filter.filter.param] = filter.filter.value.replace(/\{val\}/g, entityData.filters[search_filter]);
                            } else if (entityData.filters[search_filter].length){
                                if (filter.type === 'term'){
                                    var search_value = entityData.filters[search_filter].map(function(e){
                                        return MapasCulturais.taxonomyTerms[filter.filter.param][e] || e;
                                    });
                                    searchData['term:'+ filter.prefix + filter.filter.param] = filter.filter.value.replace(/\{val\}/g, search_value.join(','));
                                } else
                                    searchData[filter.prefix + filter.filter.param] = filter.filter.value.replace(/\{val\}/g, entityData.filters[search_filter].join(','));
                            }
                        }
                    }
                }

                if(data.global.locationFilters.enabled !== null){
                    var type = data.global.locationFilters.enabled;
                    var center = data.global.locationFilters[type].center;
                    var radius = data.global.locationFilters[type].radius;
                    searchData._geoLocation = 'GEONEAR(' + center.lng + ',' + center.lat + ',' + radius + ')';
                }
                if(entityData.sort) {
                    searchData['@order'] = entityData.sort.sortBy + ' ' + entityData.sort.type;
                }
                return searchData;
            }

            function apiFind(entity, searchData, page, action) {
                if(MapasCulturais.searchFilters && MapasCulturais.searchFilters[entity]){
                    angular.extend(searchData, MapasCulturais.searchFilters[entity]);
                }

                var selectData = MapasCulturais.searchQueryFields;
                
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
                    selectData += ',classificacaoEtaria,project.name,project.singleUrl,occurrences.{*,space.{*}}';
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
                delete searchData['@count'];

                var querystring = '';
                var Description = MapasCulturais.EntitiesDescription[exportEntity];
                var exportSelect = ['singleUrl,isVerified,type,terms'];
                var dontExportSelect = {
                    user: true,
                    publicLocation: true,
                    status: true,
                    subsite: true,
                    sentNotification: true
                }
                Object.keys(Description).forEach(function(prop) {
                    if(prop[0] == '_'){
                        return;
                    }
                    if (dontExportSelect[prop])
                        return;
                        
                    var def = Description[prop];
                    var selectProperty = def['@select'] || prop;
                    if(def.isMetadata || (!def.isMetadata && !def.isEntityRelation)){
                        
                        // Não adiciona os metadados geograficos que devem ser ocultos (que começam com "_")
                        if (isRemovableMeta(prop))
                            return;
                        
                        exportSelect.push(selectProperty); 
                    } else if(def.isEntityRelation) {
                        if(def.isOwningSide){
                            exportSelect.push(prop + '.{id,name,singleUrl}');
                        } else if (prop == 'occurrences') {
                            exportSelect.push('occurrences.{space.{id,name,singleUrl,En_CEP,' + 
                                'En_Nome_Logradouro,En_Num,En_Complemento,En_Bairro,En_Municipio,En_Estado},rule}');
                        }
                    }
                });
                
                var queryString_apiExport = '@select='+exportSelect.join(',');

                //removes type column from event export
                if(apiExportURL.indexOf('event/findByLocation') !== -1)
                    queryString_apiExport = queryString_apiExport.replace(',type','');
                else
                    apiExportURL += entity + '/' + action + '/?';

                for(var att in searchData) {
                    querystring += "&"+att+"="+searchData[att];
                    if(att != '@select' && att!='@page' && att!='@limit' && att!='@files')
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

            function isRemovableMeta(prop) {
                var isGeo = prop.substr(0,4) == 'geo_';
                var isFva = prop.substr(0,3) == 'fva';

                return isGeo || isFva;
            }
        }

        return 'done';
    }]);

})(angular);




