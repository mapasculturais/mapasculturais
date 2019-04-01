(function(angular){
    "use strict";

    var entity_deps = MapasCulturais.angularAppDependencies;
    entity_deps.push('ng-mapasculturais');
    var app = angular.module('entity.app', entity_deps);

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


    app.controller('EntityController',['$scope', 'EditBox', 'loginService', function($scope, EditBox, loginService){
        $scope.editbox = EditBox;
        $scope.setRedirectUrl = function() {
            loginService.setLastUrl();
        }

        $scope.getEntityType = function() {         
            return parseInt($('#entityType').attr('data-value'));
        }
 
        $scope.data= {
            entityName: MapasCulturais.request.controller,
            entityType: $scope.getEntityType()
        }

        $('#entityType').on('hidden', function() {
            $scope.data.entityType =  $scope.getEntityType();
            $scope.$apply();
            MapasCulturais.Editables.createAll();
        });

        $scope.showField = function(fieldName) {  
            if (MapasCulturais.entityTypesMetadata) {
               return MapasCulturais.entityTypesMetadata[$scope.data.entityName][$scope.data.entityType][fieldName] !== undefined;
            }   
            return false; 
        }

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
