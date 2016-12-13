(function(angular){
    "use strict";

    var module = angular.module('entity.module.relatedAgents', ['ngSanitize']);
    
    var labels = MapasCulturais.gettext.relatedAgents;

    module.config(['$httpProvider',function($httpProvider){
        $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
        $httpProvider.defaults.transformRequest = function(data) {
            var result = angular.isObject(data) && String(data) !== '[object File]' ? $.param(data) : data;

            return result;
        };
    }]);

    module.factory('RelatedAgentsService', ['$http', '$rootScope', function($http, $rootScope){
        var controllerId = null,
            entityId = null,
            baseUrl = MapasCulturais.baseURL.substr(-1) === '/' ?  MapasCulturais.baseURL : MapasCulturais.baseURL + '/';

        try{ controllerId = MapasCulturais.request.controller; }catch (e){};
        try{ entityId = MapasCulturais.entity.id; }catch (e){};

        return {
            controllerId: controllerId,

            entityId: entityId,

            getUrl: function(action){
                return baseUrl + controllerId + '/' + action + '/' + entityId;
            },

            create: function(group, agentId, hasControl){
                return $http.post(this.getUrl('createAgentRelation'), {group: group, agentId: agentId, has_control: hasControl }).
                        success(function(data, status){
                            if(status === 202){
                                MapasCulturais.Messages.alert(labels['requestSent'].replace('{{agent}}', '<strong>'+data.agent.name+'</strong>'));
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

    module.controller('RelatedAgentsController', ['$scope', '$rootScope', 'RelatedAgentsService', 'EditBox', function($scope, $rootScope, RelatedAgentsService, EditBox) {
        $scope.editbox = EditBox;

        $scope.groups = [];

        $scope.admins = MapasCulturais.entity.agentAdminRelations;

        for(var i in MapasCulturais.entity.agentRelations)
            if(i != 'group-admin')
                $scope.groups.push({name: i, relations: MapasCulturais.entity.agentRelations[i]});

        $scope.showCreateDialog = {};

        $scope.spinners = {};

        $scope.isEditable = MapasCulturais.isEditable;
        $scope.canChangeControl = MapasCulturais.entity.canUserCreateRelatedAgentsWithControl;

        $scope.data = {};

        $scope.agentRelationDisabledCD = MapasCulturais.agentRelationDisabledCD || [];

        $scope.disabledCD = function(groupName){
            return $scope.agentRelationDisabledCD.indexOf(groupName) >= 0;
        };


        function getGroup(groupName){
            var result = null;
            $scope.groups.forEach(function(group){
                if(group.name === groupName)
                    result = group;
            });

            return result;
        }

        function groupExists(groupName){
            if(getGroup(groupName))
                return true;
            else
                return false;
        }

        $scope.avatarUrl = function(entity){
            if(entity.avatar.avatarSmall)
                return entity.avatar.avatarSmall.url;
            else
                return MapasCulturais.defaultAvatarURL;
        };

        $scope.closeNewGroupEditBox = function(){
            EditBox.close('new-related-agent-group');
        };

        $scope.data.newGroupName = '';

        $scope.getCreateAgentRelationEditBoxId = function(groupName){
            return 'add-related-agent-' + groupName.replace(/[^a-z0-9_]/gi,'');
        };

        $scope.createGroup = function(){
            if($scope.data.newGroupName.trim() && !groupExists( $scope.data.newGroupName && $scope.data.newGroupName.toLowerCase().trim() !== 'registration') ){
                var newGroup = {name: $scope.data.newGroupName, relations: []};

                $scope.groups = [newGroup].concat($scope.groups);

                $scope.data.newGroupName = '';
                EditBox.close('new-related-agent-group');
            }
        };

        $scope.createRelation = function(entity){
            var _scope = this.$parent;
            var groupName = _scope.attrs.group;

            RelatedAgentsService.create(groupName, entity.id).
                    success(function(data){
                        var group = getGroup(groupName);
                        group.relations.push(data);
                        $scope.showCreateDialog[groupName] = false;
                        _scope.$parent.searchText = '';
                        _scope.$parent.result = [];
                        EditBox.close($scope.getCreateAgentRelationEditBoxId(groupName));
                    });
        };

        $scope.deleteRelation = function(relation){
            var group = getGroup(relation.group);
            var oldRelations = group.relations.slice();
            var i = group.relations.indexOf(relation);

            group.relations.splice(i,1);

            RelatedAgentsService.remove(relation.group, relation.agent.id).
                    error(function(){
                        group.relations = oldRelations;
                    });
        };

        $scope.createAdminRelation = function(entity){
            var _scope = this.$parent;
            var groupName = 'group-admin';
            var hasControl = true;

            RelatedAgentsService.create(groupName, entity.id, true).
                    success(function(data){
                        $scope.admins.push(data);
                        _scope.$parent.searchText = '';
                        _scope.$parent.result = [];
                        EditBox.close('add-related-agent');
                    });
        };

        $scope.deleteAdminRelation = function(admin){
            var admins = $scope.admins;
            var oldRelations = admins.slice();
            var i = admins.indexOf(admin);

            admins.splice(i,1);

            RelatedAgentsService.remove('group-admin', admin.id).
                    error(function(){
                        $scope.admins = oldRelations;
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

        $scope.filterResult = function( data, status ){
            var group = getGroup( this.attrs.group );

            if(group && group.relations.length > 0){
                var ids = group.relations.map( function( el ){ return el.agent.id; } );

                data = data.filter( function( e ){
                    if( ids.indexOf( e.id ) === -1 )
                        return e;
                } );
            }
            return data;
        };
    }]);
})(angular);
