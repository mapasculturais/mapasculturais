(function(angular){
    "use strict";

    var module = angular.module('entity.module.subsiteAdmins', ['ngSanitize']);

    module.config(['$httpProvider',function($httpProvider){
        $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
        $httpProvider.defaults.transformRequest = function(data) {
            var result = angular.isObject(data) && String(data) !== '[object File]' ? $.param(data) : data;

            return result;
        };
    }]);

    module.factory('SubsiteAdminsService', ['$http', '$rootScope', function($http, $rootScope){
        var entityId = null;

        try{ entityId = MapasCulturais.entity.id; }catch (e){};

        return {
            entityId: entityId,

            getUrl: function(action, agentId){
                return MapasCulturais.createUrl('agent', action, [agentId]);
            },

            create: function(agentId,role){
                return $http.post(this.getUrl('addRole', agentId), { subsiteId: entityId, role: role }).
                        success(function(data, status){
                            $rootScope.$emit('subsiteAdmins.created', data);
                        }).
                        error(function(data, status){
                            $rootScope.$emit('error', { message: "Cannot create administrator for the user taken", data: data, status: status });
                        });
            },

            remove: function(agentId,role){
                var url = this.getUrl('removeRole', agentId);
                return $http.post(url, {subsiteId: entityId, role: role}).
                    success(function(data, status){
                        $rootScope.$emit('subsiteAdmins.removed', data);
                    }).
                    error(function(data, status){
                        $rootScope.$emit('error', { message: "Cannot remove administrator role", data: data, status: status });
                    });
            }
        };
    }]);

    module.controller('SubsiteAdminsController', ['$scope', '$rootScope', 'SubsiteAdminsService', 'EditBox', function($scope, $rootScope, SubsiteAdminsService, EditBox) {
        $scope.editbox = EditBox;

        $scope.superAdmins = MapasCulturais.entity.superAdmins;
        $scope.admins = MapasCulturais.entity.admins;

        $scope.showCreateDialog = {};

        $scope.spinners = {};

        $scope.data = {};

        $scope.createSuperAdminRole = function(entity){
            var _scope = this.$parent;
            SubsiteAdminsService.create(entity.id,'superAdmin').
                success(function(data){
                    $scope.superAdmins.push({profile:entity});

                    _scope.$parent.searchText = '';
                    _scope.$parent.result = [];
                    EditBox.close('add-super-admin');
                });
        };
        $scope.createAdminRole = function(entity){
            var _scope = this.$parent;
            SubsiteAdminsService.create(entity.id,'admin').
                success(function(data){
                    $scope.admins.push({profile:entity});
                    _scope.$parent.searchText = '';
                    _scope.$parent.result = [];
                    EditBox.close('add-admin');
                });
        };

        $scope.deleteSuperAdmin = function(admin){
            SubsiteAdminsService.remove(admin.profile.id,'superAdmin').success(function(){
                var i = $scope.superAdmins.indexOf(admin);

                $scope.superAdmins.splice(i,1);
            });
        };

        $scope.deleteAdmin = function(admin){
            SubsiteAdminsService.remove(admin.profile.id,'admin').success(function(){
                var i = $scope.admins.indexOf(admin);

                $scope.admins.splice(i,1);
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

        $scope.avatarUrl = function(entity){
            if(entity['@files:avatar.avatarSmall'])
                return entity['@files:avatar.avatarSmall'].url;
            else if(entity.avatar && entity.avatar.avatarSmall)
                return entity.avatar.avatarSmall.url;
            else
                return MapasCulturais.defaultAvatarURL;
        };
    }]);
})(angular);
