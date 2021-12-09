(function (angular) {
    "use strict";

    var module = angular.module('ng.evaluationMethod.technical', ['ngSanitize']);

    module.config(['$httpProvider', function ($httpProvider) {
            $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
            $httpProvider.defaults.headers.patch['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
            $httpProvider.defaults.transformRequest = function (data) {
                var result = angular.isObject(data) && String(data) !== '[object File]' ? $.param(data) : data;

                return result;
            };
        }]);

    module.factory('TechnicalEvaluationMethodService', ['$http', '$rootScope', function ($http, $rootScope) {
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

    module.controller('TechnicalEvaluationMethodConfigurationController', ['$scope', '$rootScope', '$timeout', 'TechnicalEvaluationMethodService', 'EditBox', function ($scope, $rootScope, $timeout, TechnicalEvaluationMethodService, EditBox) {
            $scope.editbox = EditBox;

            var labels = MapasCulturais.gettext.technicalEvaluationMethod;
            
            if(MapasCulturais.evaluationConfiguration && MapasCulturais.evaluationConfiguration.criteria){
                MapasCulturais.evaluationConfiguration.criteria = MapasCulturais.evaluationConfiguration.criteria.map(function(e){
                    e.min = parseFloat(e.min);
                    e.max = parseFloat(e.max);
                    e.weight = parseFloat(e.weight);
                    return e;
                });
            }
            
            $scope.data = {
                sections: MapasCulturais.evaluationConfiguration.sections || [],
                criteria: MapasCulturais.evaluationConfiguration.criteria || [],
                quotas: MapasCulturais.evaluationConfiguration.quotas || [],
                enableViability: MapasCulturais.evaluationConfiguration.enableViability || false,

                debounce: 2000
            };

            function sectionExists(name) {
                var exists = false;
                $scope.data.sections.forEach(function (s) {
                    if (s.name == name) {
                        exists = true;
                    }
                });

                return exists;
            }

            $scope.save = function(){
                var data = {
                    sections: $scope.data.sections,
                    criteria: [],
                    quotas: $scope.data.quotas,
                    enableViability: $scope.data.enableViability,
                };

                $scope.data.criteria.forEach(function (crit) {
                    for (var i in data.sections) {
                        var section = data.sections[i];
                        if (crit.sid == section.id) {
                            data.criteria.push(crit);
                        }
                    }
                });

                TechnicalEvaluationMethodService.patchEvaluationMethodConfiguration(data).success(function () {
                    MapasCulturais.Messages.success(labels.changesSaved);
                    $scope.data.sections = data.sections;
                    $scope.data.criteria = data.criteria;
                    $scope.data.enableViability = data.enableViability;
                });
            };

            $scope.addSection = function(){
                var date = new Date;
                var new_id = 's-' + date.getTime();
                $scope.data.sections.push({id: new_id, name: ''});

                $timeout(function(){
                    jQuery('#' + new_id + ' header input').focus();
                },1);
            };

            $scope.deleteSection = function(section){
                if(!confirm(labels.deleteSectionConfirmation)){
                    return;
                }
                var index = $scope.data.sections.indexOf(section);

                $scope.data.criteria = $scope.data.criteria.filter(function(cri){
                    if(cri.sid != section.id){
                        return cri;
                    }
                });

                $scope.data.sections.splice(index,1);

                $scope.save();
            }

            $scope.addCriterion = function(section){
                var date = new Date;
                var new_id = 'c-' + date.getTime();
                $scope.data.criteria.push({id: new_id, sid: section.id, title: null, min: 0, max: 10, weight:1});
                $scope.save();

                $timeout(function(){
                    jQuery('#' + new_id + ' .criterion-title input').focus();
                },1);
            }

            $scope.deleteCriterion = function(criterion){
                if(!confirm(labels.deleteCriterionConfirmation)){
                    return;
                }
                var index = $scope.data.criteria.indexOf(criterion);

                $scope.data.criteria.splice(index,1);

                $scope.save();
            }
        }]);

    module.controller('TechnicalEvaluationMethodFormController', ['$scope', '$rootScope', '$timeout', 'TechnicalEvaluationMethodService', function ($scope, $rootScope, $timeout, TechnicalEvaluationMethodService) {
            var labels = MapasCulturais.gettext.technicalEvaluationMethod;
            MapasCulturais.evaluationConfiguration.criteria = MapasCulturais.evaluationConfiguration.criteria.map(function(e){
                e.min = parseInt(e.min);
                e.max = parseInt(e.max);
                e.weight = parseInt(e.weight);
                return e;
            });
            
            if(MapasCulturais.evaluation){
                for(var id in MapasCulturais.evaluation.evaluationData){
                    if(id != 'obs' && id != 'viability'){
                        MapasCulturais.evaluation.evaluationData[id] = parseFloat(MapasCulturais.evaluation.evaluationData[id]);
                    }
                }
            }
            
            $scope.data = {
                sections: MapasCulturais.evaluationConfiguration.sections || [],
                criteria: MapasCulturais.evaluationConfiguration.criteria || [],
                enableViability: MapasCulturais.evaluationConfiguration.enableViability || false,
                empty: true
            };

            if(MapasCulturais.evaluation){
                $scope.evaluation =  MapasCulturais.evaluation.evaluationData;
                $scope.data.empty = false;
            } else {
                $scope.evaluation =  {};
            }

            $scope.subtotalSection = function(section){
                var total = 0;

                for(var i in $scope.data.criteria){
                    var cri = $scope.data.criteria[i];
                    if(cri.sid == section.id){
                        total += $scope.evaluation[cri.id] * cri.weight;
                    }
                }

                return total.toFixed(1);
            };

            $scope.total = function(){
                var total = 0;

                for(var i in $scope.data.criteria){
                    var cri = $scope.data.criteria[i];
                    total += $scope.evaluation[cri.id] * cri.weight;
                }

                return total.toFixed(1);
            };

            $scope.max = function(){
                var total = 0;

                for(var i in $scope.data.criteria){
                    var cri = $scope.data.criteria[i];
                    total += cri.max * cri.weight;
                }

                return total;
            };

            $scope.checkTotal = function(num) {
                if (isNaN(num))
                    return 0;

                return num.toFixed(1);
            };

            $scope.viabilityLabel = function(val) {
                if ($scope.data.enableViability) {
                    var label = "Inválida";
                    if ("valid" === val)
                        label = "Válida";

                    return label;
                }
            }

            
        }]);
})(angular);