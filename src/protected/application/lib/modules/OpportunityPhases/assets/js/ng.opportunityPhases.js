(function (angular) {
    "use strict";

    var module = angular.module('OpportunityPhases', ['ngSanitize']);

    module.config(['$httpProvider', function ($httpProvider) {
            $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
            $httpProvider.defaults.transformRequest = function (data) {
                var result = angular.isObject(data) && String(data) !== '[object File]' ? $.param(data) : data;

                return result;
            };
        }]);

    module.factory('OpportunityPhasesService', ['$http', '$rootScope', function ($http, $rootScope) {
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

    module.controller('OpportunityPhasesController', ['$scope', '$rootScope', '$timeout', 'OpportunityPhasesService', 'EditBox', function ($scope, $rootScope, $timeout, OpportunityPhasesService, EditBox) {
            $scope.editbox = EditBox;
            $scope.data = {
                spinner: false,
                step: 1,
            };

            $scope.newPhaseEditBoxSubmit = function () {
                alert('submit');
                if($scope.data.step == 1) {
                    $scope.data.step++;
                } else {
                    alert('submit');
                }
            }

            $scope.newPhaseEditBoxCancel = function () {
                alert('cancelar');
                $scope.data.step = 1;
            }

        }]);
})(angular);