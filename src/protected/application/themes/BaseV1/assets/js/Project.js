(function (angular) {
    "use strict";

    var module = angular.module('Project', ['ngSanitize']);

    module.config(['$httpProvider', function ($httpProvider) {
            $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
            $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
            $httpProvider.defaults.transformRequest = function (data) {
                var result = angular.isObject(data) && String(data) !== '[object File]' ? $.param(data) : data;

                return result;
            };
        }]);

    module.factory('ProjectService', ['$http', '$rootScope', function ($http, $rootScope) {
            return {
                serviceProperty: null,
                getRegistrationUrl: function(){
                    return MapasCulturais.baseURL + 'registration';
                },
                register: function (params) {
                    var data = {
                        projectId: MapasCulturais.entity.id,
                        ownerId: params.owner.id,
                        category: params.category.value
                    };
                    return $http.post(this.getRegistrationUrl(), data).
                            success(function (data, status) {
                                $rootScope.$emit('something', {message: "Project registration was created", data: data, status: status});
                            }).
                            error(function (data, status) {
                                $rootScope.$emit('error', {message: "Cannot create project registration", data: data, status: status});
                            });
                }
            };
        }]);

    module.factory('RegistrationFileConfigurationService', ['$http', function($http){
        return {
            getUrl: function(){MapasCulturais.baseURL + 'registrationfileconfiguration'},
            create: function(data){
                $http.post(url+'/create', data)
                    .success(
                        function(){

                        }
                    );
            },
            edit: function(){

            },
            delete: function(){

            }
        };

    }]);
    module.controller('RegistrationFileConfigurationsController', ['$scope', '$rootScope', '$timeout', 'RegistrationFileConfigurationService', 'EditBox', function ($scope, $rootScope, $timeout, ProjectService, EditBox) {
        $scope.isEditable = MapasCulturais.isEditable;
        $scope.uploadFileGroup = 'registrationFileConfiguration';
        $scope.getUploadUrl = function (ownerId){
            return RegistrationFileConfigurationService.getUrl()+'/upload/'+ownerId;
        };
        $scope.data = {
            files: MapasCulturais.entity.registrationFileConfigurations
        };
        console.log($scope.data.files);
    }]);

    module.controller('ProjectController', ['$scope', '$rootScope', '$timeout', 'ProjectService', 'EditBox', function ($scope, $rootScope, $timeout, ProjectService, EditBox) {
            var adjustingBoxPosition = false,
                categories = MapasCulturais.entity.registrationCategories.map(function(e){
                    return { value: e, label: e };
                });

            $scope.editbox = EditBox;
            
            $scope.openEditBox = function(id, e){
                console.log(id, e);
                EditBox.open(id, e);
            };

            $scope.data = {
                spinner: false,
                apiQueryRegistrationAgent: {
                    '@permissions': '@control',
                    'type': 'EQ(1)' // type individual
                },
                registrationCategories: categories,
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
            
            $scope.setRegistrationAgent = function(entity, attrs){
                console.log(attrs);
                EditBox.close(attrs.editBoxId);
            };

            $('#editbox-select-registration-owner').on('open', function () {
                if (!adjustingBoxPosition)
                    $('#find-entity-registration-owner').trigger('find');
            });

            $scope.register = function(){
                var registration = $scope.data.registration;

                if(registration.owner && (!categories.length || registration.category)){
                    ProjectService.register(registration).success(function(rs){
                        document.location = rs.editUrl;
                    });
                }else{
                    if(!registration.owner && categories.length && !registration.category){
                        MapasCulturais.Messages.error('Para se inscrever neste projeto você deve selecionar um agente responsável e uma categoria.');
                    }else if(!registration.owner){
                        MapasCulturais.Messages.error('Para se inscrever neste projeto você deve selecionar um agente responsável.');
                    }else if(categories.length && !registration.category){
                        MapasCulturais.Messages.error('Para se inscrever neste projeto você deve selecionar uma categoria.');
                    }
                }
            };

        }]);
})(angular);