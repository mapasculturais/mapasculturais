(function(angular) {
    var app = angular.module('SearchService', []);
    app.factory('SearchService', ['$http', '$rootScope', function($http, $rootScope){
        return function(data){
            var select,
                numRequests = 0,
                numSuccessRequests = 0,
                results = {},
                apiParams;

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
            };

            if(data.global.viewMode === 'list'){
                select = 'id,singleUrl,name,type,shortDescription,terms';
            }else{
                select = 'id,name,location';
                page = null;
            }

            if(data.global.enabled.agent){
                apiParams = JSON.stringify([data2searchData(data.agent),page]);

                if($rootScope.apiCache.agent.params === apiParams){
                    console.log('CACHED: agent');
                    results.agent = $rootScope.apiCache.agent.result;
                    endRequest();

                }else{
                    numRequests++;
                    getData('agent', select, data2searchData(data.agent), page).success(function(rs){
                        console.log('SUCCESS: agent');
                        numSuccessRequests++;
                        results.agent = rs;

                        $rootScope.apiCache.agent.result = rs;

                        endRequest();
                    });

                    $rootScope.apiCache.agent.params = apiParams;
                }
            }

            if(data.global.enabled.event){
                apiParams = JSON.stringify([data2searchData(data.event),page]);

                if($rootScope.apiCache.event.params === apiParams){
                    console.log('CACHED: event');
                    results.event = $rootScope.apiCache.event.result;
                    endRequest();

                }else{
                    numRequests++;
                    getData('event', select, data2searchData(data.event), page).success(function(rs){
                        console.log('SUCCESS: event');
                        numSuccessRequests++;
                        results.event = rs;

                        $rootScope.apiCache.event.result = rs;

                        endRequest();
                    });

                    $rootScope.apiCache.event.params = apiParams;
                }
            }

            if(data.global.enabled.space){
                apiParams = JSON.stringify([data2searchData(data.space),page]);

                if($rootScope.apiCache.space.params === apiParams){
                    console.log('CACHED: space');
                    results.space = $rootScope.apiCache.space.result;
                    endRequest();

                }else{
                    numRequests++;
                    getData('space', select, data2searchData(data.space), page).success(function(rs){
                        console.log('SUCCESS: space');
                        numSuccessRequests++;
                        results.space = rs;

                        $rootScope.apiCache.space.result = rs;

                        endRequest();
                    });

                    $rootScope.apiCache.space.params = apiParams;
                }
            }

            function endRequest(){
                if(numSuccessRequests == numRequests){
                    $rootScope.$emit('searchResultsReady', results);
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
                    searchData['term:area'] = 'IN(' + entityData.areas + ')';
                }

                if(entityData.linguagens && entityData.linguagens.length){
                    searchData['term:linguagem'] = 'IN(' + entityData.linguagens + ')';
                }

                if(entityData.isVerified){
                    searchData.isVerified = 'EQ(true)';
                }

                if(data.global.locationFilters && data.global.locationFilters.enabled){
                    var type = data.global.locationFilters.enabled;
                    var center = data.global.locationFilters[type].center;
                    var radius = 1000;
                    if(data.global.locationFilters[type].radius)
                        radius = data.global.locationFilters[type].radius;

                    searchData._geoLocation = 'GEONEAR(' + center.lng + ',' + center.lat + ',' + radius + ')';
                }

                return searchData;
            };

            function getData(entity, select, searchData, page) {
                searchData['@select'] = select;
                searchData['@order'] = 'name ASC';

                var querystring = "";

                for(var att in searchData) {
                    querystring += "&"+att+"="+searchData[att];
                }
                console.log({method: 'GET', url: MapasCulturais.baseURL + 'api/'+entity+'/find/?'+querystring, data:searchData});
                return $http({method: 'GET', url: MapasCulturais.baseURL + 'api/'+entity+'/find/?'+querystring, data:searchData});
            };
        };



    }]);

})(angular);




