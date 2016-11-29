(function(angular){
    "use strict";

    var module = angular.module('entity.module.changeOwner', ['ngSanitize']);
    
    var labels = MapasCulturais.gettext.changeOwner;

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
        try{ entityId = MapasCulturais.entity.id; }catch (e){};

        return {
            controllerId: controllerId,

            entityId: entityId,

            getUrl: function(){
                return baseUrl + controllerId + '/changeOwner/' + entityId;
            },


        setOwnerTo: function(agentId){
                return $http.post(this.getUrl(), {ownerId: agentId}).
                    success(function(data, status){
                        $rootScope.$emit('changedOwner', { message: labels['ownerChanged'], data: data, status: status });
                    }).
                    error(function(data, status){
                        $rootScope.$emit('error', { message: labels['cannotChangeOwner'], data: data, status: status });
                    });
            }
        };
    }]);

    module.controller('ChangeOwnerController', ['$scope', '$rootScope', '$timeout', 'ChangeOwnerService', 'EditBox', function($scope, $rootScope, $timeout, ChangeOwnerService, EditBox) {
        var adjustingBoxPosition = false;
        $scope.editbox = EditBox;
        $scope.data = {
            spinner: false,
            apiQuery: { }
        };
        
        if(!MapasCulturais.entity.userHasControl){
            $scope.data.apiQuery['@permissions'] = '@control';
        }
        
        var adjustBoxPosition = function(){
            setTimeout(function(){
                adjustingBoxPosition = true;
                $('#change-owner-button').click();
                adjustingBoxPosition = false;
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
                    var requestMessage = labels['requestMessage'];
                    requestMessage.replace('{{type}}', MapasCulturais.entity.getTypeName());
                    requestMessage.replace('{{recipient}}', '<strong>'+e.name+'</strong>');
                    MapasCulturais.Messages.alert(requestMessage);
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

        $('#editbox-change-owner').on('open', function(){
            if(!adjustingBoxPosition)
                $('#find-entity-change-owner').trigger('find');
        });

    }]);
})(angular);
