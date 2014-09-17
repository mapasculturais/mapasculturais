(function(angular){
    "use strict";

    var module = angular.module('ChangeOwner', ['ngSanitize']);

    module.config(['$httpProvider',function($httpProvider){
        $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
        $httpProvider.defaults.transformRequest = function(data) {
            var result = angular.isObject(data) && String(data) !== '[object File]' ? $.param(data) : data;

            return result;
        };
    }]);

    module.factory('ChangeOwnerService', ['$http', '$rootScope', function($http, $rootScope){
        var controllerId = null,
            entityId = null,
            baseUrl = MapasCulturais.baseURL.substr(-1) === '/' ?  MapasCulturais.baseURL : MapasCulturais.baseURL + '/';

        try{ controllerId = MapasCulturais.request.controller; }catch (e){};
        try{ entityId = MapasCulturais.request.id; }catch (e){};

        return {
            controllerId: controllerId,

            entityId: entityId,

            getUrl: function(action){
                return baseUrl + controllerId + '/' + action + '/' + entityId;
            },

            create: function(group, agentId){
                return $http.post(this.getUrl('createAgentRelation'), {group: group, agentId: agentId}).
                        success(function(data, status){
                            if(status === 202){
                                MapasCulturais.Messages.alert('Sua requisição para relacionar o agente <strong>' + data.agent.name + '</strong> foi enviada.');
                            }
                            $rootScope.$emit('relatedAgent.created', data);
                        }).
                        error(function(data, status){
                            $rootScope.$emit('error', { message: "Cannot create related agent", data: data, status: status });
                        });
            },

            remove: function(group, agentId){
                return $http.post(this.getUrl('removeAgentRelation'), {group: group, agentId: agentId}).
                    success(function(data, status){
                        $rootScope.$emit('relatedAgent.removed', data);
                    }).
                    error(function(data, status){
                        $rootScope.$emit('error', { message: "Cannot remove related agent", data: data, status: status });
                    });
            },

            giveControl: function(agentId){
                return this.setControl(agentId, true);
            },

            removeControl: function(agentId){
                return this.setControl(agentId, false);
            },

            setControl: function(agentId, hasControl){
                return $http({
                    method: 'POST',
                    url: this.getUrl('setRelatedAgentControl'),
                    data: {agentId: agentId, hasControl: hasControl}
                }).success(function(data, status){
                    $rootScope.$emit(hasControl ? 'relatedAgent.controlGiven' : 'relatedAgent.controlRemoved', data);
                }).error(function(data, status){
                    $rootScope.$emit('error', {
                        message: hasControl ? "Cannot give control to related agent" : "Cannot remove control of related agent",
                        data: data,
                        status: status
                    });
                });
            }
        };
    }]);

    module.controller('ChangeOwnerController', ['$scope', '$rootScope', 'ChangeOwnerService', 'EditBox', function($scope, $rootScope, ChangeOwnerService, EditBox) {
        $scope.editbox = EditBox;
        
        $scope.spinner = false;

        $scope.requestEntity = function(e){
            console.log(e);
        };
    }]);
})(angular);