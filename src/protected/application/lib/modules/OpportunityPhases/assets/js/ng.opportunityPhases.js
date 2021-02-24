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
                    var data = {
                        prop: name
                    };
                    var opportunity_id = MapasCulturais.entity.object.parent ? MapasCulturais.entity.object.parent.id : MapasCulturais.entity.object.id;

                    var url = MapasCulturais.createUrl('opportunity', 'createNextPhase', {id: opportunity_id, evaluationMethod: 'simple'});

                    return $http.post(url, data).
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

            $scope.newPhasePostData = {
                evaluationMethod: null
            };

            $scope.newPhaseEditBoxSubmit = function () {

                if($scope.data.step == 1) {
                    $scope.data.step++;
                } else {
                    $scope.data.spinner = true;
                    OpportunityPhasesService.createPhase().success(function(){
                        $scope.data.spinner = false;
                    }).error(function() {
                        $scope.data.spinner = false;
                        MapasCulturais.Messages.error('ERROR');
                    });
                }
            }

            $scope.newPhaseEditBoxCancel = function () {
                $scope.data.spinner = false;
                $scope.data.step = 1;
            }

        }]);
})(angular);