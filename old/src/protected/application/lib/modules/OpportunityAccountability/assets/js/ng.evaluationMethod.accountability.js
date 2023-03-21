(function (angular) {
    "use strict";
    var module = angular.module('ng.evaluationMethod.accountability', []);


    module.config(['$httpProvider', function ($httpProvider) {
        $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
        $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
        $httpProvider.defaults.transformRequest = function (data) {
            var result = angular.isObject(data) && String(data) !== '[object File]' ? $.param(data) : data;

            return result;
        };
    }]);


    module.factory('ApplyAccountabilityEvaluationService', ['$http', '$rootScope', 'UrlService', function ($http, $rootScope, UrlService) {

        return {
            apply: function (from, to, status) {
                var data = {from: from, to: to, status};
                var url = MapasCulturais.createUrl('opportunity', 'applyEvaluationsAccountability', [MapasCulturais.entity.id]);

                return $http.post(url, data).
                    success(function (data, status) {
                        $rootScope.$emit('registration.create', {message: "Opportunity registration was created", data: data, status: status});
                    }).
                    error(function (data, status) {
                        $rootScope.$emit('error', {message: "Cannot create opportunity registration", data: data, status: status});
                    });
            },
        };
    }]);

    module.controller('ApplyAccountabilityEvaluationResults',['$scope', 'RegistrationService', 'ApplyAccountabilityEvaluationService', 'EditBox', function($scope, RegistrationService, ApplyAccountabilityEvaluationService, EditBox){

        var evaluation = MapasCulturais.evaluation;
        var statuses = RegistrationService.registrationStatusesNames.filter((status) => {
            if(status.value > 1) return status;
        });
        $scope.data = {
            registration: evaluation ? evaluation.evaluationData.status : null,
            obs: evaluation ? evaluation.evaluationData.obs : null,
            registrationStatusesNames: statuses,
            applying: false,
            status: 'pending'
        };

        $scope.getStatusLabel = (status) => {
            for(var i in statuses){
                if(statuses[i].value == status){
                    return statuses[i].label;
                }
            }
            return '';
        };

        $scope.applyEvaluations = () => {
            if(!$scope.data.applyFrom || !$scope.data.applyTo) {
                // @todo: utilizar texto localizado
                MapasCulturais.Messages.error("É necessário selecionar os campos Avaliação e Status");
                return;
            }

            $scope.data.applying = true;
            ApplyAccountabilityEvaluationService.apply($scope.data.applyFrom, $scope.data.applyTo, $scope.data.status).
                success(() => {
                    $scope.data.applying = false;
                    MapasCulturais.Messages.success('Avaliações aplicadas com sucesso');
                    EditBox.close('apply-consolidated-results-editbox');
                    $scope.data.applyFrom = null;
                    $scope.data.applyTo = null;
                }).
                error((data, status) => {
                    $scope.data.applying = false;
                    $scope.data.errorMessage = data.data;
                    MapasCulturais.Messages.success('As avaliações não foram aplicadas.');
                })
        }
    }]);

    module.factory('AccountabilityEvaluationService', ['$http', '$rootScope', 'UrlService', function ($http, $rootScope, UrlService) {

        return {
            reopen: function (registrationId, evaluationData, uid) {
                var url = MapasCulturais.createUrl("registration", "saveEvaluation", {id: registrationId, status: "draft"});
                return $http.post(url, {data: evaluationData, uid});
            },

            save: function (registrationId, evaluationData, uid) {
                var url = MapasCulturais.createUrl('registration', 'saveEvaluation', [registrationId]);
                return $http.post(url, {data: evaluationData, uid});
            },

            send: function (registrationId, evaluationData, uid) {
                var url = MapasCulturais.createUrl('registration', 'saveEvaluation', {id: registrationId, status: 'evaluated'});
                return $http.post(url, {data: evaluationData, uid});
            },

            autoSave: function (registrationId, evaluationData, uid) {
                var url = MapasCulturais.createUrl('registration', 'saveEvaluation', {id: registrationId, status: 'draft'});
                return $http.post(url, {data: evaluationData, uid});
            },

            createChat: function (evaluation, identifier) {
                var url = MapasCulturais.createUrl('chatThread', 'createAccountabilityField');
                return $http.post(url, {evaluation, identifier});
            },

            closeChat: function (chat) {
                var url = MapasCulturais.createUrl('chatThread', 'close', [chat.id]);
                return $http.post(url);
            },

            openChat: function (chat) {
                var url = MapasCulturais.createUrl('chatThread', 'open', [chat.id]);
                return $http.post(url);
            },
            openField: function (registrationId, evaluationData, uid) {
                var url = MapasCulturais.createUrl('accountability', 'openField', [registrationId]);

                return $http.post(url, {data: evaluationData, uid});
            },
            closeField: function (registrationId, evaluationData, uid) {
                var url = MapasCulturais.createUrl('accountability', 'closeField', [registrationId]);

                return $http.post(url, {data: evaluationData, uid});
            },
        };
    }]);

    module.controller('AccountabilityEvaluationForm', ['$scope', '$rootScope', 'AccountabilityEvaluationService', function($scope, $rootScope, AccountabilityEvaluationService) {
        if(!MapasCulturais.evaluation) {
            return;
        }
        const evaluationId = MapasCulturais.evaluation.id;
        const registrationId = MapasCulturais.evaluation.registration.id;

        $scope.chatThreads = MapasCulturais.accountabilityChatThreads;
        $scope.openChats = {};
        $scope.openFields = {};

        $scope.accountabilityPermissions = MapasCulturais.accountabilityPermissions;
        $scope.evaluationData = MapasCulturais.evaluation.evaluationData;
        $scope.resultString = MapasCulturais.evaluation.resultString;

        $rootScope.closedChats = $rootScope.closedChats || {};

        Object.keys($scope.chatThreads).forEach(function(identifier) {
            const chat = $scope.chatThreads[identifier];

            $scope.openChats[identifier] = chat.status == 1;
            if(chat.status != 1) {
                $rootScope.closedChats[chat.id] = true;
            }
        });

        if (!$scope.accountabilityPermissions) {
            $scope.accountabilityPermissions = {};
        }

        Object.keys($scope.accountabilityPermissions).forEach(function (key) {
            $scope.openFields[key] = ($scope.accountabilityPermissions[key] == "true");
            return;
        });

        $scope.obsTimeOut = null;
        $scope.$watchGroup(['evaluationData.obs'], function(new_val, old_val) {
            if(new_val != old_val){
                clearTimeout($scope.obsTimeOut)               
                $scope.obsTimeOut = setTimeout(() => {
                    AccountabilityEvaluationService.autoSave(registrationId, $scope.evaluationData, MapasCulturais.evaluation.user).success(function () {
                        MapasCulturais.Messages.success('Salvo');
                    }).error(function (data) {
                        MapasCulturais.Messages.error(data.data[0]);
                    });
                }, 10000);
            }
            
        });

        $scope.$watchGroup(['evaluationData.result'], function(new_val, old_val) {
            if(new_val != old_val){
                AccountabilityEvaluationService.autoSave(registrationId, $scope.evaluationData, MapasCulturais.evaluation.user).success(function () {
                    MapasCulturais.Messages.success('Salvo');
                }).error(function (data) {
                    MapasCulturais.Messages.error(data.data[0]);
                });
            }
        });

        $scope.getFieldIdentifier = function(field) {
            
            if(!field){
                return "events";
            }

            return field.fieldName || field.groupName;
        }

        $scope.getChatByField = function (field) {
            if(!field){
                var _field = {'fieldName':'events'}
            }else{
                var _field = field
            }
            
            let identifier = $scope.getFieldIdentifier(_field);
            return this.chatThreads[identifier];
        }

        $scope.isChatOpen = function(field) {
            if(!field){
                var _field = {'fieldName':'events'}
            }else{
                var _field = field
            }
            let chat = this.getChatByField(_field)
            return !! chat && chat.status == 1;
        };

        $scope.chatExists = function(field) {
            return this.getChatByField(field) != undefined;
        };

        $scope.toggleOpen = function(field) {
            const identifier = $scope.getFieldIdentifier(field); 
            $scope.accountabilityPermissions = {};           
            $scope.accountabilityPermissions[identifier] = $scope.openFields[identifier]; 
            if($scope.openFields[identifier]){
                AccountabilityEvaluationService.openField(registrationId, $scope.accountabilityPermissions, MapasCulturais.evaluation.user).success(function (data) {
                    MapasCulturais.Messages.success('Campo aberto para edição');
                    return;
                });
            }else{
                AccountabilityEvaluationService.closeField(registrationId, $scope.accountabilityPermissions, MapasCulturais.evaluation.user).success(function (data) {
                    MapasCulturais.Messages.success('Campo fechado para edição');
                    return;
                });
            }  
            
        };

        $scope.toggleChat = function(field) {
            const identifier = $scope.getFieldIdentifier(field);
            if (!this.chatExists(field)) {
                AccountabilityEvaluationService.createChat(evaluationId, identifier).then(function(response) {
                    const newChatThread = response.data;
                    $scope.chatThreads[newChatThread.identifier] = newChatThread;
                });
            } else {
                const chat = this.getChatByField(field);
                if (this.isChatOpen(field)) {
                    AccountabilityEvaluationService.closeChat(chat).then(function(response) {
                        const chat = response.data;
                        $scope.chatThreads[chat.identifier] = chat;
                        $rootScope.closedChats[chat.id] = true;
                    });
                } else {
                    AccountabilityEvaluationService.openChat(chat).then(function(response) {
                        const chat = response.data;
                        $scope.chatThreads[chat.identifier] = chat;
                        delete $rootScope.closedChats[chat.id];
                    });
                }
            }
        };

        $scope.sendEvaluation = function () {
            if (!confirm("Você tem certeza que deseja finalizer o parecer técnico?\n\nApós a finalização não será mais possível modificar o parecer.")) {
                return;
            }

            AccountabilityEvaluationService.send(registrationId, $scope.evaluationData, MapasCulturais.evaluation.user).success(function () {
                MapasCulturais.Messages.success('Salvo');
                setTimeout(function () {
                    location.reload();
                    return;
                }, 500);
            }).error(function (data) {
                MapasCulturais.Messages.error(data.data[0]);
            });
        }

        $scope.reopenAccountability = function () {

            if(MapasCulturais.entity.isPublishedResult){
                MapasCulturais.Messages.error("Resultado já publicado para essa prestação de contas.");
                return;  
            }

            // TODO: i18n
            if (!confirm("Você tem certeza que deseja reabrir a prestação de contas?\n\nA abertura dos campos para edição deverá ser feita manualmente.")) {
                return;
            }
            AccountabilityEvaluationService.reopen(registrationId, $scope.evaluationData, MapasCulturais.evaluation.user).success(function () {
                MapasCulturais.Messages.success("Prestação de contas reaberta.");
                setTimeout(function () {
                    location.reload();
                    return;
                }, 500);
            });
        }

       setTimeout(() => {
            var container = document.getElementById("evaluation-editor");
            if(!container){
                return;
            }
            
            var editor = new Quill(container, {
                modules: { 
                    toolbar: [
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'color': [] }, { 'background': [] }],
                    ],
                },
                theme: 'snow'
            });
            
            editor.on('text-change', function(){
                $scope.evaluationData.obs = editor.root.innerHTML;
                $scope.$apply();
            });
       },1000);
    }]);

    module.controller('OpportunityAccountability', ['$scope', function ($scope) {
        $scope.canUserEdit = function (field) {
            if (MapasCulturais.entity.registrationStatus == 0) {
                return true;
            }
            var ref = (field.fieldType == "file") ? field.groupName : field.fieldName;
            if (MapasCulturais.accountabilityPermissions[ref] === "true") {
               return true;
            }
            return false;
        }
    }]);

    module.factory('PublishedResultRegistrationService', ['$http', '$rootScope', 'UrlService', function ($http, $rootScope, UrlService) {

        return {           
            publishedResult: function (data) {
                var url = MapasCulturais.createUrl('accountability', 'publishedResult', {opportunity_id: MapasCulturais.entity.id});

                return $http.post(url, {registrationId:data}).
                success(function (data, status, headers) {
                    $rootScope.$emit('registration.create', {message: "Accountability found", data: data, status: status});
                }).
                error(function (data, status) {
                    $rootScope.$emit('error', {message: "Accountability not found for this opportunity", data: data, status: status});
                });
            },
            checkPublishedResult: function (data) {
                var url = MapasCulturais.createUrl('accountability', 'checkRegistrationPublished', {opportunity_id: MapasCulturais.entity.id});

                return $http.get(url, data).
                success(function (data, status, headers) {
                    $rootScope.$emit('registration.create', {message: "Accountability found", data: data, status: status});
                }).
                error(function (data, status) {
                    $rootScope.$emit('error', {message: "Accountability not found for this opportunity", data: data, status: status});
                });
            },
        };
    }]);
    

    module.controller('publishedResultRegistration', ['$scope', '$rootScope', 'PublishedResultRegistrationService', function($scope, $rootScope, PublishedResultRegistrationService) {
        
        $scope.isPublishedResult = MapasCulturais.accountability.isPublishedResult;
        $scope.published = false;

        $scope.isPublished = function(registrationId){
            
            if(MapasCulturais.entity.published){
                return true;
            }

            return $scope.isPublishedResult.includes(registrationId);
        }
        
        $scope.publishedResult = function(registration){
            if (!confirm("ATENÇÃO, essa ação é irreversível! Você tem certeza que deseja publicar o resultado dessa inscrição?")) {
                return;
            }

            PublishedResultRegistrationService.publishedResult(registration.id).success(function (data) {
                $scope.published = true;
                MapasCulturais.Messages.success('Resultado da inscrição publicado');
            });
        }
    }]);

    module.controller('accountabilityDate', ['$scope', '$rootScope', function($scope, $rootScope) {
        
        $scope.getSentAccountability = function(registrationId){
            var accountabilityDates = MapasCulturais.accountability.dates;
            return accountabilityDates[registrationId];                     
        }
        
    }]);

})(angular);