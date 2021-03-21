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

    }]);

    module.controller('Support', ['$scope', 'SupportService', '$window', function ($scope, SupportService, $window) {
        $scope.data = {
            agents: []
        };

        SupportService.findAgentsRelation(MapasCulturais.entity.id).success(function (data, status, headers) {
            $scope.data.agents = data;
        });
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
