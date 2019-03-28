(function (angular) {
    "use strict";
    var module = angular.module('entity.module.opportunity', ['ngSanitize', 'checklist-model']);

    module.config(['$httpProvider', function ($httpProvider) {
        $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
        $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
        $httpProvider.defaults.transformRequest = function (data) {
            var result = angular.isObject(data) && String(data) !== '[object File]' ? $.param(data) : data;

            return result;
        };
    }]);

    function getOpportunityId(){
        if(MapasCulturais.request.controller == 'registration'){
            return MapasCulturais.entity.object.opportunity.id;
        } else {
            return MapasCulturais.entity.id;
        }
    }

    function _getStatusSlug(status) {
        switch (status) {
            case 0: return 'draft'; break;
            case 1: return 'sent'; break;
            case 2: return 'invalid'; break;
            case 3: return 'notapproved'; break;
            case 8: return 'waitlist'; break;
            case 10: return 'approved'; break;
        }
    }

    module.factory('RegistrationService', ['$http', '$rootScope', '$q', 'UrlService', function ($http, $rootScope, $q, UrlService) {
        var url = new UrlService('registration');
        var labels = MapasCulturais.gettext.moduleOpportunity;

        return {
            getUrl: function(action, registrationId){
                return url.create(action, registrationId);
            },

            register: function (params) {
                var data = {
                    opportunityId: MapasCulturais.entity.id,
                    ownerId: params.owner.id,
                    category: params.category
                };

                return $http.post( this.getUrl(), data).
                success(function (data, status) {
                    $rootScope.$emit('registration.create', {message: "Opportunity registration was created", data: data, status: status});
                }).
                error(function (data, status) {
                    $rootScope.$emit('error', {message: "Cannot create opportunity registration", data: data, status: status});
                });
            },

            setMultipleStatus: function(registrations) {
                var endPoint = url.create('setMultipleStatus');

                return $http.post(endPoint, { evaluations: registrations }).
                success(function (data) {
                    for(var aval in data) {
                        var slug = _getStatusSlug(data[aval]);
                        $("#registration-" +  aval).attr('class', slug);

                        var txt = $("#registration-" +  aval + " .registration-status-col").first().text();
                        $("#registration-" + aval + " .registration-status-col .dropdown.js-dropdown div").text(txt);
                    }
                });
            },

            setStatusTo: function(registration, registrationStatus){
                return $http.post(this.getUrl('setStatusTo', registration.id), {status: registrationStatus}).
                success(function (data, status) {
                    registration.status = data.status;
                    $rootScope.$emit('registration.' + registrationStatus, {message: "Opportunity registration status was setted to " + registrationStatus, data: data, status: status});
                }).
                error(function (data, status) {
                    $rootScope.$emit('error', {message: "Cannot " + registrationStatus + " opportunity registration", data: data, status: status});
                });

            },

            send: function(registrationId){
                return $http.post(this.getUrl('send', registrationId)).
                success(function(data, status){
                    $rootScope.$emit('registration.send', {message: "Opportunity registration was send ", data: data, status: status});
                }).
                error(function(data, status){
                    $rootScope.$emit('error', {message: "Cannot send opportunity registration", data: data, status: status});
                });
            },

            save: function () {
                jQuery('a.js-submit-button').click();
            },

            getFields: function () {
                var fieldTypes = MapasCulturais.registrationFieldTypes.slice();

                var fieldTypesBySlug = {};

                fieldTypes.forEach(function (e) {
                    fieldTypesBySlug[e.slug] = e;
                });

                function processFieldConfiguration(field) {
                    if(typeof field.fieldOptions === 'string'){
                        field.fieldOptions = field.fieldOptions ? field.fieldOptions.split("\n") : [];
                    }

                    return field;
                }

                function processFileConfiguration(file) {
                    file.fieldType = 'file';
                    file.categories = file.categories || [];
                    return file;
                }

                var _files = MapasCulturais.entity.registrationFileConfigurations.map(processFileConfiguration);
                var _fields = MapasCulturais.entity.registrationFieldConfigurations.map(processFieldConfiguration);


                var fields = _files.concat(_fields);

                fields.sort(function(a,b){
                    if(a.displayOrder > b.displayOrder){
                        return 1;
                    } else if(a.displayOrder < b.displayOrder){
                        return -1;
                    }else {
                        return 0;
                    }
                });

                return fields;
            },

            getSelectedCategory: function(){
                return $q(function(resolve){
                    var interval = setInterval(function(){
                        var $editable = jQuery('.js-editable-registrationCategory');

                        if($editable.length){
                            var editable = $editable.data('editable');
                            if(editable){
                                clearInterval(interval);
                                resolve(editable.value);
                            }
                        }else{
                            resolve(MapasCulturais.entity.object.category);
                        }
                    },50)
                });
            },

            registrationStatuses: [
                {value: "", label: labels['allStatus']},
                {value: 1, label: labels['pending']},
                {value: 2, label: labels['invalid']},
                {value: 3, label: labels['notSelected']},
                {value: 8, label: labels['suplente']},
                {value: 10, label: labels['selected']},
                {value: 0, label: labels['draft']}
            ],

            registrationStatusesNames: [
                {value: 1, label: labels['pending']},
                {value: 2, label: labels['invalid']},
                {value: 3, label: labels['notSelected']},
                {value: 8, label: labels['suplente']},
                {value: 10, label: labels['selected']},
                {value: 0, label: labels['draft']}
            ],

            publishedRegistrationStatuses: [
                {value: null, label: labels['allStatus']},
                {value: 8, label: labels['suplente']},
                {value: 10, label: labels['selected']}
            ],

            publishedRegistrationStatusesNames: [
                {value: 8, label: labels['suplente']},
                {value: 10, label: labels['selected']}
            ]

        };
    }]);

module.factory('RegistrationConfigurationService', ['$rootScope', '$q', '$http', '$log', 'UrlService', function($rootScope, $q, $http, $log, UrlService) {
    return function (controllerId){
        var url = new UrlService(controllerId);
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
    };
}]);

module.factory('EvaluationMethodConfigurationService', ['$rootScope', '$q', '$http', '$log', 'UrlService', function($rootScope, $q, $http, $log, UrlService) {
    var url = new UrlService('evaluationMethodConfiguration');
    return {
        getUrl: function(action){
            return url.create(action, [MapasCulturais.entity.object.evaluationMethodConfiguration.id]);
        },
        patch: function(data){
            var deferred = $q.defer();
            $http.post(this.getUrl('single'), data)
            .success(
                function(response){
                    deferred.resolve(response);
                }
            );
            return deferred.promise;
        },
        reopenValuerEvaluations: function(relation){
            var deferred = $q.defer();

            $http.post(this.getUrl('reopenValuerEvaluations'), {relationId: relation.id})
            .success(
                function(response){
                    deferred.resolve(response);
                }
            );
            return deferred.promise;
        }
    };
}]);

module.controller('RegistrationConfigurationsController', ['$scope', '$rootScope', '$timeout', '$interval', 'UrlService', 'RegistrationConfigurationService', 'EditBox', '$http', function ($scope, $rootScope, $timeout, $interval, UrlService, RegistrationConfigurationService, EditBox, $http) {
    var fileService = RegistrationConfigurationService('registrationfileconfiguration');
    var fieldService = RegistrationConfigurationService('registrationfieldconfiguration');

    var labels = MapasCulturais.gettext.moduleOpportunity;

    $scope.isEditable = MapasCulturais.isEditable;
    $scope.maxUploadSize = MapasCulturais.maxUploadSize;
    $scope.maxUploadSizeFormatted = MapasCulturais.maxUploadSizeFormatted;
    $scope.uploadFileGroup = 'registrationFileTemplate';
    $scope.getUploadUrl = function (ownerId){
        return fileService.getUrl('upload', ownerId);
    };

    var fieldTypes = MapasCulturais.registrationFieldTypes.slice();

    var fieldTypesBySlug = {};

    fieldTypes.forEach(function(e){
        fieldTypesBySlug[e.slug] = e;
    });

    fieldTypes.unshift({
        slug: null,
        name: labels['selectFieldType'],
        disabled: true
    });

    var fileConfigurationSkeleton = {
        ownerId: MapasCulturais.entity.id,
        title: null,
        description: null,
        required: false,
        categories: []
    };

    var fieldConfigurationSkeleton = {
        ownerId: MapasCulturais.entity.id,
        fieldType: null,
        fieldOptions: null,
        title: null,
        description: null,
        maxSize: null,
        required: false,
        categories: []
    };

    function processFieldConfiguration(field){
        if(field.fieldOptions instanceof Array){
            field.fieldOptions = field.fieldOptions.join("\n");
        }
        return field;
    }

    function processFileConfiguration(file){
        file.fieldType = 'file';
        file.categories = file.categories || [];
        return file;
    }

    var _files = MapasCulturais.entity.registrationFileConfigurations.map(processFileConfiguration);
    var _fields = MapasCulturais.entity.registrationFieldConfigurations.map(processFieldConfiguration);

        // @TODO: USAR A FUNCAO getFields
        var fields = _files.concat(_fields);

        $scope.sortableOptions = {

            // ao reordenar, atualiza displayOrder dos campos e salva
            stop: function(e, ui) {

                var ii = 1;

                $.each(fields, function(i,f) {
                    f.displayOrder=ii;
                    ii++;
                });

                var url = new UrlService('opportunity');
                var saveOrderUrl = url.create('saveFieldsOrder', MapasCulturais.entity.id);

                // requisição para salvar ordem
                $http.post(saveOrderUrl, {fields: fields});
            }
        };

        $scope.data = {
            fieldSpinner: false,
            uploadSpinner: false,

            fields: fields,
            newFileConfiguration: angular.copy(fileConfigurationSkeleton),
            newFieldConfiguration: angular.copy(fieldConfigurationSkeleton),
            entity: $scope.$parent.data.entity,
            fieldTypes: fieldTypes,
            fieldsWithOptions: fieldTypes.filter(function(e) { if(e.requireValuesConfiguration) return e; }).map(function(e) { return e.slug; } ),
            fieldTypesBySlug: fieldTypesBySlug,
            fieldsRequiredLabel: labels['requiredLabel'],
            fieldsOptionalLabel: labels['optionalLabel'],
            categories: []
        };

        $scope.data.newFieldConfiguration.fieldType = fieldTypes[0].slug;


        $interval(function(){
            $scope.data.categories = jQuery('#registration-categories .js-categories-values').text().split("\n");
        },1000);

        $scope.allCategories = function(model){
            return model.categories.length === 0;
        }

        function sortFields(){
            $scope.data.fields.sort(function(a,b){
                if(a.displayOrder > b.displayOrder){
                    return 1;
                } else if(a.displayOrder < b.displayOrder){
                    return -1;
                }else {
                    return 0;
                }
            });
        }


        sortFields();

        function validationErrors(response){
            Object.keys(response.data).forEach(function(prop){
                Object.keys(response.data[prop]).forEach(function(error){
                    MapasCulturais.Messages.error(response.data[prop][error]);
                });
            });
        }

        // Fields
        $scope.fieldConfigurationBackups = [];

        $scope.createFieldConfiguration = function(){
            $scope.data.fieldSpinner = true;
            $scope.data.newFieldConfiguration.displayOrder = $scope.data.fields.length +1;
            fieldService.create($scope.data.newFieldConfiguration).then(function(response){
                $scope.data.fieldSpinner = false;

                if (response.error) {
                    validationErrors(response);

                } else {
                    $scope.data.fields.push(response);
                    sortFields();
                    EditBox.close('editbox-registration-fields');
                    $scope.data.newFieldConfiguration = angular.copy(fieldConfigurationSkeleton);
                    MapasCulturais.Messages.success(labels['fieldCreated']);
                }
            });
        };

        $scope.removeFieldConfiguration = function (id, $index) {
            if(confirm('Deseja remover este campo?')){
                fieldService.delete(id).then(function(response){
                    if(!response.error){
                        $scope.data.fields.splice($index, 1);
                        MapasCulturais.Messages.alert(labels['fieldRemoved']);
                    }
                });
            }
        };

        $scope.editFieldConfiguration = function(attrs) {
            var model = $scope.data.fields[attrs.index],
            data = {
                id: model.id,
                title: model.title,
                fieldType: model.fieldType,
                fieldOptions: model.fieldOptions,
                description: model.description,
                maxSize: model.maxSize,
                required: model.required,
                categories: model.categories.length ? model.categories : '',

            };

            $scope.data.fieldSpinner = true;
            fieldService.edit(data).then(function(response){
                $scope.data.fieldSpinner = false;
                if (response.error) {
                    validationErrors(response);

                } else {
                    sortFields();
                    EditBox.close('editbox-registration-field-'+data.id);
                    MapasCulturais.Messages.success(labels['changesSaved']);
                }
            });
        };

        $scope.cancelFieldConfigurationEditBox = function(attrs){
            $scope.data.fields[attrs.index] = $scope.fieldConfigurationBackups[attrs.index];
            delete $scope.fieldConfigurationBackups[attrs.index];
        };

        $scope.openFieldConfigurationEditBox = function(id, index, event){
            $scope.fieldConfigurationBackups[index] = angular.copy($scope.data.fields[index]);
            EditBox.open('editbox-registration-field-'+id, event);
        };

        // Files
        $scope.fileConfigurationBackups = [];

        $scope.createFileConfiguration = function(){
            $scope.data.uploadSpinner = true;
            $scope.data.newFileConfiguration.displayOrder = $scope.data.fields.length +1;
            fileService.create($scope.data.newFileConfiguration).then(function(response){
                $scope.data.uploadSpinner = false;
                if (response.error) {
                    validationErrors(response);

                } else {
                    response = processFileConfiguration(response);
                    $scope.data.fields.push(response);
                    sortFields();
                    EditBox.close('editbox-registration-files');
                    $scope.data.newFileConfiguration = angular.copy(fileConfigurationSkeleton);
                    MapasCulturais.Messages.success(labels['attachmentCreated']);
                }
            });
        };

        $scope.removeFileConfiguration = function (id, $index) {
            if(confirm(labels['confirmAttachmentRemoved'])){
                fileService.delete(id).then(function(response){
                    if(!response.error){
                        $scope.data.fields.splice($index, 1);
                        MapasCulturais.Messages.alert(labels['attachmentRemoved']);
                    }
                });
            }
        };

        $scope.editFileConfiguration = function(attrs) {
            $scope.data.uploadSpinner = true;
            var model = $scope.data.fields[attrs.index],
            data = {
                id: model.id,
                title: model.title,
                description: model.description,
                required: model.required,
                template: model.template,
                categories: model.categories.length ? model.categories : '',
            };
            fileService.edit(data).then(function(response){
                $scope.data.uploadSpinner = false;
                if (response.error) {
                    validationErrors(response);

                } else {
                    sortFields();
                    EditBox.close('editbox-registration-files-'+data.id);
                    MapasCulturais.Messages.success(labels['changesSaved']);
                }
            });
        };

        $scope.sendFile = function(attrs){
            $('#' + attrs.id + ' form').submit();
        };

        $scope.cancelFileConfigurationEditBox = function(attrs){
            $scope.data.fields[attrs.index] = $scope.fileConfigurationBackups[attrs.index];
            delete $scope.fileConfigurationBackups[attrs.index];
        };

        $scope.openFileConfigurationEditBox = function(id, index, event){
            $scope.fileConfigurationBackups[index] = angular.copy($scope.data.fields[index]);
            EditBox.open('editbox-registration-files-'+id, event);
        };

        $scope.openFileConfigurationTemplateEditBox = function(id, index, event){
            EditBox.open('editbox-registration-files-template-'+id, event);
            initAjaxUploader(id, index);

        };

        $scope.removeFileConfigurationTemplate = function (id, $index) {
            if(confirm(labels['confirmRemoveModel'])){
                $http.get($scope.data.fields[$index].template.deleteUrl).success(function(response){
                    delete $scope.data.fields[$index].template;
                    MapasCulturais.Messages.alert(labels['modelRemoved']);
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
                $scope.data.fields[index].template = response[$scope.uploadFileGroup];
                $scope.$apply();
                setTimeout(function(){
                    EditBox.close('editbox-registration-files-template-'+id, event);
                }, 700);
            });
        };

    }]);

module.controller('OpportunityEventsController', ['$scope', '$rootScope', '$timeout', 'OpportunityEventsService', 'EditBox', '$http', 'UrlService', function ($scope, $rootScope, $timeout, OpportunityEventsService, EditBox, $http, UrlService) {
    $scope.events = $scope.data.entity.events.slice();
    $scope.numSelectedEvents = 0;

    var labels = MapasCulturais.gettext.moduleOpportunity;

    $scope.events.forEach(function(evt){
        evt.statusText = '';

        if(evt.status == 1){
            evt.statusText = labels['statusPublished'];
        } else if(evt.status == 0){
            evt.statusText = labels['statusDraft'];
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

        $scope.data.processingText = labels['publishing...'];

        $scope.data.processing = true;

        OpportunityEventsService.publish(ids.toString()).success(function(){
            MapasCulturais.Messages.success(labels['eventsPublished']);
            events.forEach(function(e){
                e.status = 1;
                e.statusText = labels['statusPublished'];
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

        $scope.data.processingText = labels['savingAsDraft'];

        $scope.data.processing = true;

        OpportunityEventsService.unpublish(ids.toString()).success(function(){
            MapasCulturais.Messages.success(labels['savedAsDraft']);
            events.forEach(function(e){
                e.status = 0;
                e.statusText = labels['statusDraft'];
            });

            $scope.data.processing = false;
        });
    };


    $scope.toggle = false;
}]);


module.controller('RegistrationFieldsController', ['$scope', '$rootScope', '$interval', '$timeout', 'RegistrationService', 'RegistrationConfigurationService', 'EditBox', '$http', 'UrlService', function ($scope, $rootScope, $interval, $timeout, RegistrationService, RegistrationConfigurationService, EditBox, $http, UrlService) {
    var registrationsUrl = new UrlService('registration');

    var labels = MapasCulturais.gettext.moduleOpportunity;

    $scope.uploadUrl = registrationsUrl.create('upload', MapasCulturais.entity.id);

    $scope.maxUploadSizeFormatted = MapasCulturais.maxUploadSizeFormatted;

    $scope.entity = MapasCulturais.entity.object;

    $scope.data = {
        fileConfigurations: MapasCulturais.entity.registrationFileConfigurations
    };

    $scope.data.fileConfigurations.forEach(function(item){
        item.file = MapasCulturais.entity.registrationFiles[item.groupName];
    });


    $scope.data.fields = RegistrationService.getFields();
    $scope.data.fieldsRequiredLabel = labels['requiredLabel'];
    $scope.data.fieldsOptionalLabel = labels['optionalLabel'];

    var fieldsByName = {};
    $scope.data.fields.forEach(function(e){
        fieldsByName[e.fieldName] = e;
    });

    function initEditables(){
        jQuery('.js-editable-field').each(function(){
            var field = fieldsByName[this.id];
            if(field && field.fieldOptions){
                var cfg = {};
                cfg.source = field.fieldOptions.map(function(e){ return {value: e, text: e}; });

                if(field.fieldType === "date"){
                    cfg.datepicker = {weekStart: 1, yearRange: jQuery(this).data('yearrange') ? jQuery(this).data('yearrange') : "1900:+0"};
                }
                jQuery(this).editable(cfg);
            } else {
                jQuery(this).editable();
            }

            if(!jQuery(this).data('editable-init')){
                jQuery(this).data('editable-init', true);
                jQuery(this).on('save', function(){
                    setTimeout(function(){
                        RegistrationService.save();
                    });
                });

            }
        });
    }

    $rootScope.$on('repeatDone:registration-fields', function(){
            // só para esperar a renderização
            $timeout(function(){
                initEditables();
            });
        });

    $scope.showField = function (field) {

    };

    $scope.sendFile = function(attrs){
        $('.carregando-arquivo').show();
        $('.submit-attach-opportunity').hide();

        var $form = $('#' + attrs.id + ' form');
        $form.submit();
        if(!$form.data('onSuccess')){
            $form.data('onSuccess', true);
            $form.on('ajaxForm.success', function(){
                MapasCulturais.Messages.success(labels['changesSaved']);
            });
        }
    };

    $scope.openFileEditBox = function(id, index, event){
        EditBox.open('editbox-file-'+id, event);
        initAjaxUploader(id, index);
    };

    $scope.removeFile = function (id, $index) {
        if(confirm(labels['confirmRemoveAttachment'])){
            $http.get($scope.data.fields[$index].file.deleteUrl).success(function(response){
                delete $scope.data.fields[$index].file;
            });
        }
    };

    var initAjaxUploader = function(id, index) {
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
            $scope.data.fields[index].file = response[$scope.data.fields[index].groupName];
            $scope.$apply();
            setTimeout(function(){
                $('.carregando-arquivo').hide();
                $('.submit-attach-opportunity').show();
                EditBox.close('editbox-file-'+id, evt);
            }, 700);
        });
    };

    $scope.useCategories = MapasCulturais.entity.registrationCategories.length > 0;


    if($scope.useCategories){

        RegistrationService.getSelectedCategory().then(function(value){
            $scope.selectedCategory = value;
        });

        $('.js-editable-registrationCategory').on('save', function(){
            RegistrationService.getSelectedCategory().then(function(value){
                $scope.selectedCategory = value;
            });
        });

        $scope.$watch('selectedCategory', function(){
            $timeout(function(){
                initEditables();
            });
        });

    }

    $scope.showFieldForCategory = function(field){
        var result;
        if (!$scope.useCategories){
            result = true;
        } else {
            result = field.categories.length === 0 || field.categories.indexOf($scope.selectedCategory) >= 0;
        }

        return result;
    };



    $scope.printField = function(field, value){

        if (field.fieldType === 'date') {
            return moment(value).format('DD-MM-YYYY');
        } else if (field.fieldType === 'url'){
            return '<a href="' + value + '" target="_blank">' + value + '</a>';
        } else if (field.fieldType === 'email'){
            return '<a href="mailto:' + value + '"  target="_blank">' + value + '</a>';
        } else if (value instanceof Array) {
            return value.join(', ');
        } else {
            return value;
        }
    };

}]);


    module.controller('EvaluationMethodConfigurationController', ['$scope', '$rootScope', 'RelatedAgentsService', 'EvaluationMethodConfigurationService', 'EditBox', 'OpportunityApiService', function($scope, $rootScope, RelatedAgentsService, EvaluationMethodConfigurationService, EditBox, OpportunityApiService) {
        var labels = MapasCulturais.gettext.moduleOpportunity;
        var emconfig = MapasCulturais.entity.object.evaluationMethodConfiguration;
        
        var committeeApi = new OpportunityApiService($scope, 'committee', 'evaluationCommittee', {'@opportunity': getOpportunityId()});

        $scope.editbox = EditBox;
        RelatedAgentsService = angular.copy(RelatedAgentsService);

        RelatedAgentsService.controllerId = 'evaluationMethodConfiguration';
        RelatedAgentsService.entityId = MapasCulturais.entity.object.evaluationMethodConfiguration.id;

        $scope.groups = [];

        $scope.showCreateDialog = {};

        $scope.spinners = {};

        $scope.isEditable = MapasCulturais.isEditable;
        $scope.canChangeControl = MapasCulturais.entity.canUserCreateRelatedAgentsWithControl;

        $scope.data = {
            entity: MapasCulturais.entity,
            categories: MapasCulturais.entity.registrationCategories,
            committee: [],

        };
        
        committeeApi.find().success(function(result){
            $scope.data.committee = result;
        });

        $scope.fetch = emconfig.fetch || {};
        $scope.fetchCategories = emconfig.fetchCategories || {};

        $scope.config = {
            fetch: emconfig.fetch,
            fetchCategories: emconfig.fetchCategories,
            infos: emconfig.infos
        };

        var lastConfig = angular.copy($scope.config);

        $scope.$watch('config', function(o,n){
            if(angular.equals(lastConfig, $scope.config)){
                return;
            }

            lastConfig = angular.copy($scope.config);

            var promise = EvaluationMethodConfigurationService.patch($scope.config);
            promise.then(function(){
                            MapasCulturais.Messages.success(labels['changesSaved']);
                        }, function(error){
                            console.log('error: ' + error);
                        });
        },true);

        $scope.agentRelationDisabledCD = MapasCulturais.agentRelationDisabledCD || [];

        $scope.findQuery = {
            type: 'EQ(1)',
            status: 'GT(0)',
            parent: 'NULL()'
        };

        $scope.$watch('committee',function(o,n){
            var ids = $scope.data.committee.map(function(e){ return e.agent.id; });
            if(ids.length > 0){
                $scope.findQuery.id = '!IN(' + (ids.join(',')) + ')';
            } else {
                delete $scope.findQuery.id;
            }
        },true);

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

        $scope.closeRenameGroupEditBox = function(){
            EditBox.close('rename-related-agent-group');
        };

        $scope.data.newGroupName = '';

        $scope.getCreateAgentRelationEditBoxId = function(groupName){
            return 'add-related-agent-' + groupName.replace(/[^a-z0-9_]/gi,'');
        };

        $scope.createGroup = function(){
            if($scope.data.newGroupName.trim() && !groupExists( $scope.data.newGroupName ) && $scope.data.newGroupName.toLowerCase().trim() !== 'registration' && $scope.data.newGroupName.toLowerCase().trim() !== 'group-admin' ){
                var newGroup = {name: $scope.data.newGroupName, relations: []};

                $scope.groups = [newGroup].concat($scope.groups);

                $scope.data.newGroupName = '';
                EditBox.close('new-related-agent-group');
            }
        };

        $scope.setRenameGroup = function(group){
            $scope.data.editGroup = {};
            angular.copy(group, $scope.data.editGroup);
            $scope.data.editGroupIndex = $scope.groups.indexOf(group);
        };

        $scope.renameGroup = function(e){
            if($scope.data.editGroup.name.trim() && !groupExists( $scope.data.editGroup.name ) && $scope.data.editGroup.name.toLowerCase().trim() !== 'registration' && $scope.data.editGroup.name.toLowerCase().trim() !== 'group-admin' ){
                RelatedAgentsService.renameGroup($scope.data.editGroup).success(function() {
                    angular.copy($scope.data.editGroup, $scope.groups[$scope.data.editGroupIndex]);
                    EditBox.close('rename-related-agent-group');
                });
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

        $scope.deleteGroup = function(group) {
            if (confirm(labels['confirmDeleteGroup'].replace('%s', group.name))) {
                var i = $scope.groups.indexOf(group);
                group.relations.forEach(function(relation){
                    //$scope.deleteRelation(relation);
                    RelatedAgentsService.remove(relation.group, relation.agent.id);
                });

                $scope.groups.splice(i,1);
            }
        };

        $scope.createAdminRelation = function(entity){
            var _scope = this.$parent;
            var isAgentRelation = false;
            _scope.spinnerCondition = true;
            _scope.noEntityFound  = true;

            isAgentRelation = $scope.data.committee.some( function(item) { return item.agent.id === entity.id; });

            if (!isAgentRelation) {
                RelatedAgentsService.create('group-admin', entity.id, true).success(function(data) {
                    $scope.data.committee.push(data);
                    _scope.spinnerCondition = false;
                    _scope.noEntityFound  = false;
                    _scope.$parent.searchText = '';
                    _scope.$parent.result = [];
                    EditBox.close('add-committee-agent');
                });
            } else {
                _scope.spinnerCondition = false;
                _scope.noEntityFound  = false;
                MapasCulturais.Messages.error(labels['agentRelationIsAlreadyExists']);
            }
        };

        $scope.reopenEvaluations = function(relation){
            if(confirm(labels.confirmReopenValuerEvaluations)){
                relation.status = 1;
    
                EvaluationMethodConfigurationService.reopenValuerEvaluations(relation).
                    error(function(){
                        relation.status = 10;
                    });
            }
        };

        $scope.deleteAdminRelation = function(relation){
            if(confirm(labels.confirmRemoveValuer)){
                RelatedAgentsService.remove('group-admin', relation.agent.id).
                    success(function(){
                        var i = $scope.data.committee.findIndex(function(el){
                            return el.id == relation.id;
                        });
                        $scope.data.committee.splice(i,1);
                    });
            }
        };

        $scope.disableAdminRelation = function(relation){
            relation.hasControl = false;
            RelatedAgentsService.removeControl(relation.agent.id).
                        error(function(){
                            relation.hasControl = true;
                        });
        };

        $scope.enableAdminRelation = function(relation){
            relation.hasControl = true;
            RelatedAgentsService.giveControl(relation.agent.id).
                        error(function(){
                            relation.hasControl = false;
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

    module.factory('OpportunityApiService',['$http', 'UrlService', function($http, UrlService) {
        var us = new UrlService('api/opportunity');
        
        return function($scope, varname, endpoint, params){
            var url = us.create(endpoint);
            var page = 1;
            var meta_key = varname + 'APIMetadata';
            $scope.data = $scope.data || {};
            $scope.data[varname] = [];
            $scope.data[varname+'APIMetadata'] = {};
            $scope.data[varname + '_lastpage'] = false;

            if(!params['@limit']){
                params['@limit'] = 50;
            }
            
            this.find = function(){
                params['@page'] = page;
                page++;
                
                return $http.get(url, {params: params, cache:true}).success(function(response, status, headers){
                    for (var i in response){
                        response[i]['files'] = {};
                        for(var prop in response[i]){
                            if(prop.indexOf('@files:') === 0){
                                response[i].files[prop.substr(7)] = response[i][prop];
                            }
                        }
                    }
                    var metadata = JSON.parse(headers()['api-metadata']);
                    
                    $scope.data[meta_key] = metadata;
                    $scope.data[varname] = $scope.data[varname].concat(response);
                });
                
            };

            this.finish = function(){
                var meta = $scope.data[meta_key];
                return meta.numPages && parseInt(meta.page) >= parseInt(meta.numPages);
            }
        }
    }]);

module.controller('OpportunityController', ['$scope', '$rootScope', '$timeout', 'RegistrationService', 'EditBox', 'RelatedAgentsService', '$http', 'UrlService', 'OpportunityApiService', '$window', function ($scope, $rootScope, $timeout, RegistrationService, EditBox, RelatedAgentsService, $http, UrlService, OpportunityApiService, $window) {
    var labels = MapasCulturais.gettext.moduleOpportunity;

    var opportunity_main_tab = $("#opportunity-main-info");
    if( $.trim($(opportunity_main_tab).text()).length === 0 ) {
        $(opportunity_main_tab).hide();
    }

    var select_fields = MapasCulturais.opportunitySelectFields.map(function(e){ return e.fieldName; });
    var registrationsApi;
    var evaluationsApi;

    var committeeApi = new OpportunityApiService($scope, 'evaluationCommittee', 'evaluationCommittee', {'@opportunity': getOpportunityId()});

    $scope.registrationsFilters = {};
    $scope.evaluationsFilters = {};

    $scope.isSelected = function(object, key){
        var selected  = false;
        for(var index in object) {
            if (key == index){
                selected =  object[key];
                break;
            }
        }
        return selected;
    };

    $scope.toggleSelection = function(object, key){
        var value  = true;
        for(var index in object) {
            if (key == index){
                value = !object[key];
                break;
            }
        }
        object[key] = value;
        return;
    };

    $scope.toggleSelectionColumn = function(object, key){

        $scope.toggleSelection(object, key);

        if ($scope.numberOfEnabledColumns() == 0) {
            object[key] = true;
            alert('Não é permitido desabilitar todas as colunas da tabela');
            return;
        }

        if (key == 'number' ) {
            var columnObj = $scope.getColumnByKey(key);
            object[key] = true;
            alert('Não é permitido desabilitar a coluna ' + columnObj.title);
            return;
        }

        return;
    };

    $scope.findRegistrations = function(){
        if(registrationsApi.finish()){
            return null;
        }
        $scope.data.findingRegistrations = true;
        return registrationsApi.find().success(function(){
            $scope.data.findingRegistrations = false;
        });
    }

    $scope.findEvaluations = function(){
         if(evaluationsApi.finish()){
            return null;
        }
        $scope.data.findingEvaluations = true;
        return evaluationsApi.find().success(function(){
            $scope.data.findingEvaluations = false;
        });
    }

    $scope.$watch('registrationsFilters', function(){
        var qdata = {
            'status': 'GT(-1)',
            '@files': '(zipArchive):url',
            '@opportunity': getOpportunityId(),
            '@select': 'id,singleUrl,category,status,owner.{id,name,singleUrl},consolidatedResult,evaluationResultString,' + select_fields.join(',')
        };
        for(var prop in $scope.registrationsFilters){
            if($scope.registrationsFilters[prop] || $scope.registrationsFilters[prop] === 0){
                qdata[prop] = 'EQ(' + $scope.registrationsFilters[prop] + ')'
            }
        }
        registrationsApi = new OpportunityApiService($scope, 'registrations', 'findRegistrations', qdata);
        $scope.findRegistrations();
    }, true);

    $scope.$watch('evaluationsFilters', function(){
        var qdata = {
            '@opportunity': getOpportunityId(),
            '@select': 'id,singleUrl,category,owner.{id,name,singleUrl},consolidatedResult,evaluationResultString,status,',
            '@order': 'evaluation desc'
        };
        for(var prop in $scope.evaluationsFilters){
            if($scope.evaluationsFilters[prop]){
                qdata[prop] = 'EQ(' + $scope.evaluationsFilters[prop] + ')'
            }
        }
        evaluationsApi = new OpportunityApiService($scope, 'evaluations', 'findEvaluations', qdata);
        $scope.findEvaluations();
    }, true);

    angular.element($window).bind("scroll", function(){
        // @TODO: refatorar este if
        if(document.location.hash.indexOf("tab=inscritos") >= 0){
            if(!$scope.data.findingRegistrations){
                if(document.body.offsetHeight - $window.pageYOffset <  $window.innerHeight){
                    $scope.findRegistrations();
                }
            }
        } else if (document.location.hash.indexOf("tab=evaluations") >= 0){
            if(!$scope.data.findingEvaluations){
                if(document.body.offsetHeight - $window.pageYOffset <  $window.innerHeight){
                    $scope.findEvaluations();
                }
            }
        }
    });

    var adjustingBoxPosition = false,
    categories = MapasCulturais.entity.registrationCategories.length ? MapasCulturais.entity.registrationCategories.map(function(e){
        return { value: e, label: e };
    }) : [];


    var defaultSelectFields = [
        {fieldName: "number", title:labels["Inscrição"] ,required:true},
        {fieldName: "category", title:labels['Categorias'] ,required:true},
        {fieldName: "agents", title:labels['Agentes'] ,required:true},
        {fieldName: "attachments", title:labels['Anexos']  ,required:true},
        {fieldName: "evaluation", title: labels['Avaliação'] ,required:true},
        {fieldName: "status", title:labels['Status'] ,required:true},
    ];

    MapasCulturais.opportunitySelectFields.forEach(function(e){
        e.options = [{ value: null, label: e.title }].concat(e.fieldOptions.map(function(e){
            return {value: e, label: e};
        }));
    });

    $scope.editbox = EditBox;

    $scope.confirmEvaluations = function () {
        MapasCulturais.confirm(labels['applyEvaluations'], function() {
            $scope.totalEvaluations().map(function(e) {
                var register = e.registration;
                var result = { value: parseInt(e.evaluation.result) };
                $scope.setRegistrationStatus( register, result, true );
            });
        });
    };

    $scope.applyEvaluations = function() {
        var _arr = [];
        $scope.totalEvaluations().map(function(e) {
            var result = parseInt(e.evaluation.result);
            _arr.push({ reg_id: e.registration.id, result: result });
        });

        RegistrationService.setMultipleStatus(_arr);
    };

    $scope.hasEvaluations = function() {
        return ($scope.totalEvaluations().length > 0);
    };

    $scope.totalEvaluations = function() {
        var total_evaluations = $scope.data.evaluations.filter(function(ev) {
            if(ev.evaluation && ev.evaluation != null) {
                return ev.evaluation;
            }
        });

        return total_evaluations;
    };

    $scope.data = angular.extend({
        uploadSpinner: false,
        spinner: false,

        evaluationCommittee: [],
        evaluationCommitteeAPIMetadata: {},

        registrationCategories: categories,
        registrationCategoriesToFilter: [{value: null, label: labels.allCategories}].concat(categories),
        evaluationStatusToFilter: [{value: "", label: labels['all']}, {value: 1, label: labels['evaluated']},{value: -1, label: labels['notEvaluated']}],
        registrations: [],
        registration: {
            owner: null,
            category: null,
            owner_default_label: labels['registrationOwnerDefault']
        },

        evaluationStatuses: [
            {value: null, label: labels['allStatus']},
            {value: -1, label: labels['pending']},
            {value: 1, label: labels['evaluated']},
            {value: 2, label: labels['sent']}
        ],

        registrationStatuses: RegistrationService.registrationStatuses,

        registrationStatusesNames: RegistrationService.registrationStatusesNames,

        publishedRegistrationStatuses: RegistrationService.publishedRegistrationStatuses,

        publishedRegistrationStatusesNames: RegistrationService.publishedRegistrationStatusesNames,

        publishedRegistrationStatus: 10,

        propLabels : [],

        defaultSelectFields : defaultSelectFields,

        registrationTableColumns: {
            number: true,
            category: true,
            agents: true,
            attachments: true,
            evaluation: true,
            status: true
        },

        confirmEvaluationLabel: labels['confirmEvaluationLabel'],

        fields: RegistrationService.getFields(),

        relationApiQuery: {'@keywowrd': '*'},

        fullscreenTable: false,

    }, MapasCulturais);

    committeeApi.find().success(function(result){
        $scope.data.evaluationCommittee = result.map(function(e){
            return {
                value: e.agent.id,
                label: e.agent.name
            };
        });
    })

    $scope.usingRegistrationsFilters = function(){
        var using = false;
        for(var i in $scope.registrationsFilters){
            if($scope.registrationsFilters[i]){
                using = true;
            }
        }
        return using;
    };

    $scope.usingEvaluationsFilters = function(){
        var using = false;
        for(var i in $scope.registrationsFilters){
            if($scope.registrationsFilters[i]){
                using = true;
            }
        }
        return using;
    };

    $scope.$watch('data.fullscreenTable', function(){
        var $t = $('#registrations-table-container');
        if($scope.data.fullscreenTable){
            $t.css('margin-left', - $t.offset().left );
            $t.css('width', document.body.offsetWidth);
        } else {
            $t.css('margin-left', 0);
            $t.css('width', '100%');
        }
    });

    $scope.getColumnByKey = function(key){
        for(var index in $scope.data.defaultSelectFields){
            if($scope.data.defaultSelectFields[index].fieldName == key ){
                return $scope.data.defaultSelectFields[index];
            }
        }

        return null;
    };

    $scope.numberOfEnabledColumns = function(){
        var result = 0;
        for(var prop in $scope.data.registrationTableColumns){
            if($scope.data.registrationTableColumns[prop]){
                result++;
            }
        }

        return result;
    };

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

    $scope.getEvaluationStatus = function(evaluation){
        if(angular.isObject(evaluation.evaluation)){
            return evaluation.evaluation;
        } else {
            return -1;
        }
    };

    $scope.getEvaluationStatusLabel = function(registration){
        var status;
        if(registration.valuer){
            if (registration.evaluation){
                status = registration.evaluation.status;
            } else {
                status = -1;
            }

        } else {
            status = $scope.getEvaluationStatus(registration);
        }

        var slugs = {
            '-1': 'notEvaluated',
            '0': 'draft',
            '1': 'evaluated',
            '2': 'sent'
        };
        var statusSlug = slugs[status];

        return labels[statusSlug];

    }

    $scope.getEvaluationResultString = function(registration){

        if(registration.evaluation){
            return registration.evaluation.resultString;
        } else {
            return labels['pending'];
        }
    }

    // EVALUATIONS - END


    $scope.getStatusSlug = function(status) {
        /*
            const STATUS_SENT = self::STATUS_ENABLED;
            const STATUS_APPROVED = 10;
            const STATUS_WAITLIST = 8;
            const STATUS_NOTAPPROVED = 3;
            const STATUS_INVALID = 2;
       */
        return _getStatusSlug(status);
    };

    $scope.getStatusNameById = function(id) {
        var statuses = $scope.data.registrationStatusesNames;
        for(var s in statuses){
            if(statuses[s].value == id)
                return statuses[s].label;
        }
    };

    $scope.approvedRegistrations = function(){

        var registrations = $scope.data.registrations,
        approved = 0;

        for(var key in registrations){
            if(registrations[key].hasOwnProperty('status') && registrations[key].status === 10) {
                approved++;
            }
        }

        return approved;
    };


    $scope.setRegistrationStatus = function(registration, status, is_bulk) {
        var can = false;
        if(MapasCulturais.request.controller === 'opportunity'){
            can = MapasCulturais.entity.userHasControl;
        } else if(MapasCulturais.request.controller === 'registration') {
            can = registration.id === MapasCulturais.entity.id;
        }
        if(can && (status.value !== 0 || confirm(labels['confirmReopen']))) {
            var slug = $scope.getStatusSlug(status.value);

            RegistrationService.setStatusTo(registration, slug).success(function(entity) {
                if(registration.status === 0){
                    $scope.data.registrations.splice($scope.data.registrations.indexOf(registration),1);
                }
            });

            if(is_bulk) {
                $("#registration-" +  registration.id).attr('class', slug);
                var t = $("#registration-" +  registration.id + " .registration-status-col").first().text();
                $("#registration-" +  registration.id + " .registration-status-col .dropdown.js-dropdown div").text(t);
            }
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

                RegistrationService.save();
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
                        MapasCulturais.Messages.success(labels['changesSaved']);
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
                    $('#find-entity-registration-owner').trigger('find',0);
            });

            $scope.register = function(){                
                var registration = $scope.data.registration;                
                var ownerRegistration = [];
                // @TODO: buscar na api
                for(var i in $scope.data.registrations) {
                    if(registration.owner && $scope.data.registrations[i].owner){
                        if($scope.data.registrations[i].owner.id == registration.owner.id) {
                            ownerRegistration.push($scope.data.registrations[i].owner);
                        }
                    }
                }

                if(MapasCulturais.entity.object.registrationLimitPerOwner > 0 && ownerRegistration.length >= MapasCulturais.entity.object.registrationLimitPerOwner) {
                    MapasCulturais.Messages.error(labels['limitReached']);
                }else if(MapasCulturais.entity.object.registrationLimit > 0 && registration.owner && $scope.data.registrations.length >= MapasCulturais.entity.object.registrationLimit){
                    MapasCulturais.Messages.error(labels['VacanciesOver']);
                }else if(registration.owner && (MapasCulturais.entity.object.registrationLimit == 0 || $scope.data.registrations.length <= MapasCulturais.entity.object.registrationLimit)){
                    RegistrationService.register(registration).success(function(rs){
                        document.location = rs.editUrl;
                    });
                }else {
                    setTimeout(function(){
                        $('#select-registration-owner-button').trigger("click");
                    }, 0);
                    MapasCulturais.Messages.error(labels['needResponsible']);
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
                            if(field === 'projectName'){
                                $el = $('#projectName').parent().find('.label');
                            }else if(field === 'category'){
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
                        MapasCulturais.Messages.error(labels['correctErrors']);
                    }else{
                        MapasCulturais.Messages.success(labels['registrationSent']);
                        document.location = response.singleUrl;
                    }
                });
            };

            var url = new UrlService('opportunity');

            $scope.publish = function(){
                $http.post(url.create('publish', $scope.data.entity.id)).
                success(function(r){
                    alert('publicado');
                }).error(function(r){
                    alert('erro');
                });
            };

            $timeout(function() {
                //Se não existir agentes registrado ao carregar o modúlo, adiciona o agente padrão ao registro.
                //if (!MapasCulturais.entity.registrationAgents) {
                //    $scope.setRegistrationOwner(MapasCulturais.userProfile);
                //}
            });

        }]);

    module.controller('RegistrationListController', ['$scope', '$interval', 'OpportunityApiService', function($scope, $timeout, OpportunityApiService){
        
        $scope.registrations = [];
        $scope.evaluations = {};
        $scope.data = {
            keyword: '',
            current: MapasCulturais.registration.id,
            keywords: [],
            pending: false,
            registrations: [],
            evaluations: []
        }

        var registrationsApi = new OpportunityApiService($scope, 'registrations', 'findRegistrations', {
            '@opportunity': getOpportunityId(),
            '@limit': 10000,
            '@select': 'id,singleUrl,owner.{id,name}'
        });

        var evaluationsApi = new OpportunityApiService($scope, 'evaluations', 'findEvaluations', {
            '@opportunity': getOpportunityId(),
            '@limit': 10000,
            '@select': 'id,singleUrl,category,owner.{id,name,singleUrl},consolidatedResult,evaluationResultString,status,'
        });

        registrationsApi.find().success(function(){
            $scope.registrations = $scope.data.registrations;
        });

        evaluationsApi.find().success(function(){
            $scope.data.evaluations.forEach(function(e){
                $scope.evaluations[e.registration.id] = e.evaluation;
            });
        });

        var last = '';

        $scope.$watch('data.keyword', function(o,n){
            var lower = $scope.data.keyword.toLowerCase();
            if(lower != last){
                last = lower;
                $scope.data.keywords = lower.split('*');
            }
        });

        $scope.evaluated = function(registration){
            return  $scope.evaluations[registration.id] && $scope.evaluations[registration.id].result !== null;
        };

			var labels = MapasCulturais.gettext.moduleOpportunity;
			$scope.status_str = function(registration) {
            return this.evaluated(registration) ? $scope.evaluations[registration.id].resultString : labels['pending'];
        };

        $scope.getEvaluationResult = function(registration) {

            if($scope.evaluations[registration.id] == null){
                return 0;
            }
            return $scope.evaluations[registration.id].result;
        };

        $scope.show = function(registration){
            if(registration.status === 0){
                return false;
            }
            var ks = $scope.data.keywords;
            var result = false;
            if(ks.length > 0){
                for(var i in ks){
                    var k = ks[i];
                    if(k == registration.number || k == registration.id || registration.owner.name.toLowerCase().indexOf(k) >= 0){
                        result = true;
                    }

                    if($scope.evaluations[registration.id] && $scope.evaluations[registration.id].resultString.toLowerCase() == k){
                        result = true;
                    }
                }
            } else {
                result = true;
            }

            if($scope.data.pending){
                if($scope.evaluated(registration)){
                    result = false;
                }
            }

            return result;
        }
    }]);

module.controller('SealsController', ['$scope', '$rootScope', 'RelatedSealsService', 'EditBox', function($scope, $rootScope, RelatedSealsService, EditBox) {
    $scope.editbox = EditBox;

    $scope.seals = [];

    for(var i in MapasCulturais.allowedSeals)
        $scope.seals.push(MapasCulturais.allowedSeals[i]);

    $scope.registrationSeals = [];

    $scope.showCreateDialog = {};

    $scope.isEditable = MapasCulturais.isEditable;

    $scope.data = {};

    $scope.avatarUrl = function(url){
        if(url) {
            return url;
        } else {
            return MapasCulturais.assets.avatarSeal;
        }
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

        if(!angular.isObject($scope.entity.registrationSeals)) {
            $scope.entity.registrationSeals = {};
        }
        $scope.entity.registrationSeals[agent] =  entity.id;
        sealRelated = $scope.entity.registrationSeals;
        jQuery("#registrationSeals").editable('setValue',sealRelated);

        $scope.registrationSeals.push(sealRelated);
        EditBox.close('set-seal-' + agent);
    };

    $scope.getArrIndexBySealId = function(sealId) {
        for(var found in $scope.seals) {
            if($scope.seals[found].id == sealId)
                return found;
        };
    };

    $scope.removeSeal = function(entity){
        delete $scope.entity.registrationSeals[entity];
    };

    $scope.deleteRelation = function(relation){
        RelatedSealsService.remove(relation.seal.id).error(function(){
            relations = oldRelations;
        });
    };

    // Load relatedSeals saved values
    jQuery("#registrationSeals").editable('setValue',$scope.entity.registrationSeals);

}]);

})(angular);
