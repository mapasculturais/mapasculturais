(function(angular){
    "use strict";
    
    var module = angular.module('RelatedAgents', []);
    
    module.factory('RelatedAgentsService', ['$http', '$rootScope', function($http, $rootScope){
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
                $http.post(this.getUrl('createAgentRelation'), {group: group, agentId: agentId}).
                        success(function(data, status){
                            $rootScope.$emit('relatedAgent.created', data);
                        }).
                        error(function(data, status){
                            $rootScope.$emit('error', { message: "Cannot create related agent", data: data, status: status });
                        });
            },
            
            remove: function(group, agentId){
                $http.post(this.getUrl('removeAgentRelation'), {group: group, agentId: agentId}).
                        success(function(data, status){
                            $rootScope.$emit('relatedAgent.removed', data);
                        }).
                        error(function(data, status){
                            $rootScope.$emit('error', { message: "Cannot remove related agent", data: data, status: status });
                        });
            },
            
            giveControl: function(agentId){
                $http.post(this.getUrl('setRelatedAgentControl'), {group: group, agentId: agentId}).
                        success(function(data, status){
                            $rootScope.$emit('relatedAgent.controlGiven', data);
                        }).
                        error(function(data, status){
                            $rootScope.$emit('error', { message: "Cannot give control to related agent", data: data, status: status });
                        });
            },
            
            removeControl: function(agentId){
                $http.post(this.getUrl('removeAgentRelation'), {group: group, agentId: agentId}).
                        success(function(data, status){
                            $rootScope.$emit('relatedAgent.controlRemoved', data);
                        }).
                        error(function(data, status){
                            $rootScope.$emit('error', { message: "Cannot remove control of related agent", data: data, status: status });
                        });
            }
        };
    }]);
    
    module.controller('RelatedAgentsController', ['$scope', '$rootScope', 'RelatedAgentsService', function($scope, $rootScope, RelatedAgentsService) {
        $scope.groups = MapasCulturais.entity.agentRelations;
        
        $scope.showCreateDialog = {};
        
        $scope.isEditable = MapasCulturais.isEditable;
        
        $scope.avatarUrl = function(entity){
            if(entity.avatar.avatarSmall)
                return entity.avatar.avatarSmall.url;
            else
                return MapasCulturais.defaultAvatarURL;
        };
            
        $rootScope.$on('relatedAgent.created', function(data){
            console.log('relatedAgent.created',data);
        });
        
        $rootScope.$on('relatedAgent.removed', function(data){
            console.log('relatedAgent.removed',data);
        });
        
        $rootScope.$on('relatedAgent.controlGiven', function(data){
            console.log('relatedAgent.controlGiven',data);
        });
        
        $rootScope.$on('relatedAgent.controlRemoved', function(data){
            console.log('relatedAgent.controlRemoved',data);
        });
        
        $scope.deleteRelation = function(group, i){
            delete $scope.groups[group][i];
            $scope.groups[group].splice(i,1);
        };
        
        $scope.filterResult = function(data){
            
        }
    }]);
})(angular);