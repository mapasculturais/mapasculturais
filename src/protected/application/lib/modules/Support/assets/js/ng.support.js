(function (angular) {
    "use strict";
    var module = angular.module('ng.support', []);
    
    module.config(['$httpProvider', function ($httpProvider) {
        $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
        $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
        $httpProvider.defaults.transformRequest = function (data) {
            var result = angular.isObject(data) && String(data) !== '[object File]' ? $.param(data) : data;
            return result;
        };
    }]);

    module.controller('SupportModal',['$scope', 'SupportService','$window', function($scope, SupportService, $window){        
        $scope.data = {
            openModal: false,
            userPermissions: {},
            fields: MapasCulturais.entity.registrationFieldConfigurations,
        };

        $scope.agents = {};

        SupportService.findAgentsRelation().success(function (data, status, headers){
           $scope.data.agents = data
        });

        $scope.savePermission = function(field){

        }
        
    }]);

    module.controller('Support',['$scope', 'SupportService','$window', function($scope, SupportService, $window){        
        $scope.data = {
            agents: [],
        };

        SupportService.findAgentsRelation().success(function (data, status, headers){
           $scope.data.agents = data
        });
    }]);
    
    module.factory('SupportService', ['$http', '$rootScope', 'UrlService', function ($http, $rootScope, UrlService) {  
       return {
            findAgentsRelation: function (data) {
                
                var url = MapasCulturais.createUrl('support', 'getAgentsRelation', {opportunity_id: MapasCulturais.entity.id});

                return $http.get(url, data).
                success(function (data, status, headers) {
                    $rootScope.$emit('registration.create', {message: "Reports found", data: data, status: status});
                }).
                error(function (data, status) {
                    $rootScope.$emit('error', {message: "Reports not found for this opportunity", data: data, status: status});
                });
            },
            setPermissonFields: function (data) {
                
                var url = MapasCulturais.createUrl('support', 'setPermissonFields', {opportunity_id: MapasCulturais.entity.id});

                return $http.patch(url, data).
                success(function (data, status, headers) {
                    $rootScope.$emit('registration.create', {message: "Reports found", data: data, status: status});
                }).
                error(function (data, status) {
                    $rootScope.$emit('error', {message: "Reports not found for this opportunity", data: data, status: status});
                });
            }
       };
    }]);

})(angular);
