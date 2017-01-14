(function(angular){
    "use strict";

    var app = angular.module('entity.app', MapasCulturais.angularAppDependencies);

    app.factory('UrlService', [function(){
        return function(controller){
            this.create = function(action, params){
                if(params == parseInt(params)){ // params is an integer, so it is an id
                    return MapasCulturais.createUrl(controller, action, [params]);
                }else{
                    return MapasCulturais.createUrl(controller, action, params);
                }
            };
        };
    }]);

    app.controller('EntityController',['$scope', '$timeout', function($scope, $timeout){
        $scope.data = {
            teste: 'ALALALALALALA'
        }
    }]);

    app.controller('CompliantController',['$scope', '$timeout', 'CompliantService',function($scope, $timeout, CompliantService){
        $scope.compliant_type = MapasCulturais.notification_type.compliant_type.config.options;
        $scope.send = function( ) {

            var name        = $scope.data.name;
            var email       = $scope.data.email;
            var type        = $scope.data.type;
            var anonimous   = $scope.data.anonimous;
            var only_owner  = $scope.data.only_owner;
            var message     = $scope.data.message;
            var copy        = $scope.data.copy;
            MapasCulturais.compliant_ok = true;

            if(anonimous && !email){
                MapasCulturais.Messages.error('O preenchimento do e-mail é obrigatório.');
                MapasCulturais.compliant_ok = false;
            } else if(!type){
                MapasCulturais.Messages.error('O preenchimento do tipo de denúncia é obrigatório.');
                MapasCulturais.compliant_ok = false;
            } else if(!message){
                MapasCulturais.Messages.error('O preenchimento da mensagem da denúncia é obrigatório.');
                MapasCulturais.compliant_ok = false;
            }

            if(MapasCulturais.compliant_ok) {
                CompliantService.send(name,email,type,anonimous,only_owner,message,copy).
                    success(function (data) {});
            }
        }
    }]);

    app.factory('CompliantService', ['$http', '$rootScope', function($http, $rootScope){
        var controllerId = null,
            entityId = null,
            baseUrl = MapasCulturais.baseURL.substr(-1) === '/' ?  MapasCulturais.baseURL : MapasCulturais.baseURL + '/';

        try{ controllerId = MapasCulturais.request.controller; }catch (e){};
        try{ entityId = MapasCulturais.entity.id; }catch (e){};

        return {
            controllerId: controllerId,

            entityId: entityId,

            getUrl: function(action){
                return baseUrl + controllerId + "/" + action;
            },

            send: function(name,email,type,anonimous,only_owner,message,copy) {
                return $http.post(this.getUrl('sendCompliantMessage'), {name: name,email: email,type: type,anonimous: anonimous,only_owner: only_owner,message: message, entityId: this.entityId, copy: copy}).
                        success(function(data, status){
                            if(status === 202){
                                MapasCulturais.Messages.alert('Sua requisição para enviar uma denúncia foi enviada com sucesso.');
                            }
                            $rootScope.$emit('sendCompliantMessage.created', data);
                        }).
                        error(function(data, status){
                            $rootScope.$emit('error', { message: "Cannot send compliant message", data: data, status: status });
                        });
            }
        };
    }]);

    app.controller('SuggestionController',['$scope', '$timeout', 'SuggestionService',function($scope, $timeout, SuggestionService){
        $scope.suggestion_type = MapasCulturais.notification_type.suggestion_type.config.options;
        $scope.send = function( ) {

            var name        = $scope.data.name;
            var email       = $scope.data.email;
            var type        = $scope.data.type;
            var anonimous   = $scope.data.anonimous;
            var only_owner  = $scope.data.only_owner;
            var message     = $scope.data.message;
            var copy        = $scope.data.copy;
            MapasCulturais.suggestion_ok = true;

            if(anonimous && !email){
                MapasCulturais.Messages.error('O preenchimento do e-mail é obrigatório.');
                MapasCulturais.suggestion_ok = false;
            } else if(!type){
                MapasCulturais.Messages.error('O preenchimento do tipo de mensagem é obrigatório.');
                MapasCulturais.suggestion_ok = false;
            } else if(!message){
                MapasCulturais.Messages.error('O preenchimento da mensagem do contato é obrigatório.');
                MapasCulturais.suggestion_ok = false;
            }

            if(MapasCulturais.compliant_ok) {
                SuggestionService.send(name,email,type,anonimous,only_owner,message,copy).
                    success(function (data) {});
            }
        }
    }]);

    app.factory('SuggestionService', ['$http', '$rootScope', function($http, $rootScope){
        var labels = MapasCulturais.gettext.entityApp;
        
        var controllerId = null,
            entityId = null,
            baseUrl = MapasCulturais.baseURL.substr(-1) === '/' ?  MapasCulturais.baseURL : MapasCulturais.baseURL + '/';

        try{ controllerId = MapasCulturais.request.controller; }catch (e){};
        try{ entityId = MapasCulturais.entity.id; }catch (e){};

        return {
            controllerId: controllerId,

            entityId: entityId,

            getUrl: function(action){
                return baseUrl + controllerId + "/" + action;
            },

            send: function(name,email,type,anonimous,only_owner,message, copy) {
                return $http.post(this.getUrl('sendSuggestionMessage'), {name: name,email: email,type: type,anonimous: anonimous,only_owner: only_owner,message: message, entityId: this.entityId, copy: copy}).
                        success(function(data, status){
                            if(status === 202){
                                MapasCulturais.Messages.alert(labels['requestSent']);
                            }
                            $rootScope.$emit('sendSuggestionMessage.created', data);
                        }).
                        error(function(data, status){
                            $rootScope.$emit('error', { message: "Cannot send Suggestion message", data: data, status: status });
                        });
            }
        };
    }]);

    app.directive('onRepeatDone', ['$rootScope', '$timeout', function($rootScope, $timeout) {
        return function($scope, element, attrs) {
            if ($scope.$last) {
                // só para esperar a renderização
                $timeout(function(){
                    $rootScope.$emit('repeatDone:' + attrs.onRepeatDone);
                });
            }
        };
    }]);


})(angular);
