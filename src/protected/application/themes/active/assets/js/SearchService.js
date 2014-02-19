(function(angular) {
    var app = angular.module('SearchService', []);
    app.factory('SearchService', ['$http', function($http){
        return function(entity, searchData, page) {
            searchData['@select'] = 'id,singleUrl,location,name,type,shortDescription,terms';
            searchData['@offset'] = (page-1)*10;
            searchData['@files'] = '(avatar.avatarBig)'
            //searchData['@limit'] = 10;
            searchData['@order'] = 'name ASC';

            var querystring = "";

            for(var att in searchData) {
                querystring += "&"+att+"="+searchData[att];
            }
            console.log(searchData)
            return $http({method: 'GET', url: MapasCulturais.baseURL + 'api/'+entity+'/find/?'+querystring, data:searchData});
        };
    }]);
})(angular);