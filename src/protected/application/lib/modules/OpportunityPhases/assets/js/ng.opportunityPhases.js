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
                createPhase: function (param) {

                    var opportunity_id = MapasCulturais.entity.object.parent ? MapasCulturais.entity.object.parent.id : MapasCulturais.entity.object.id;

                    var url = MapasCulturais.createUrl('opportunity', 'createNextPhase', {id: opportunity_id });

                    return $http.post(url, param).
                            success(function (data, status) {
                                $rootScope.$emit('something', {message: "Something was done", data: data, status: status});
                            }).
                            error(function (data, status) {
                                $rootScope.$emit('error', {message: "Cannot do something", data: data, status: status});
                            });
                }
            };
        }]);

    module.controller('OpportunityPhasesController', ['$scope', '$window', '$rootScope', '$timeout', 'OpportunityPhasesService', 'EditBox', function ($scope, $window, $rootScope, $timeout, OpportunityPhasesService, EditBox) {
            $scope.editbox = EditBox;
            $scope.data = {
                spinner: false,
                step: 1,
            };

            $scope.newPhasePostData = {
                evaluationMethod: null,
                isLastPhase: '',
            };

            $scope.newPhaseEditBoxSubmit = function () {

                if($scope.data.step == 1) {
                    $scope.data.step++;
                } else {
                    $scope.data.spinner = true;
                    OpportunityPhasesService.createPhase($scope.newPhasePostData).success(function(result){
                        $scope.data.spinner = false;
                        $window.location = result.editUrl;
                    }).error(function(err) {
                        $scope.data.spinner = false;
                        MapasCulturais.Messages.error(err.data);
                    });
                }
            }

            $scope.newPhaseEditBoxCancel = function () {
                $scope.data.spinner = false;
                $scope.data.step = 1;
                $scope.newPhasePostData.evaluationMethod = null;
            }

        }]);
})(angular);