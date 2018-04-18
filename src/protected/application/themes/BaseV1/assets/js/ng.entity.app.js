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
        $scope.data = {};
        $scope.setRedirectUrl = function() {
            loginService.setLastUrl();
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
