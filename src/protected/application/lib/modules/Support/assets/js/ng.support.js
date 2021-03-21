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

        // script para remover o scroll do body quando o modal está aberto
        var modal = document.querySelector('.bg-support-modal');
        var contains = modal.classList.contains('open');
        var body = document.querySelector('body');
        var observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                if (mutation.attributeName == "class") {
                    var currentClass = mutation.target.classList.contains('open');
                    if (contains !== currentClass) {
                        contains = currentClass;
                        if (currentClass) {
                            body.classList.add('modal-oppened');
                        } else {
                            body.classList.remove('modal-oppened');
                        }
                    }
                }
            });
        });
        observer.observe(modal, { attributes: true });
        
    }]);

    module.controller('Support',['$scope', 'SupportService','$window', function($scope, SupportService, $window){        
        $scope.data = {
            agents: [],
        };

        SupportService.findAgentsRelation().success(function (data, status, headers){
           $scope.data.agents = data
        });
    }]);

    module.controller('SupportForm',['$scope', 'SupportService','$window', function($scope, SupportService, $window){        
        $scope.userAllowedFields = MapasCulturais.userAllowedFields
        
        $scope.canUserEdit = function(field){
            if (field.fieldType == 'file'){
                if (MapasCulturais.userAllowedFields[field.groupName] == 'rw'){
                    return true
                }
                return false
            }
            if (MapasCulturais.userAllowedFields[field.fieldName] == 'rw'){
                return true
            }
        return false

        };    
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
