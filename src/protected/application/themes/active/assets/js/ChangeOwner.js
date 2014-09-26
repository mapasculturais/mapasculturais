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

            getUrl: function(){
                return baseUrl + controllerId + '/changeOwner/' + entityId;
            },

            setOwnerTo: function(agentId){
                return $http.post(this.getUrl(), {ownerId: agentId}).
                    success(function(data, status){
                        $rootScope.$emit('changedOwner', { message: "The entity owner was changed", data: data, status: status });
                    }).
                    error(function(data, status){
                        $rootScope.$emit('error', { message: "Cannot change the owner", data: data, status: status });
                    });
            }
        };
    }]);

    module.controller('ChangeOwnerController', ['$scope', '$rootScope', '$timeout', 'ChangeOwnerService', 'EditBox', function($scope, $rootScope, $timeout, ChangeOwnerService, EditBox) {
        $scope.editbox = EditBox;
        $scope.data = {
            spinner: false,
            apiQuery: {
                '@permissions': MapasCulturais.entity.userHasControl ? '!@control' : '@control'
            }
        };

        var adjustBoxPosition = function(){
            setTimeout(function(){
                $('#change-owner-button').click();
            });
        };

        $rootScope.$on('repeatDone:findEntity:find-entity-change-owner', adjustBoxPosition);
        $scope.$watch('data.spinner', function(ov, nv){
            if(ov && !nv)
                adjustBoxPosition();
        });

        $scope.requestEntity = function(e){
            ChangeOwnerService.setOwnerTo(e.id).success(function(data, status){
                if(status === 202){
                    MapasCulturais.Messages.alert('Sua requisição foi para mudança de propriedade deste ' + MapasCulturais.entity.getTypeName() + ' para o agente <strong>'+e.name+'</strong> foi enviada.');
                }else{
                    $('.js-owner-name').html('<a href="' + e.singleUrl + '">' + e.name + '</a>');
                    $('.js-owner-description').html(e.shortDescription);
                    try{
                        $('.js-owner-avatar').attr('src', e['@files:avatar.avatarSmall'].url);
                    }catch(e){
                        $('.js-owner-avatar').attr('src', MapasCulturais.defaultAvatarURL);
                    }
                }
            });

            EditBox.close('editbox-change-owner');
        };
    }]);
})(angular);