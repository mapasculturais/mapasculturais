(function(angular){
    "use strict";
    
    var app = angular.module('Entity', ['RelatedAgents', 'FindService']);
    
    app.controller('EntityController',['$scope', 'RelatedAgents', 'FindService', function($scope, RelatedAgents, FindService){
            
    }]).directive('findEntity', ['FindService', function(FindService){
        return {
            restrict: 'E',
            require: '^entity',
            templateUrl: MapasCulturais.assetURL + '/js/directives/find-entity.html',
            scope:{
                entity: '@',
                searchText: '='
            },
            find: function(){
                
            }
        };
    }]);
})(angular);