(function(angular) {
    var app = angular.module('SearchService', []);
    app.factory('SearchService', ['$http', '$rootScope', function($http, $rootScope){
        return function(data){
            var select,
                numRequests = 0,
                numSuccessRequests = 0,
                results = {};


            if(data.global.viewMode === 'map'){
                select = 'id,name,location';
                page = null;
            }else{
                select = 'id,singleUrl,name,type,shortDescription,terms';
            }

            if(data.global.enabled.agent){
                numRequests++;
                getData('agent', select, data2searchData(data.agent), page).success(function(rs){
                    console.log('SUCCESS: agent');
                    numSuccessRequests++;
                    results.agent = rs;
                    endRequest();
                });
            }

            if(data.global.enabled.event){
                numRequests++;
                getData('event', select, data2searchData(data.event), page).success(function(rs){
                    console.log('SUCCESS: event');
                    numSuccessRequests++;
                    results.event = rs;
                    endRequest();
                });
            }

            if(data.global.enabled.space){
                numRequests++;
                getData('space', select, data2searchData(data.space), page).success(function(rs){
                    console.log('SUCCESS: space');
                    numSuccessRequests++;
                    results.space = rs;
                    endRequest();
                });
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

                if(data.global.locationFilters.enabled){
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




