(function (angular) {
    "use strict";
    var module = angular.module('ng.support', []);

    module.config(['$httpProvider', function ($httpProvider) {
        $httpProvider.defaults.headers.put['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
        $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
        $httpProvider.defaults.transformRequest = function (data) {
            var result = angular.isObject(data) && String(data) !== '[object File]' ? $.param(data) : data;
            return result;
        };
    }]);

    module.controller('SupportModal', ['$scope', 'SupportService', '$window', function ($scope, SupportService, $window) {
        $scope.data = {
            openModal: false,
            userPermissions: {},
            defaultAvatar: MapasCulturais.assets.avatarAgent,
            fields: MapasCulturais.entity.registrationFieldConfigurations.concat(MapasCulturais.entity.registrationFileConfigurations),
        };

        $scope.data.fields.sort(function (a, b) {            
            return a.displayOrder - b.displayOrder;
        });

        $scope.data.fields.map(function(item){
            if(item.fieldType == "file"){
                item.ref = item.groupName;
            }else{
                item.ref = item.fieldName;
            }
        });
        
        $scope.savePermission = function (agentId) {
            SupportService.savePermission(MapasCulturais.entity.id, agentId, $scope.data.userPermissions).success(function (data, status, headers) {
                MapasCulturais.Messages.success('Permissoes salvas com sucesso.');
            });
        }

        // script para remover o scroll do body quando o modal está aberto
        var modal = document.querySelectorAll('.bg-support-modal');
        modal.forEach((each)=>{
            var contains = each.classList.contains('open');
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
            observer.observe(each, { attributes: true });
        });

        

    }]);

    module.controller('Support', ['$scope', 'SupportService', '$window', function ($scope, SupportService, $window) {
        $scope.data = {
            agents: []
        };

        SupportService.findAgentsRelation(MapasCulturais.entity.id).success(function (data, status, headers) {
            $scope.data.agents = data;
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
            findAgentsRelation: function (opportunityId, data) {

                var url = MapasCulturais.createUrl('support', 'getAgentsRelation', {opportunityId});

                return $http.get(url, data).
                    success(function (data, status, headers) {
                        $rootScope.$emit('registration.create', { message: "Reports found", data: data, status: status });
                    }).
                    error(function (data, status) {
                        $rootScope.$emit('error', { message: "Reports not found for this opportunity", data: data, status: status });
                    });
            },
            savePermission: function (opportunityId, agentId, data) {

                var url = MapasCulturais.createUrl('support', 'opportunityPermissions', {opportunityId, agentId});

                return $http.put(url, data).
                    success(function (data, status, headers) {
                        $rootScope.$emit('registration.create', { message: "Reports found", data: data, status: status });
                    }).
                    error(function (data, status) {
                        $rootScope.$emit('error', { message: "Reports not found for this opportunity", data: data, status: status });
                    });
            }
        };
    }]);

})(angular);
