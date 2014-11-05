(function (angular) {
    "use strict";

    var module = angular.module('Project', ['ngSanitize']);

    module.config(['$httpProvider', function ($httpProvider) {
            $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
            $httpProvider.defaults.transformRequest = function (data) {
                var result = angular.isObject(data) && String(data) !== '[object File]' ? $.param(data) : data;

                return result;
            };
        }]);

    module.factory('ProjectService', ['$http', '$rootScope', function ($http, $rootScope) {
            return {
                serviceProperty: null,
                getUrl: function(){
                    return MapasCulturais.baseURL // + controllerId  + '/' + actionName 
                },
                doSomething: function (param) {
                    var data = {
                        prop: name
                    };
                    return $http.post(this.getUrl(), data).
                            success(function (data, status) {
                                $rootScope.$emit('something', {message: "Something was done", data: data, status: status});
                            }).
                            error(function (data, status) {
                                $rootScope.$emit('error', {message: "Cannot do something", data: data, status: status});
                            });
                }
            };
        }]);

    module.controller('ProjectController', ['$scope', '$rootScope', '$timeout', 'ProjectService', 'EditBox', function ($scope, $rootScope, $timeout, ProjectService, EditBox) {
            var adjustingBoxPosition = false;
            $scope.editbox = EditBox;
            $scope.data = {
                spinner: false,
                apiQueryRegistrationAgent: {
                    '@permissions': '@control',
                    'type': 'EQ(1)' // type individual
                },
                registrationCategories: [
                    {value: 'Categoria 1', label: 'Categoria 1'},
                    {value: 'Categoria 2', label: 'Categoria 2'},
                    {value: 'Categoria 3', label: 'Categoria 3'}
                ],
                registration: {
                    owner: null,
                    category: null
                }
            };
            
            var adjustBoxPosition = function () {
                setTimeout(function () {
                    adjustingBoxPosition = true;
                    $('#select-registration-owner-button').click();
                    adjustingBoxPosition = false;
                });
            };

            $rootScope.$on('repeatDone:findEntity:find-entity-registration-owner', adjustBoxPosition);

            $scope.$watch('data.spinner', function (ov, nv) {
                if (ov && !nv)
                    adjustBoxPosition();
            });
            
            $scope.setRegistrationOwner = function(entity){
                $scope.data.registration.owner = entity;
                EditBox.close('editbox-select-registration-owner');
            };

            $('#editbox-select-registration-owner').on('open', function () {
                if (!adjustingBoxPosition)
                    $('#find-entity-registration-owner').trigger('find');
            });
            
            

        }]);
})(angular);