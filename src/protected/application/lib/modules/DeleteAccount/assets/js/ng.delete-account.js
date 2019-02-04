(function (angular) {
    "use strict";

    var module = angular.module('DeleteAccount', [
        'mc.directive.editBox',
        'mc.module.findEntity', 
        'ngSanitize'
    ]);

    module.config(['$httpProvider', function ($httpProvider) {
            $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
            $httpProvider.defaults.transformRequest = function (data) {
                var result = angular.isObject(data) && String(data) !== '[object File]' ? $.param(data) : data;

                return result;
            };
        }]);

    module.factory('DeleteAccountService', ['$http', '$rootScope', function ($http, $rootScope) {
            return {
                serviceProperty: null,
                getUrl: function(token){
                    return MapasCulturais.createUrl('user', 'deleteAccount', {'token': token})
                },
                deleteAccount: function (token, agentId) {
                    return $http.post(this.getUrl(token),{agentId: agentId}).
                            success(function (data, status) {
                                $rootScope.$emit('accountDeleted', {message: "The account was deleted", data: data, status: status});
                            }).
                            error(function (data, status) {
                                $rootScope.$emit('error', {message: "The account could not be deleted", data: data, status: status});
                            });
                }
            };
        }]);

    module.controller('DeleteAccountController', ['$scope', '$rootScope', '$timeout', 'DeleteAccountService', 'EditBox', function ($scope, $rootScope, $timeout, DeleteAccountService, EditBox) {
            var adjustingBoxPosition = false;
            var editboxId = 'delete-account--edit-box';
            
            $scope.editbox = EditBox;
            $scope.data = {
                spinner: false,
                apiQuery: {
                    user: '!EQ(' + MapasCulturais.userId + ')',
                    parent: 'NULL()'
                },
                selectedAgent: null
            };

            $scope.adjustBoxPosition = function () {
                if(!$scope.editbox.openEditboxes[editboxId]){
                    return;
                }
                setTimeout(function () {
                    adjustingBoxPosition = true;
                    EditBox.fixPosition(editboxId);
                    adjustingBoxPosition = false;
                });
            };

            $scope.$watch('data.spinner', function (ov, nv) {
                if (ov && !nv)
                    $scope.adjustBoxPosition();
            });

            $scope.selectedAgent = function(e){
                $scope.data.selectedAgent = e;
                EditBox.close(editboxId);
            };

            $scope.deleteAccount = function(token){
                var labels = MapasCulturais.gettext['delete-account'];
                if(confirm(labels.confirm)){
                    DeleteAccountService.deleteAccount(token, $scope.data.selectedAgent ? $scope.data.selectedAgent.id : null);
                    setTimeout(function(){
                        alert(labels.goodbye);

                        window.location = MapasCulturais.createUrl('auth', 'logout');
                    },1000);
                }
            };

        }]);
})(angular);