(function(angular){
    "use strict";
    
    var module = angular.module('RelatedAgents', [], ['$httpProvider', function($httpProvider){
        $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
        $httpProvider.defaults.transformRequest = [function(data) {
            return angular.isObject(data) && String(data) !== '[object File]' ? $.param(data) : data;
        }];
    }]);
    
    
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
                return $http.post(this.getUrl('createAgentRelation'), {group: group, agentId: agentId}).
                        success(function(data, status){
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
                    url: this.getUrl('setRelatedAgentControl')
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
    
    module.controller('RelatedAgentsController', ['$scope', '$rootScope', 'RelatedAgentsService', function($scope, $rootScope, RelatedAgentsService) {
        $scope.groups = MapasCulturais.entity.agentRelations;
        
        $scope.showCreateDialog = {};
        
        $scope.isEditable = MapasCulturais.isEditable;
        
        $scope.avatarUrl = function(entity){
//            console.log(entity);
            if(entity.avatar.avatarSmall)
                return entity.avatar.avatarSmall.url;
            else
                return MapasCulturais.defaultAvatarURL;
        };
        
        $scope.createRelation = function(entity){
            var group = this.$parent.attrs.group;
            
            RelatedAgentsService.create(group, entity.id).
                    success(function(data){
                        console.log(data);
                        $scope.groups[group].push(data);
                    });
        };
        
        $scope.deleteRelation = function(relation){
            var i = $scope.groups[relation.group].indexOf(relation),
                oldGroups = $scope.groups[relation.group].slice();
            $scope.groups[relation.group].splice(i,1);
            
            RelatedAgentsService.remove(relation.group, relation.agent.id).
                    error(function(){
                        $scope.groups[relation.group] = oldGroups;
                    });
        };
        
        $scope.toggleControl = function(relation){
            relation.hasControl = !relation.hasControl;
            
            if(relation.hasControl){
                RelatedAgentsService.giveControl(relation.agent.id).
                        error(function(){
                            relation.hasControl = false;
                        });
            }else{
                RelatedAgentsService.removeControl(relation.agent.id).
                        error(function(){
                            relation.hasControl = true;
                        });
            }
        };
        
        $scope.filterResult = function(data, status){
            var group = this.attrs.group;
            var ids = $scope.groups[group].map(function(e){return e.agent.id});
            data = data.filter(function(e){ 
                if(ids.indexOf(e.id) === -1) 
                    return e;
            });
            return data;
        };
    }]);
})(angular);