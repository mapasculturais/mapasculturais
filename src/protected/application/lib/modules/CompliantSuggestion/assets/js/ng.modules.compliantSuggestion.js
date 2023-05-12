(function(angular){
    var app = angular.module('module.compliantSuggestion', []);
    
    app.controller('CompliantController',['$scope', '$timeout', 'CompliantService',function($scope, $timeout, CompliantService){

        var captcha = (data) => {
           $scope.data.googleRecaptchaToken = data;
        };
        window.captcha = captcha;

        var labels = MapasCulturais.gettext.compliantSuggestion;
        $scope.compliant_type = MapasCulturais.notification_type.compliant_type.config.options;
        $scope.send = function() {

            var grecaptcha  = $scope.data.googleRecaptchaToken;
            var name        = $scope.data.name;
            var email       = $scope.data.email;
            var type        = $scope.data.type;
            var anonimous   = $scope.data.anonimous;
            var only_owner  = $scope.data.only_owner;
            var message     = $scope.data.message;
            var copy        = $scope.data.copy;
            MapasCulturais.compliant_ok = true;

            if(!anonimous || copy && !email){
                MapasCulturais.Messages.error( labels.compliantEmailRequired );
                MapasCulturais.compliant_ok = false;
            } else if(!type){
                MapasCulturais.Messages.error( labels.compliantTypeRequired );
                MapasCulturais.compliant_ok = false;
            } else if(!message){
                MapasCulturais.Messages.error( labels.compliantMessageRequired );
                MapasCulturais.compliant_ok = false;
            } else if(MapasCulturais.complaintSuggestionConfig.recaptcha.sitekey && !grecaptcha){
                MapasCulturais.Messages.error( labels.recaptchaRequired );
                MapasCulturais.compliant_ok = false;
            }

            if(MapasCulturais.compliant_ok) {
            $scope.data.compliantStatus = 'sending';
                CompliantService.send(name,email,type,anonimous,only_owner,message,copy,grecaptcha).
                    success(function (data) {
                        $scope.data.compliantStatus = null;
                        $scope.data.showForm = false;
                        MapasCulturais.Messages.success(labels.compliantSent);
                    }).error(function(data){
                        $scope.data.compliantStatus = null;
                        $scope.data.showForm = false;                      
                        MapasCulturais.Messages.error(labels.error);
                    });
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

            send: function(name,email,type,anonimous,only_owner,message,copy,grecaptcha) {
                return $http.post(this.getUrl('sendCompliantMessage'), {name: name,email: email,type: type,anonimous: anonimous,only_owner: only_owner,message: message, entityId: this.entityId, copy: copy, 'g-recaptcha-response' : grecaptcha}).
                        success(function(data, status){
                            $rootScope.$emit('sendCompliantMessage.created', data);
                        }).
                        error(function(data, status){
                            $rootScope.$emit('error', { message: "Cannot send compliant message", data: data, status: status });
                        });
            }
        };
    }]);

    app.controller('SuggestionController',['$scope', '$timeout', 'SuggestionService',function($scope, $timeout, SuggestionService){
        
        var captchasuggestion = (data) => {
            $scope.data.googleRecaptchaToken = data;
        }; 
        window.captchasuggestion = captchasuggestion;

        var labels = MapasCulturais.gettext.compliantSuggestion;
        $scope.suggestion_type = MapasCulturais.notification_type.suggestion_type.config.options;
        $scope.send = function( ) {
            
            var grecaptcha  = $scope.data.googleRecaptchaToken
            var name        = $scope.data.name;
            var email       = $scope.data.email;
            var type        = $scope.data.type;
            var anonimous   = $scope.data.anonimous;
            var only_owner  = $scope.data.only_owner;
            var message     = $scope.data.message;
            var copy        = $scope.data.copy;
            MapasCulturais.suggestion_ok = true;

            if(anonimous){
                email = '';
                name = '';
            }

            if(!anonimous || copy && !email){
                MapasCulturais.Messages.error( labels.suggestionEmailRequired );
                MapasCulturais.suggestion_ok = false;
            } else if(!type){
                MapasCulturais.Messages.error( labels.suggestionTypeRequired );
                MapasCulturais.suggestion_ok = false;
            } else if(!message){
                MapasCulturais.Messages.error( labels.suggestionMessageRequired );
                MapasCulturais.suggestion_ok = false;
            } else if(MapasCulturais.complaintSuggestionConfig.recaptcha.sitekey && !grecaptcha){
                MapasCulturais.Messages.error( labels.recaptchaRequired );
                MapasCulturais.suggestion_ok = false;
            }

            if(MapasCulturais.suggestion_ok) {
                $scope.data.suggestionStatus = 'sending';
                
                SuggestionService.send(name,email,type,anonimous,only_owner,message,copy,grecaptcha).
                    success(function (data) {
                        $scope.data.suggestionStatus = null;
                        $scope.data.showForm = false;
                        MapasCulturais.Messages.success(labels.suggestionSent);
                    }).error(function(data){
                        $scope.data.suggestionStatus = null;
                        $scope.data.showForm = false;                      
                        MapasCulturais.Messages.error(labels.error);
                    });
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

            send: function(name,email,type,anonimous,only_owner,message, copy, grecaptcha) {
                return $http.post(this.getUrl('sendSuggestionMessage'), {name: name,email: email,type: type,anonimous: anonimous,only_owner: only_owner,message: message, entityId: this.entityId, copy: copy,'g-recaptcha-response':grecaptcha}).
                        success(function(data, status){
                            if(status === 202){
                                MapasCulturais.Messages.alert(labels['requestSent']);
                            }
                            $rootScope.$emit('sendSuggestionMessage.created', data, status);
                        }).
                        error(function(data, status){
                            $rootScope.$emit('error', { message: "Cannot send Suggestion message", data: data, status: status });
                        });
            }
        };
    }]);

})(angular);