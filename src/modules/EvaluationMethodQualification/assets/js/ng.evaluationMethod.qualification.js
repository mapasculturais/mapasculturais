(function (angular) {
    "use strict";

    var module = angular.module('ng.evaluationMethod.qualification', ['ngSanitize']);

    module.config(['$httpProvider', function ($httpProvider) {
        $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
        $httpProvider.defaults.headers.patch['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
        $httpProvider.defaults.transformRequest = function (data) {
            var result = angular.isObject(data) && String(data) !== '[object File]' ? $.param(data) : data;

            return result;
        };
    }]);

    module.factory('QualificationEvaluationMethodService', ['$http', '$rootScope', function ($http, $rootScope) {
        return {
            serviceProperty: null,
            getEvaluationMethodConfigurationUrl: function () {
                return MapasCulturais.createUrl('evaluationMethodConfiguration', 'single', [MapasCulturais.evaluationConfiguration.id]);
            },
            patchEvaluationMethodConfiguration: function (entity) {
                entity = JSON.parse(angular.toJson(entity));
                return $http.patch(this.getEvaluationMethodConfigurationUrl(), entity);
            }
        };
    }]);

    module.controller('QualificationEvaluationMethodConfigurationController', ['$scope', '$rootScope', '$timeout', 'QualificationEvaluationMethodService', 'EditBox', function ($scope, $rootScope, $timeout, QualificationEvaluationMethodService, EditBox) {
        $scope.editbox = EditBox;

        var labels = MapasCulturais.gettext.qualificationEvaluationMethod;

        if (MapasCulturais.evaluationConfiguration && MapasCulturais.evaluationConfiguration.criteria) {
            MapasCulturais.evaluationConfiguration.criteria = MapasCulturais.evaluationConfiguration.criteria.map(function (e) {
                e.weight = parseFloat(e.weight);
                return e;
            });
        }

        $scope.data = {
            sections: MapasCulturais.evaluationConfiguration.sections || [],
            criteria: MapasCulturais.evaluationConfiguration.criteria || [],
            options: {},
            debounce: 2000
        };

        MapasCulturais.evaluationConfiguration.criteria?.forEach(function (values, index) {
            $scope.data.options[values.id] = values.options?.join("\n")
            $scope.data.criteria[index].notApplyOption = (values.notApplyOption == "true") ? true : false; 
        })

        $scope.save = function () {
            var data = {
                sections: $scope.data.sections,
                criteria: [],
                enableViability: $scope.data.enableViability,
            };
            $scope.data.criteria.forEach(function (crit, index) {
                for (var i in data.sections) {
                    var section = data.sections[i];
                    if (crit.sid == section.id) {
                        data.criteria.push(crit);
                    }
                }

                if($scope.data.options[crit.id]){
                    data.criteria[index].options = $scope.data.options[crit.id]?.split("\n")
                }
            });

            QualificationEvaluationMethodService.patchEvaluationMethodConfiguration(data).success(function () {
                MapasCulturais.Messages.success(labels.changesSaved);
                $scope.data.sections = data.sections;
                $scope.data.criteria = data.criteria;
                $scope.data.enableViability = data.enableViability;
            });
        };

        $scope.addSection = function () {
            var date = new Date;
            var new_id = 's-' + date.getTime();
            $scope.data.sections.push({ id: new_id, name: '' });

            $timeout(function () {
                jQuery('#' + new_id + ' header input').focus();
            }, 1);
        };

        $scope.deleteSection = function (section) {
            if (!confirm(labels.deleteSectionConfirmation)) {
                return;
            }
            var index = $scope.data.sections.indexOf(section);

            $scope.data.criteria = $scope.data.criteria.filter(function (cri) {
                if (cri.sid != section.id) {
                    return cri;
                }
            });

            $scope.data.sections.splice(index, 1);

            $scope.save();
        }

        $scope.addCriterion = function (section) {
            var date = new Date;
            var new_id = 'c-' + date.getTime();
            $scope.data.criteria.push({ id: new_id, sid: section.id, weight: 1 , notApplyOption: false, name: ""});
            $scope.save();

            $timeout(function () {
                jQuery('#' + new_id + ' .criterion-title input').focus();
            }, 1);
        }

        $scope.deleteCriterion = function (criterion) {
            if (!confirm(labels.deleteCriterionConfirmation)) {
                return;
            }
            var index = $scope.data.criteria.indexOf(criterion);

            $scope.data.criteria.splice(index, 1);

            $scope.save();
        }
    }]);

    module.controller('QualificationEvaluationMethodFormController', ['$scope', '$rootScope', '$timeout', 'QualificationEvaluationMethodService', function ($scope, $rootScope, $timeout, QualificationEvaluationMethodService) {
        var labels = MapasCulturais.gettext.qualificationEvaluationMethod;

        $scope.data = {
            sections: MapasCulturais.evaluationConfiguration.sections || [],
            criteria: MapasCulturais.evaluationConfiguration.criteria || [],
            empty: true,
            consolidate: {}
        };

        Object.values($scope.data.criteria).forEach(function(crit){
            crit.notApplyOption = crit.notApplyOption == "true" ? true : false;

            var cri_options = crit.options || [];
            crit.options = cri_options.filter(function (i) {
                return i;
            });
           
            if(!crit.options.includes(labels['disabled'])){
                crit.options.unshift(labels['disabled']);
            }

            if(!crit.options.includes(labels['enabled'])){
                crit.options.unshift(labels['enabled']);
            }

            if(crit.notApplyOption && crit.options.length > 0){
                crit.options.unshift(labels['notApplicable'])
            }
        })

        if (MapasCulturais.evaluation) {
            $scope.evaluation = MapasCulturais.evaluation.evaluationData;
            $scope.data.empty = false;
        } else {
            $scope.evaluation = {};
        }

        $scope.subtotalSection = function (section) {
            var approved = false;
            var hasEvaluation = false;
            for (var i in $scope.data.criteria) {
                var cri = $scope.data.criteria[i];
                if (cri.sid == section.id) {
                    if($scope.evaluation[cri.id]){
                        hasEvaluation = true;
                    }

                    if ($scope.evaluation[cri.id] == labels['notApplicable'] || $scope.evaluation[cri.id] == labels['enabled']) {
                        approved = true;
                    } else {
                        approved = false;
                        break
                    }
                }
            }

            var result = hasEvaluation ? (approved ? labels['enabled'] : labels['disabled']) : labels['notAvaliable'];
            $scope.data.consolidate[section.id] = result;
            return result
        };

        $scope.total = function () {
            var approved = true;

            Object.values($scope.data.sections).forEach(function (section) {
                if ($scope.data.consolidate[section.id] == labels['disabled']) {
                    approved = false;
                    return;
                }
            })

            var result = Object.keys($scope.evaluation).length != 0 ? (approved ?  labels['enabled'] : labels['disabled']) : labels['notAvaliable'];
            return result
        };

    }]);
})(angular);