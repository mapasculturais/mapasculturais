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
        $scope.data = {
            
            registrationStatuses: RegistrationService.registrationStatuses,

            registrationStatusesNames: RegistrationService.registrationStatusesNames,

            publishedRegistrationStatuses: RegistrationService.publishedRegistrationStatuses,

            publishedRegistrationStatusesNames: RegistrationService.publishedRegistrationStatusesNames,
        };
    }]);
})(angular);