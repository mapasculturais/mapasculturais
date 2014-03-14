(function(angular) {
    var app = angular.module('SearchService', []);
    app.factory('SearchService', ['$http', '$rootScope', function($http, $rootScope){

        return function(data){
            var select,
                numRequests = 0,
                numSuccessRequests = 0,
                numCountRequests = 0,
                numCountSuccessRequests = 0,
                results = {},
                countResults = {};

            $rootScope.apiCache = $rootScope.apiCache || {
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
            };

            if(data.global.viewMode === 'list'){
                select = 'id,singleUrl,name,type,shortDescription,terms';
            }else{
                select = 'id,name,location';
                page = null;
            }


            if(data.global.enabled.agent){
                callApi('agent');
            }

            if(data.global.enabled.event){
                callApi('event');
            }

            if(data.global.enabled.space){
                callApi('space');
            }

            function callApi(entity){
                var sData = data2searchData(data[entity]),
                    apiCountParams = JSON.stringify(sData),
                    apiParams = JSON.stringify([sData,page]),
                    requestEntity = entity === 'event' ? 'space' : entity,
                    requestAction = entity === 'event' ? 'findByEvents' : 'find';

                if($rootScope.apiCache[entity + 'Count'].params === apiCountParams){
                    console.log('COUNT CACHED: ' + entity);
                    countResults[entity] = $rootScope.apiCache[entity + 'Count'].num;
                    endCountRequest();
                }else{
                    numCountRequests++;
                    apiCount(requestEntity, sData, requestAction).success(function(rs){
                        console.log('COUNT SUCCESS: ' + entity);
                        numCountSuccessRequests++;
                        countResults[entity] = rs;

                        $rootScope.apiCache[entity + 'Count'].num = rs;

                        endCountRequest();
                    });

                    $rootScope.apiCache[entity + 'Count'].params = apiCountParams;
                }

                if($rootScope.apiCache[entity].params === apiParams){
                    console.log('CACHED: ' + entity);
                    results[entity] = $rootScope.apiCache[entity].result;
                    endRequest();

                }else{
                    numRequests++;
                    apiFind(requestEntity, select, sData, page, requestAction).success(function(rs){
                        console.log('SUCCESS: ' + entity);
                        numSuccessRequests++;
                        results[entity] = rs;

                        $rootScope.apiCache[entity].result = rs;

                        endRequest();
                    });

                    $rootScope.apiCache[entity].params = apiParams;
                }
            }

            function endRequest(){
                if(numSuccessRequests === numRequests){
                    $rootScope.$emit('searchResultsReady', results);
                }
            }

            function endCountRequest(){
                if(numCountSuccessRequests === numCountRequests){
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
                    var selectedLinguagens = entityData.areas.map(function(e){
                        return MapasCulturais.taxonomyTerms.linguagem[e];
                    });

                    searchData['term:linguagem'] = 'IN(' + selectedLinguagens + ')';
                }

                if(entityData.type){
                    searchData.type = 'IN(' + entityData.type + ')';
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

            function apiFind(entity, select, searchData, page, action) {
                action = action || 'find';
                searchData['@select'] = select;
                searchData['@order'] = 'name ASC';
                delete searchData['@count'];
                var querystring = "";

                for(var att in searchData) {
                    querystring += "&"+att+"="+searchData[att];
                }
                console.log({method: 'GET', url: MapasCulturais.baseURL + 'api/' + entity + '/' + action + '/?'+querystring, data:searchData});

                return $http({method: 'GET', url: MapasCulturais.baseURL + 'api/' + entity + '/' + action + '/?'+querystring, data:searchData});
            }

            function apiCount(entity, searchData, action) {

                action = action || 'find';
                var querystring = "";

                for(var att in searchData) {
                    querystring += "&"+att+"="+searchData[att];
                }
                console.log({method: 'GET', url: MapasCulturais.baseURL + 'api/'+entity+'/' + action + '/?@count=1&'+querystring, data:searchData});
                return $http({method: 'GET', url: MapasCulturais.baseURL + 'api/'+entity+'/' + action + '/?@count=1&'+querystring, data:searchData});
            }
        };



    }]);

})(angular);




