(function(angular){
    "use strict";

    var module = angular.module('entity.module.relatedProfileAgents', ['ngSanitize']);

    module.config(['$httpProvider',function($httpProvider){
        $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
        $httpProvider.defaults.transformRequest = function(data) {
            var result = angular.isObject(data) && String(data) !== '[object File]' ? $.param(data) : data;

            return result;
        };
    }]);

    module.factory('RelatedProfileAgentsService', ['$http', '$rootScope', function($http, $rootScope){
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

            create: function(agentId,role){
                return $http.post(this.getUrl('createAdminRole'), { agentId: agentId, role: role }).
                        success(function(data, status){
                            if(status === 202){
                                MapasCulturais.Messages.alert('Sua requisição para tornar o agente administrador do subsite corrente foi enviada.');
                            }
                            $rootScope.$emit('relatedProfileAgents.created', data);
                        }).
                        error(function(data, status){
                            $rootScope.$emit('error', { message: "Cannot create administrator for the user taken", data: data, status: status });
                        });
            },

            remove: function(agentId,role){
                return $http.post(this.getUrl('deleteAdminRelation'), {agentId: agentId, role: role}).
                    success(function(data, status){
                        $rootScope.$emit('relatedProfileAgents.removed', data);
                    }).
                    error(function(data, status){
                        $rootScope.$emit('error', { message: "Cannot remove administrator role", data: data, status: status });
                    });
            }
        };
    }]);

    module.controller('RelatedProfileAgentsController', ['$scope', '$rootScope', 'RelatedProfileAgentsService', 'EditBox', function($scope, $rootScope, RelatedAgentsService, EditBox) {
        $scope.editbox = EditBox;

        $scope.groups = [];

        $scope.profiles = MapasCulturais.entity.agentProfileRelations;

        for(var i in MapasCulturais.entity.agentProfileRelations)
            $scope.groups.push(MapasCulturais.entity.agentProfileRelations[i]);

        $scope.showCreateDialog = {};

        $scope.spinners = {};

        $scope.data = {};

        $scope.createAdminRole = function(entity){
            var _scope = this.$parent;
            RelatedAgentsService.create(entity.id,'saasSuperAdmin').
                success(function(data){
                    _scope.$parent.searchText = '';
                    _scope.$parent.result = [];
                    EditBox.close('add-related-agent');
                });
        };

        $scope.deleteAdminRelation = function(agentId){

            RelatedAgentsService.remove(agentId,'saasSuperAdmin').
                    error(function(){
                    });
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
