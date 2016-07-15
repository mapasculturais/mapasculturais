(function (angular) {
    "use strict";

    var module = angular.module('entity.module.project', ['ngSanitize']);

    module.config(['$httpProvider', function ($httpProvider) {
            $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
            $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
            $httpProvider.defaults.transformRequest = function (data) {
                var result = angular.isObject(data) && String(data) !== '[object File]' ? $.param(data) : data;

                return result;
            };
        }]);

    module.factory('ProjectEventsService', ['$http', '$rootScope', 'UrlService', function ($http, $rootScope, UrlService) {
            var url = new UrlService('project');

            return {
                getUrl: function(action){
                    return url.create(action, MapasCulturais.entity.id);
                },

                publish: function(ids){
                   var url = this.getUrl('publishEvents');

                    return $http.post(url, {ids: ids}).
                            success(function (data, status) {
                                $rootScope.$emit('project.publishEvents', {message: "Project events was published", data: data, status: status});
                            }).
                            error(function (data, status) {
                                $rootScope.$emit('error', {message: "Cannot publish project events", data: data, status: status});
                            });


                },

                unpublish: function(ids){
                    var url = this.getUrl('unpublishEvents');

                    return $http.post(url, {ids: ids}).
                            success(function (data, status) {
                                $rootScope.$emit('project.unpublishEvents', {message: "Project events was unpublished", data: data, status: status});
                            }).
                            error(function (data, status) {
                                $rootScope.$emit('error', {message: "Cannot unpublish project events", data: data, status: status});
                            });
                }
            };
        }]);



    module.factory('RegistrationService', ['$http', '$rootScope', 'UrlService', function ($http, $rootScope, UrlService) {
            var url = new UrlService('registration');

            return {
                getUrl: function(action, registrationId){
                    return url.create(action, registrationId);
                },

                register: function (params) {
                    var data = {
                        projectId: MapasCulturais.entity.id,
                        ownerId: params.owner.id,
                        category: params.category
                    };
                    return $http.post(this.getUrl(), data).
                            success(function (data, status) {
                                $rootScope.$emit('registration.create', {message: "Project registration was created", data: data, status: status});
                            }).
                            error(function (data, status) {
                                $rootScope.$emit('error', {message: "Cannot create project registration", data: data, status: status});
                            });
                },

                setStatusTo: function(registration, registrationStatus){
                    return $http.post(this.getUrl('setStatusTo', registration.id), {status: registrationStatus}).
                            success(function (data, status) {
                                registration.status = data.status;
                                $rootScope.$emit('registration.' + registrationStatus, {message: "Project registration status was setted to " + registrationStatus, data: data, status: status});
                            }).
                            error(function (data, status) {
                                $rootScope.$emit('error', {message: "Cannot " + registrationStatus + " project registration", data: data, status: status});
                            });

                },

                send: function(registrationId){
                    return $http.post(this.getUrl('send', registrationId)).
                            success(function(data, status){
                                $rootScope.$emit('registration.send', {message: "Project registration was send ", data: data, status: status});
                            }).
                            error(function(data, status){
                                $rootScope.$emit('error', {message: "Cannot send project registration", data: data, status: status});
                            });
                }

            };
        }]);

    module.factory('RegistrationFileConfigurationService', ['$rootScope', '$q', '$http', '$log', 'UrlService', function($rootScope, $q, $http, $log, UrlService) {
        var url = new UrlService('registrationfileconfiguration');
        return {
            getUrl: function(action, id){
                return url.create(action, id);
            },
            create: function(data){
                var deferred = $q.defer();

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

                $http.post(url.create('single', data.id), data)
                    .success(
                        function(response){
                            deferred.resolve(response);
                        }
                    );
                return deferred.promise;
            },
            delete: function(id){
                var deferred = $q.defer();
                $http.get(url.create('delete', id))
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
        $scope.maxUploadSize = MapasCulturais.maxUploadSize;
        $scope.maxUploadSizeFormatted = MapasCulturais.maxUploadSizeFormatted;
        $scope.uploadFileGroup = 'registrationFileTemplate';
        $scope.getUploadUrl = function (ownerId){
            return RegistrationFileConfigurationService.getUrl('upload', ownerId);
        };

        var fileConfigurationSkeleton = {
            ownerId: MapasCulturais.entity.id,
            title: null,
            description: null,
            required: false
        };

        $scope.data = {
            fileConfigurations: MapasCulturais.entity.registrationFileConfigurations,
            newFileConfiguration: angular.copy(fileConfigurationSkeleton),
            entity: $scope.$parent.data.entity
        };

        function sortFiles(){
            $scope.data.fileConfigurations.sort(function(a,b){
                if(a.title > b.title){
                    return 1;
                } else if(a.title < b.title){
                    return -1;
                }else {
                    return 0;
                }
            });
        }

        $scope.fileConfigurationBackups = [];

        $scope.createFileConfiguration = function(){
            RegistrationFileConfigurationService.create($scope.data.newFileConfiguration).then(function(response){
                if(!response.error){
                    $scope.data.fileConfigurations.push(response);
                    sortFiles();
                    EditBox.close('editbox-registration-files');
                    $scope.data.newFileConfiguration = angular.copy(fileConfigurationSkeleton);
                    MapasCulturais.Messages.success('Anexo criado.');
                }
            });
        };

        $scope.removeFileConfiguration = function (id, $index) {
            if(confirm('Deseja remover este anexo?')){
                RegistrationFileConfigurationService.delete(id).then(function(response){
                    if(!response.error){
                        $scope.data.fileConfigurations.splice($index, 1);
                        MapasCulturais.Messages.alert('Anexo removido.');
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
                    sortFiles();
                    EditBox.close('editbox-registration-files-'+data.id);
                    MapasCulturais.Messages.success('Alterações Salvas.');
                }
            });
        };

        $scope.sendFile = function(attrs){
            $('#' + attrs.id + ' form').submit();
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
                    MapasCulturais.Messages.alert('Modelo removido.');
                });
            }
        };

        var initAjaxUploader = function(id, index){
            var $form = $('#editbox-registration-files-template-' + id + ' form');
            MapasCulturais.AjaxUploader.resetProgressBar($form);

            if($form.data('initialized'))
                return;
            MapasCulturais.AjaxUploader.init($form);

            $('#editbox-registration-files-template-'+id).on('cancel', function(){
                if($form.data('xhr')) $form.data('xhr').abort();
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

    module.controller('ProjectEventsController', ['$scope', '$rootScope', '$timeout', 'ProjectEventsService', 'EditBox', '$http', 'UrlService', function ($scope, $rootScope, $timeout, ProjectEventsService, EditBox, $http, UrlService) {
        $scope.events = $scope.data.entity.events.slice();
        $scope.numSelectedEvents = 0;

        $scope.events.forEach(function(evt){
            evt.statusText = '';

            if(evt.status == 1){
                evt.statusText = 'publicado';
            } else if(evt.status == 0){
                evt.statusText = 'rascunho';
            }
        });

        $scope.$watch('events', function(){
            var num = 0;
            $scope.events.forEach(function(e){
                if(e.selected){
                    num++;
                }
            });

            $scope.numSelectedEvents = num;
        }, true);

        $scope.selectAll = function(){
            $scope.events.forEach(function(e){
                if(!e.hidden){
                    e.selected = true;
                }
            });
        };

        $scope.deselectAll = function(){
            $scope.events.forEach(function(e){
                if(!e.hidden){
                    e.selected = false;
                }
            });
        };

        $scope.eventFilterTimeout = null;

        $scope.filterEvents = function(){
            $timeout.cancel($scope.eventFilterTimeout);
            $scope.eventFilterTimeout = $timeout(function() {
                var keywords = $scope.data.eventFilter.toLowerCase().split(' ');

                $scope.events.forEach(function(evt,i){
                    var show = true;
                    keywords.forEach(function(keyword){
                        keyword = keyword.trim();
                        var match = false;
                        if(evt.name.toLowerCase().indexOf(keyword) >= 0){
                            match = true;
                        }else if(evt.owner.name.toLowerCase().indexOf($scope.data.eventFilter.toLowerCase()) >= 0){
                            match = true;
                        }else if(evt.statusText.indexOf(keyword) >= 0){
                            match = true;
                        }else if(evt.classificacaoEtaria.toLowerCase().indexOf(keyword) >= 0){
                            match = true;
                        }else{
                            evt.occurrences.forEach(function(o){
                                if(o.space.name.toLowerCase().indexOf($scope.data.eventFilter.toLowerCase()) >= 0){
                                    match = true;
                                }
                            });

                            evt.terms.linguagem.forEach(function(term){
                                if(term.toLowerCase().indexOf(keyword) >= 0){
                                    match = true;
                                }
                            });
                        }

                        show = show && match;

                    });
                    evt.hidden = !show;

                });
            },500);

        };

        $scope.processing = false;

        $scope.publishSelectedEvents = function(){
            var ids = [],
                events = [];

            if($scope.data.processing){
                return;
            }

            $scope.events.forEach(function(e,i){
                if(e.selected){
                    ids.push(e.id);
                    events.push(e);
                }
            });

            if(!ids.length){
                return;
            }

            $scope.data.processingText = 'Publicando...';

            $scope.data.processing = true;

            ProjectEventsService.publish(ids.toString()).success(function(){
                MapasCulturais.Messages.success('Eventos publicados.');
                events.forEach(function(e){
                    e.status = 1;
                    e.statusText = 'publicado';
                });

                $scope.data.processing = false;
            });
        };

        $scope.unpublishSelectedEvents = function(){
            var ids = [],
                events = [];

            if($scope.data.processing){
                return;
            }

            $scope.events.forEach(function(e,i){
                if(e.selected){
                    ids.push(e.id);
                    events.push(e);
                }
            });

            if(!ids.length){
                return;
            }

            $scope.data.processingText = 'Tornando rascunho...';

            $scope.data.processing = true;

            ProjectEventsService.unpublish(ids.toString()).success(function(){
                MapasCulturais.Messages.success('Eventos transformados em rascunho.');
                events.forEach(function(e){
                    e.status = 0;
                    e.statusText = 'rascunho';
                });

                $scope.data.processing = false;
            });
        };


        $scope.toggle = false;
    }]);


    module.controller('RegistrationFilesController', ['$scope', '$rootScope', '$timeout', 'RegistrationFileConfigurationService', 'EditBox', '$http', 'UrlService', function ($scope, $rootScope, $timeout, RegistrationFileConfigurationService, EditBox, $http, UrlService) {
        var registrationsUrl = new UrlService('registration');

        $scope.uploadUrl = registrationsUrl.create('upload', MapasCulturais.entity.id);

        $scope.maxUploadSizeFormatted = MapasCulturais.maxUploadSizeFormatted;

        $scope.data = {
            fileConfigurations: MapasCulturais.entity.registrationFileConfigurations
        };

        $scope.data.fileConfigurations.forEach(function(item){
            item.file = MapasCulturais.entity.registrationFiles[item.groupName];
        });

        $scope.sendFile = function(attrs){
            var $form = $('#' + attrs.id + ' form');
            $form.submit();
            if(!$form.data('onSuccess')){
                $form.data('onSuccess', true);
                $form.on('ajaxForm.success', function(){
                    MapasCulturais.Messages.success('Alterações salvas.');
                });
            }
        };

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
            var $form = $('#editbox-file-' + id + ' form');
            if($form.data('initialized'))
                return;
            MapasCulturais.AjaxUploader.init($form);

            $('#editbox-file-'+id).on('cancel', function(){
                if($form.data('xhr')) $form.data('xhr').abort();
                $form.get(0).reset();
                MapasCulturais.AjaxUploader.resetProgressBar($form);
            });

            $form.on('ajaxForm.success', function(evt, response){
                $scope.data.fileConfigurations[index].file = response[$scope.data.fileConfigurations[index].groupName];
                $scope.$apply();
                setTimeout(function(){
                    EditBox.close('editbox-file-'+id, event);
                }, 700);
           });
        };

    }]);

    module.controller('ProjectController', ['$scope', '$rootScope', '$timeout', 'RegistrationService', 'EditBox', 'RelatedAgentsService', '$http', 'UrlService', function ($scope, $rootScope, $timeout, RegistrationService, EditBox, RelatedAgentsService, $http, UrlService) {
            var adjustingBoxPosition = false,
                categories = MapasCulturais.entity.registrationCategories.length ? MapasCulturais.entity.registrationCategories.map(function(e){
                    return { value: e, label: e };
                }) : [];

            $scope.editbox = EditBox;
            $scope.data = angular.extend({
                uploadSpinner: false,
                spinner: false,

                registrationCategories: categories,
                registrationCategoriesToFilter: [{value: null, label: 'Todas opções'}].concat(categories),

                registration: {
                    owner: null,
                    category: null
                },

                registrationStatuses:[
                    {value: null, label: 'Todas'},
                    {value: 1, label: 'Pendente'},
                    {value: 2, label: 'Inválida'},
                    {value: 3, label: 'Não selecionada'},
                    {value: 8, label: 'Suplente'},
                    {value: 10, label: 'Selecionada'}
                ],

                registrationStatusesNames: [
                    {value: 1, label: 'Pendente'},
                    {value: 2, label: 'Inválida'},
                    {value: 3, label: 'Não selecionada'},
                    {value: 8, label: 'Suplente'},
                    {value: 10, label: 'Selecionada'},
                    {value: 0, label: 'Rascunho'}
                ],

                publishedRegistrationStatuses:[
                    {value: null, label: 'Todas'},
                    {value: 8, label: 'Suplente'},
                    {value: 10, label: 'Selecionada'}
                ],

                publishedRegistrationStatusesNames: [
                    {value: 8, label: 'Suplente'},
                    {value: 10, label: 'Selecionada'}
                ],

                publishedRegistrationStatus: 10,

                propLabels : [],

                relationApiQuery: {}
            }, MapasCulturais);

            for(var name in MapasCulturais.labels.agent){
                var label = MapasCulturais.labels.agent[name];
                $scope.data.propLabels.push({
                    name: name,
                    label: label
                });
            }

            if(MapasCulturais.entity.registrationAgents){
                MapasCulturais.entity.registrationAgents.forEach(function(e){
                    $scope.data.relationApiQuery[e.agentRelationGroupName] = {type: 'EQ(' + e.type + ')'};
                    if(e.agentRelationGroupName === 'owner'){
                        $scope.data.relationApiQuery[e.agentRelationGroupName]['@permissions'] = '@control';
                    }
                });
            }else{
                $scope.data.relationApiQuery.owner = {'@permissions': '@control', 'type': 'EQ(1)'};
            }
            $scope.fns = {};

            $scope.hideStatusInfo = function(){
                jQuery('#status-info').slideUp('fast');
            };

            $scope.openEditBox = function(id, e){
                EditBox.open(id, e);
            };

            $scope.getStatusSlug = function(status){
                /*
                        const STATUS_SENT = self::STATUS_ENABLED;
                        const STATUS_APPROVED = 10;
                        const STATUS_WAITLIST = 8;
                        const STATUS_NOTAPPROVED = 3;
                        const STATUS_INVALID = 2;
                 */
                switch (status){
                    case 0: return 'draft'; break;
                    case 1: return 'sent'; break;
                    case 2: return 'invalid'; break;
                    case 3: return 'notapproved'; break;
                    case 8: return 'waitlist'; break;
                    case 10: return 'approved'; break;
                }
            };

            $scope.getStatusNameById = function(id){
                var statuses = $scope.data.registrationStatusesNames
                for(var s in statuses){
                    if(statuses[s].value == id)
                        return statuses[s].label;
                }
            };

            $scope.setRegistrationStatus = function(registration, status){
                if(MapasCulturais.entity.userHasControl && (status.value !== 0 || confirm('Você tem certeza que deseja reabrir este formulário para edição? Ao fazer isso, ele sairá dessa lista.'))){
                    RegistrationService.setStatusTo(registration, $scope.getStatusSlug(status.value)).success(function(entity){
                        if(registration.status === 0){
                            $scope.data.entity.registrations.splice($scope.data.entity.registrations.indexOf(registration),1);
                        }
                    });
                }
            };

            $scope.getRegistrationStatus = function(registration){
                return registration.status;
            };

            $scope.getReadableLocation = function(location){
                if(angular.isString(location) && location){
                    location = JSON.parse(location);
                }

                if(location){
                    return location.latitude + ',' + location.longitude;
                }
            };


            $scope.showRegistration = function(registration){
                var status = !$scope.data.registrationStatus || $scope.data.registrationStatus === registration.status;
                var category = !$scope.data.registrationCategory || $scope.data.registrationCategory === registration.category;

                return status && category;
            };

            $scope.getFilteredRegistrations = function(){
                return $scope.data.entity.registrations.filter(function(e){
                    return $scope.showRegistration(e);
                });
            };

            $scope.usingFilters = function(){
                return $scope.data.registrationStatus || $scope.data.registrationCategory;
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

            function replaceRegistrationAgentBy(groupName, agent, relationStatus){
                for(var i in $scope.data.entity.registrationAgents){
                    var def = $scope.data.entity.registrationAgents[i];
                    if(def.agentRelationGroupName === groupName){
                        def.agent = agent;
                        if(typeof relationStatus !== 'undefined'){
                            def.relationStatus = relationStatus;
                        }
                    }
                }
            }

            $scope.setRegistrationOwner = function(agent){
                $scope.data.registration.owner = agent;
                replaceRegistrationAgentBy('owner', agent);
                jQuery('#ownerId').editable('setValue', agent.id);
                setTimeout(function(){
                    $('#submitButton').trigger('click');
                });
                EditBox.close('editbox-select-registration-owner');
            };

            $scope.setRegistrationAgent = function(entity, attrs){
                if(attrs.name === 'owner'){
                    $scope.setRegistrationOwner(entity);
                    return;
                }
                var editBoxId = 'editbox-select-registration-' + attrs.name;
                RelatedAgentsService.create(attrs.name, entity.id).success(function(response){
                    if(response.agent.avatar && response.agent.avatar.avatarSmall){
                        response.agent.avatarUrl = response.agent.avatar.avatarSmall.url;
                    }
                    replaceRegistrationAgentBy(attrs.name, response.agent, response.status);
                    EditBox.close(editBoxId);
                    if(response.status > 0)
                        MapasCulturais.Messages.success('Alterações salvas.');
                });
            };

            $scope.unsetRegistrationAgent = function(entityId, groupName){
                if(groupName === 'owner')
                    return null;

                var editBoxId = 'editbox-select-registration-' + groupName;
                RelatedAgentsService.remove(groupName, entityId).success(function(){
                    for(var i in $scope.data.entity.registrationAgents){
                        var def = $scope.data.entity.registrationAgents[i];
                        if(def.agentRelationGroupName === groupName)
                            delete def.agent;
                    }
                    EditBox.close(editBoxId);
                });
            };

            $('#editbox-select-registration-owner').on('open', function () {
                if (!adjustingBoxPosition)
                    $('#find-entity-registration-owner').trigger('find');
            });

            $scope.register = function(){
                var registration = $scope.data.registration;

                if(registration.owner){
                    RegistrationService.register(registration).success(function(rs){
                        document.location = rs.editUrl;
                    });
                }else{
                    MapasCulturais.Messages.error('Para se inscrever neste projeto você deve selecionar um agente responsável.');
                }
            };

            $scope.sendRegistrationRulesFile = function(){
                $('#edibox-upload-rules form').submit();
            };

            $scope.openRulesUploadEditbox = function(event){
                EditBox.open('edibox-upload-rules', event);
                initAjaxUploader('edibox-upload-rules');
            };


            $scope.removeRegistrationRulesFile = function (id, $index) {
                if(confirm('Deseja remover este anexo?')){
                    $http.get($scope.data.entity.registrationRulesFile.deleteUrl).success(function(response){
                        $scope.data.entity.registrationRulesFile = null;
                    });
                }
            };

            var initAjaxUploader = function(id){
                var $form = $('#' + id + ' form');

                if($form.data('initialized'))
                    return;

                MapasCulturais.AjaxUploader.init($form);

                $('#'+id).on('cancel', function(){
                    if($form.data('xhr')) $form.data('xhr').abort();
                    $form.get(0).reset();
                    MapasCulturais.AjaxUploader.resetProgressBar($form);
                });

                $form.on('ajaxForm.success', function(evt, response){
                    $scope.data.entity.registrationRulesFile = response['rules'];
                    $scope.$apply();
                    setTimeout(function(){
                        EditBox.close(id);
                    }, 700);
               });
            };

            if(MapasCulturais.request.controller === 'registration'){
                //hide submit button and category submit on change
                $('#submitButton').hide();
                $('.js-editable-registrationCategory').on('save', function(){
                    setTimeout(function(){
                        $('#submitButton').trigger('click');
                    });
                });
            }

            $scope.sendRegistration = function(){
                RegistrationService.send($scope.data.entity.id).success(function(response){
                    $('.js-response-error').remove();
                    if(response.error){
                        var focused = false;
                        Object.keys(response.data).forEach(function(field, index){
                            var $el;
                            if(field === 'category'){
                                $el = $('.js-editable-registrationCategory').parent();
                            }else if(field.indexOf('agent') !== -1){
                                $el = $('#' + field).parent().find('.registration-label');
                            }else {
                                $el = $('#' + field).find('div:first');
                            }
                            var message = response.data[field] instanceof Array ? response.data[field].join(' ') : response.data[field];
                            message = message.replace(/"/g, '&quot;');
                            $scope.data.propLabels.forEach(function(prop){
                                message = message.replace('{{'+prop.name+'}}', prop.label);
                            });
                            $el.append('<span title="' + message + '" class="danger hltip js-response-error" data-hltip-classes="hltip-danger"></span>');
                            if(!focused){
                                $('html,body').animate({scrollTop: $el.parents('li').get(0).offsetTop - 10}, 300);
                                focused = true;
                            }
                        });
                        MapasCulturais.Messages.error('Corrija os erros indicados abaixo.');
                    }else{
                        MapasCulturais.Messages.success('Inscrição enviada. Aguarde tela de sumário.');
                        document.location = response.singleUrl;
                    }
                });
            };

            var url = new UrlService('project');

            $scope.publish = function(){
                $http.post(url.create('publish', $scope.data.entity.id)).
                    success(function(r){
                        alert('publicado');
                    }).error(function(r){
                        alert('erro');
                    });
            };
        }]);
    
    module.controller('RelatedSealsController', ['$scope', '$rootScope', 'RelatedSealsService', 'EditBox', function($scope, $rootScope, RelatedSealsService, EditBox) {
        $scope.editbox = EditBox;
        
        $scope.seals = [];
        
        for(var i in MapasCulturais.allowedSeals)
            $scope.seals.push(MapasCulturais.allowedSeals[i]);
        
        $scope.registrationSeals = [];
        
        $scope.showCreateDialog = {};
        
        $scope.isEditable = MapasCulturais.isEditable;
        
        $scope.data = {};
        
        $scope.avatarUrl = function(url){
            if(url)
                return url;
            else
                return MapasCulturais.assets.avatarSeal;
        };
        
        $scope.closeNewSealEditBox = function(){
            EditBox.close('new-related-seal');
        };
        
        $scope.getCreateSealRelationEditBoxId = function(){
            return 'add-related-seal';
        };
        
        $scope.entity = MapasCulturais.entity.object;
        
        $scope.setSeal = function(agent, entity){
        	var sealRelated = {};
            var _scope = this.$parent;
            console.log(agent, entity);
            
            if(typeof $scope.entity.registrationSeals !== "object") {
            	$scope.entity.registrationSeals = {};
        	} 
            
            $scope.entity.registrationSeals[agent] =  entity.id;
            sealRelated = $scope.entity.registrationSeals;
            jQuery("#registrationSeals").editable('setValue',sealRelated);
            
            $scope.registrationSeals.push(sealRelated);
            EditBox.close('set-seal-' + agent);
//            
//            RelatedSealsService.create(entity.id).success(function(data){
//                $scope.showCreateDialog = false;
//                _scope.$parent.searchText = 'Mary, vocÊ está aqui!';
//                _scope.$parent.result = [];
//                EditBox.close($scope.getCreateSealRelationEditBoxId());
//            });
        };
        
        $scope.getArrIndexBySealId = function(sealId) {
        	var Found = 0;
        	for(var Found in $scope.seals){
                if($scope.seals[Found].id == sealId) {
                    return Found;
                }
            }
        	
        };
        
        $scope.deleteRelation = function(relation){
            RelatedSealsService.remove(relation.seal.id).error(function(){
                relations = oldRelations;
            });
        };
    }]);
})(angular);