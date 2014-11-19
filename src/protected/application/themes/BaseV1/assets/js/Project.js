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

    module.factory('RegistrationService', ['$http', '$rootScope', function ($http, $rootScope) {
            function getUrl(action, registrationId){
                var url = MapasCulturais.baseURL + 'registration';

                if(action){
                    url += '/' + action;
                }

                if(registrationId){
                    url += '/' + registrationId;
                }

                return url;
            }

            function setStatus(registration, registrationStatus){
                return $http.post(getUrl(registrationStatus, registration.id)).
                            success(function (data, status) {
                                registration.status = data.status;
                                $rootScope.$emit('registration.' + registrationStatus, {message: "Project registration was " + registrationStatus, data: data, status: status});
                            }).
                            error(function (data, status) {
                                $rootScope.$emit('error', {message: "Cannot " + registrationStatus + " project registration", data: data, status: status});
                            });
            }

            return {
                register: function (params) {
                    var data = {
                        projectId: MapasCulturais.entity.id,
                        ownerId: params.owner.id,
                        category: params.category.value
                    };
                    return $http.post(getUrl(), data).
                            success(function (data, status) {
                                $rootScope.$emit('registration.create', {message: "Project registration was created", data: data, status: status});
                            }).
                            error(function (data, status) {
                                $rootScope.$emit('error', {message: "Cannot create project registration", data: data, status: status});
                            });
                },

                approve: function(registration){
                    return setStatus(registration, 'approve');
                },

                reject: function(registration){
                    return setStatus(registration, 'reject');
                },

                maybe: function(registration){
                    return setStatus(registration, 'maybe');
                }

            };
        }]);

    module.factory('RegistrationFileConfigurationService', ['$rootScope', '$q', '$http', '$log', function($rootScope, $q, $http, $log) {
        return {
            getUrl: function(){
                return MapasCulturais.baseURL + 'registrationfileconfiguration';
            },
            create: function(data){
                var deferred = $q.defer();
                $log.debug(data);
                $http.post(this.getUrl(), data)
                    .success(
                        function(response){
                            deferred.resolve(response);
                        }
                    );
                return deferred.promise;
            },
            edit: function(data){
                var deferred = $q.defer();
                $log.debug(data);
                $http.post(this.getUrl()+'/single/'+data.id, data)
                    .success(
                        function(response){
                            deferred.resolve(response);
                        }
                    );
                return deferred.promise;
            },
            delete: function(id){
                var deferred = $q.defer();
                $http.get(this.getUrl()+'/delete/'+id)
                    .success(
                        function(response){
                            deferred.resolve(response);
                        }
                    );
                return deferred.promise;
            }
        };

    }]);
    module.controller('RegistrationFileConfigurationsController', ['$scope', '$rootScope', '$timeout', 'RegistrationFileConfigurationService', 'EditBox', '$http', function ($scope, $rootScope, $timeout, RegistrationFileConfigurationService, EditBox, $http) {

        $scope.isEditable = MapasCulturais.isEditable;
        $scope.uploadFileGroup = 'registrationFileTemplate';
        $scope.getUploadUrl = function (ownerId){
            return RegistrationFileConfigurationService.getUrl() + '/upload/id:' + ownerId;
        };

        var fileConfigurationSkeleton = {
            ownerId: MapasCulturais.entity.id,
            title: null,
            description: null,
            required: false
        };

        $scope.data = {
            fileConfigurations: MapasCulturais.entity.registrationFileConfigurations,
            newFileConfiguration: angular.copy(fileConfigurationSkeleton)
        };

        $scope.fileConfigurationBackups = [];

        $scope.createFileConfiguration = function(){
            RegistrationFileConfigurationService.create($scope.data.newFileConfiguration).then(function(response){
                if(!response.error){
                    $scope.data.fileConfigurations.push(response);
                    EditBox.close('editbox-registration-files');
                    $scope.data.newFileConfiguration = angular.copy(fileConfigurationSkeleton);
                }
            });
        };

        $scope.removeFileConfiguration = function (id, $index) {
            if(confirm('Deseja remover este anexo?')){
                RegistrationFileConfigurationService.delete(id).then(function(response){
                    if(!response.error){
                        $scope.data.fileConfigurations.splice($index, 1);
                    }
                });
            }
        };

        $scope.editFileConfiguration = function(attrs) {
            var model = $scope.data.fileConfigurations[attrs.index],
                data = {
                    id: model.id,
                    title: model.title,
                    description: model.description,
                    required: model.required,
                    template: model.template
                };
            RegistrationFileConfigurationService.edit(data).then(function(response){
                if(!response.error){
                    EditBox.close('editbox-registration-files-'+data.id);
                }
            });
        };

        $scope.cancelFileConfigurationEditBox = function(attrs){
            $scope.data.fileConfigurations[attrs.index] = $scope.fileConfigurationBackups[attrs.index];
            delete $scope.fileConfigurationBackups[attrs.index];
        };

        $scope.openFileConfigurationEditBox = function(id, index, event){
            $scope.fileConfigurationBackups[index] = angular.copy($scope.data.fileConfigurations[index]);
            EditBox.open('editbox-registration-files-'+id, event);
        };

        $scope.openFileConfigurationTemplateEditBox = function(id, index, event){
            EditBox.open('editbox-registration-files-template-'+id, event);
            initAjaxUploader(id, index);
        };

        $scope.removeFileConfigurationTemplate = function (id, $index) {
            if(confirm('Deseja remover este modelo?')){
                $http.get($scope.data.fileConfigurations[$index].template.deleteUrl).success(function(response){
                    delete $scope.data.fileConfigurations[$index].template;
                });
            }
        };

        var initAjaxUploader = function(id, index){
            var $form = jQuery('#editbox-registration-files-template-' + id + ' form');

            if($form.data('initialized'))
                return;
            MapasCulturais.AjaxUploader.init($form);

            jQuery('#editbox-registration-files-template-'+id).on('cancel', function(){
                $form.data('xhr').abort();
                $form.get(0).reset();
                MapasCulturais.AjaxUploader.resetProgressBar($form);
            });

            $form.on('ajaxForm.success', function(evt, response){
                $scope.data.fileConfigurations[index].template = response[$scope.uploadFileGroup];
                $scope.$apply();
                setTimeout(function(){
                    EditBox.close('editbox-registration-files-template-'+id, event);
                }, 700);
           });
        };

    }]);

    module.controller('RegistrationFilesController', ['$scope', '$rootScope', '$timeout', 'RegistrationFileConfigurationService', 'EditBox', '$http', function ($scope, $rootScope, $timeout, RegistrationFileConfigurationService, EditBox, $http) {
        $scope.uploadUrl = MapasCulturais.baseURL + 'registration/upload/id:' + MapasCulturais.entity.id;

        $scope.data = {
            fileConfigurations: MapasCulturais.entity.registrationFileConfigurations
        };

        $scope.data.fileConfigurations.forEach(function(item){
            item.file = MapasCulturais.entity.registrationFiles[item.groupName];
        });

        $scope.openFileEditBox = function(id, index, event){
            EditBox.open('editbox-file-'+id, event);
            initAjaxUploader(id, index);
        };

        $scope.removeFile = function (id, $index) {
            if(confirm('Deseja remover este anexo?')){
                $http.get($scope.data.fileConfigurations[$index].file.deleteUrl).success(function(response){
                    delete $scope.data.fileConfigurations[$index].file;
                });
            }
        };

        var initAjaxUploader = function(id, index){
            var $form = jQuery('#editbox-file-' + id);
            if($form.data('initialized'))
                return;
            MapasCulturais.AjaxUploader.init($form);

            $form.on('ajaxform.success', function(evt, response){
                $scope.data.fileConfigurations[index].file = response[$scope.data.fileConfigurations[index].groupName];
                $scope.$apply();
                setTimeout(function(){
                    EditBox.close('editbox-file-'+id, event);
                }, 700);
           });
        };

    }]);

    module.controller('ProjectController', ['$scope', '$rootScope', '$timeout', 'RegistrationService', 'EditBox', 'RelatedAgentsService', function ($scope, $rootScope, $timeout, RegistrationService, EditBox, RelatedAgentsService) {
            var adjustingBoxPosition = false,
                categories = MapasCulturais.entity.registrationCategories.map(function(e){
                    return { value: e, label: e };
                });

            $scope.editbox = EditBox;

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
                },

                registrations: MapasCulturais.entity.registrations,

                registrationStatuses:[
                    {value: null, label: 'Todos'},
                    {value: 1, label: 'Aguardando'},
                    {value: 3, label: 'Rejeitado'},
                    {value: 8, label: 'Suplente'},
                    {value: 10, label: 'Aprovado'}
                ],

                userHasControl: MapasCulturais.entity.userHasControl
            };

            $scope.openEditBox = function(id, e){
                EditBox.open(id, e);
            };

            $scope.statusName = function(registration){
                switch (registration.status){
                    case 1: return 'waiting'; break;
                    case 3: return 'rejected'; break;
                    case 8: return 'maybe'; break;
                    case 10: return 'approved'; break;
                }
            };

            $scope.setRegistrationStatus = function(registration, status){
                if(MapasCulturais.entity.userHasControl){
                    RegistrationService[status](registration);
                }
            };


            $scope.showRegistration = function(registration){
                var result = !$scope.data.registrationStatus || !$scope.data.registrationStatus.value || $scope.data.registrationStatus.value === registration.status;

                return result;
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
                var editBoxId = 'editbox-select-registration-' + attrs.name;
                RelatedAgentsService.create(attrs.name, entity.id).success(function(){
                    var $el = $('#registration-agent-' + attrs.name);

                    EditBox.close(editBoxId);
                });
            };

            $('#editbox-select-registration-owner').on('open', function () {
                if (!adjustingBoxPosition)
                    $('#find-entity-registration-owner').trigger('find');
            });

            $scope.register = function(){
                var registration = $scope.data.registration;

                if(registration.owner && (!categories.length || registration.category)){
                    RegistrationService.register(registration).success(function(rs){
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