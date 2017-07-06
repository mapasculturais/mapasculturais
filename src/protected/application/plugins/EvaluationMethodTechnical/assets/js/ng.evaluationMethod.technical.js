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
                    e.min = parseInt(e.min);
                    e.max = parseInt(e.max);
                    e.weight = parseInt(e.weight);
                    return e;
                });
            }
            
            $scope.data = {
                sections: MapasCulturais.evaluationConfiguration.sections || [],
                criteria: MapasCulturais.evaluationConfiguration.criteria || [],
                quotas: MapasCulturais.evaluationConfiguration.quotas || [],

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

            $scope.save = function(data){
                data = data || {
                    sections: $scope.data.sections,
                    criteria: $scope.data.criteria,
                    quotas: $scope.data.quotas,
                };
                
                TechnicalEvaluationMethodService.patchEvaluationMethodConfiguration(data).success(function () {
                    MapasCulturais.Messages.success(labels.changesSaved);
                });
            };

            $scope.addSection = function(){
                var date = new Date;
                $scope.data.sections.push({id: 's-' + date.getTime(), name: ''});
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
                $scope.data.criteria.push({id: 'c-' + date.getTime(), sid: section.id, title: null, min: 0, max: 10, weight:1});
                $scope.save({criteria: $scope.data.criteria});
            }

            $scope.deleteCriterion = function(section){
                if(!confirm(labels.deleteCriterionConfirmation)){
                    return;
                }
                var index = $scope.data.criteria.indexOf(section);

                $scope.data.criteria.splice(index,1);

                $scope.save({criteria: $scope.data.criteria});
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
                    if(id != 'obs'){
                        MapasCulturais.evaluation.evaluationData[id] = parseFloat(MapasCulturais.evaluation.evaluationData[id]);
                    }
                }
            }
            
            $scope.data = {
                sections: MapasCulturais.evaluationConfiguration.sections || [],
                criteria: MapasCulturais.evaluationConfiguration.criteria || [],

            };

            $scope.evaluation = MapasCulturais.evaluation ? MapasCulturais.evaluation.evaluationData : {};

            $scope.subtotalSection = function(section){
                var total = 0;

                for(var i in $scope.data.criteria){
                    var cri = $scope.data.criteria[i];
                    if(cri.sid == section.id){
                        total += $scope.evaluation[cri.id] * cri.weight;
                    }
                }

                return total;
            };

            $scope.total = function(){
                var total = 0;

                for(var i in $scope.data.criteria){
                    var cri = $scope.data.criteria[i];
                    total += $scope.evaluation[cri.id] * cri.weight;
                }

                return total;
            };

            $scope.max = function(){
                var total = 0;

                for(var i in $scope.data.criteria){
                    var cri = $scope.data.criteria[i];
                    total += cri.max * cri.weight;
                }

                return total;
            };

            
        }]);
})(angular);