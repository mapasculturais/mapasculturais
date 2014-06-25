(function(angular){
    "use strict";
    
    var app = angular.module('Entity', ['RelatedAgents', 'angularSpinner']);
    
    app.factory('FindService', ['$rootScope', '$http', function($rootScope, $http){
        var baseUrl = MapasCulturais.baseURL + '/api/';
        function extend (query){
            return angular.extend(query, {
                "@select": 'id,name,type,shortDescription,terms,avatar',
                "@files": '(avatar.avatarSmall):url',
                "@order": 'name'
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

    app.controller('EntityController',['$scope', '$timeout', 'RelatedAgents', function($scope, $timeout, RelatedAgents){
            
    }]);
        
    app.directive('findEntity', ['$timeout', 'FindService', function($timeout, FindService){
        var timeouts = {};
        
        return {
            restrict: 'E',
            templateUrl: MapasCulturais.assetURL + '/js/directives/find-entity.html',
            scope:{
                spinnerCondition: '=',
                entity: '@',
                noResultsText: '@',
                filter: '=',
                select: '='
            },
            
            link: function($scope, el, attrs){
                $scope.attrs = attrs;
                
                $scope.result = [];
                
                $scope.searchText = '';
                
                $scope.noEntityFound = false;
                
                $scope.avatarUrl = function(entity){
                    if(entity['@files:avatar.avatarSmall'])
                        return entity['@files:avatar.avatarSmall'].url;
                    else
                        return MapasCulturais.defaultAvatarURL;
                };
                
                $scope.find = function(){
                    if(timeouts.find)
                        $timeout.cancel(timeouts.find);
                    
                    var s = $scope.searchText.trim().replace(' ', '*');
                    
                    timeouts.find = $timeout(function(){
                        $scope.spinnerCondition = true;
                        FindService.find($scope.entity, { name: 'ILIKE(*' + s + '*)' }, function(data,status){
                            $scope.processResult(data, status); 
                            $scope.spinnerCondition = false;
                        });
                    },500);
                };
                
                $scope.processResult = function(data, status){
                    if(angular.isFunction($scope.filter))
                        data = $scope.filter(data, status);
                    
                    if(data.length === 0){
                        $scope.noEntityFound = true;
                        
                        $timeout(function(){
                            $scope.noEntityFound = false;
                        },3000);
                    }
                    
                    $scope.result = data;
                };
            }
        };
    }]);

    app.directive('editBox', function() {
        return {
            restrict: 'E',
            templateUrl: MapasCulturais.assetURL + '/js/directives/edit-box.html',
            transclude: true,
            
            scope: {
                spinnerCondition: '=',
                onSubmit: '=',
                onCancel: '='
            },
            
            link: function($scope, el, attrs) {
                $scope.args = attrs;
                
                $scope.spinnerUrl = MapasCulturais.assetURL + '/img/spinner.gif';
                
                $scope.classes = {
                    'mc-bottom': attrs.position === 'bottom' || !attrs.position, 
                    'mc-top': attrs.position === 'top', 
                    'mc-left': attrs.position === 'left', 
                    'mc-right': attrs.position === 'right'
                };
                
                $scope.submit = function(){
                    if(angular.isFunction($scope.onSubmit)){
                        $scope.onSubmit();
                    }
                };
                
                $scope.cancel = function(){
                    if(angular.isFunction($scope.onCancel)){
                        $scope.onCancel();
                    }
                };
            }
        }
    });


})(angular);