(function (angular) {
    "use strict";
    var module = angular.module('ng.evaluationMethod.simple', []);
    

    module.config(['$httpProvider', function ($httpProvider) {
        $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
        $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
        $httpProvider.defaults.transformRequest = function (data) {
            var result = angular.isObject(data) && String(data) !== '[object File]' ? $.param(data) : data;

            return result;
        };
    }]);

    module.controller('SimpleEvaluationForm',['$scope', 'RegistrationService',function($scope, RegistrationService){
        var evaluation = MapasCulturais.evaluation;
        var statuses = RegistrationService.registrationStatusesNames.filter(function(status) {
            if(status.value > 1) return status;
        });
        $scope.data = {
            registration: evaluation ? evaluation.evaluationData.status : null,
            obs: evaluation ? evaluation.evaluationData.obs : null,
            registrationStatusesNames: statuses,

        };

        $scope.getStatusLabel = function(status){
            for(var i in statuses){
                if(statuses[i].value == status){
                    return statuses[i].label;
                }
            }
            return '';
        };
    }]);


    module.factory('ApplySimpleEvaluationService', ['$http', '$rootScope', 'UrlService', function ($http, $rootScope, UrlService) {
        
        return {
            apply: function (from, to, status) {
                var data = {from: from, to: to, status: status};
                var url = MapasCulturais.createUrl('opportunity', 'applyEvaluationsSimple', [MapasCulturais.entity.id]);
                
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

    module.controller('ApplySimpleEvaluationResults',['$scope', 'RegistrationService', 'ApplySimpleEvaluationService', 'EditBox', function($scope, RegistrationService, ApplySimpleEvaluationService, EditBox){
        
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
            ApplySimpleEvaluationService.apply($scope.data.applyFrom, $scope.data.applyTo, $scope.data.status).
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
})(angular);