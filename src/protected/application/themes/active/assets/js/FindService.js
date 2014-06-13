(function(angular){
    "use strict";
    
    var app = angular.module('MapasCulturais', []);
    
    app.factory('FindService', ['$rootScope', '$http', function($rootScope, $http){
        var baseUrl = MapasCulturais.baseURL + '/api/';
        function extend (query){
            return angular.extend(query, {
                "@select": 'id,name,shortDescription,terms',
                "@files": '(avatar.avatarSmall):url'
            });
        };
        
        function request (url, query, success_cb, error_cb){
            query = extend(query);

            var p = $http({
                url: url,
                method: "GET",
                params: query
            });

            if( angular.isFunction( success_cb ) ){
                p.success( success_cb );
            }

            if( angular.isFunction( error_cb ) ){
                p.error( error_cb );
            }
        };
        
        return {
            
            find: function(entity, query, success_cb, error_cb){
                var url = baseUrl + entity + '/find';
                
                request(url, query, success_cb, error_cb);
            },
            
            findOne: function(entity, query, success_cb, error_cb){
                var url = baseUrl + entity + '/find';
                
                request(url, query, success_cb, error_cb);
            },
            
            pagination: function(entity, resultsPerPage, query, success_cb, error_cb){
                var url = baseUrl + entity + '/find',
                    page = 0, executing = false;
                
                function success () {
                    success_cb();
                    executing = false;
                }
                
                function error () {
                    error_cb();
                    executing = false;
                }
                
                return {
                    nextPage: function () {
                        if(!executing){
                            executing = true;
                            
                            page++;
                            
                            query['@page'] = page;
                            query['@limit'] = resultsPerPage;

                            request(url, query, success, error);
                        }
                    },
                    
                    currentPage: function (){
                        if(this._page > 0 && !executing){
                            executing = true;
                            
                            query['@page'] = page;
                            query['@limit'] = resultsPerPage;
                            
                            request(url, query, success, error);
                        }
                    },
                    
                    previousPage: function () {
                        if(this._page > 0 && !executing){
                            executing = true;
                            
                            page--;
                            
                            query['@page'] = page;
                            query['@limit'] = resultsPerPage;

                            request(url, query, success, error);
                        }
                    }
                };
            }
        };
    }]);
    
})(angular);

