(function(angular) {
    "use strict";

    var app = angular.module('FindOneService', []);
    app.factory('FindOneService', ['$http', '$rootScope', function($http, $rootScope){
        return function(data){
            var select = 'id,singleUrl,name,type,shortDescription,terms',
                requestAction = 'findOne',
                page=null,
                result = {},
                sData = {},
                entity;

            $rootScope.spinnerCount++;

            entity = data.global.openEntity.type;
            sData.id = 'EQ(' + data.global.openEntity.id + ')';

            apiFindOne(entity, select, sData, page, requestAction).success(function(rs){
                console.log('FIND ONE: ' + entity);
                result[entity] = rs;
                endRequest();
            });

            function endRequest(){
                $rootScope.spinnerCount--;
                $rootScope.$emit('findOneResultReady', result);
            }

            function apiFindOne(entity, select, searchData, page, action) {
                action = action || 'find';
                searchData['@select'] = select;
                searchData['@files'] = '(avatar.avatarBig):url';
                var querystring = "";
                for(var att in searchData) {
                    querystring += "&"+att+"="+searchData[att];
                }
                console.log({method: 'GET', url: MapasCulturais.baseURL + 'api/' + entity + '/' + action + '/?'+querystring, data:searchData});
                return $http({method: 'GET', url: MapasCulturais.baseURL + 'api/' + entity + '/' + action + '/?'+querystring, data:searchData});
            }
        };
    }]);
})(angular);




