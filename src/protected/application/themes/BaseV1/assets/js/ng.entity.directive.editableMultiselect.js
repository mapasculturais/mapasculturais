(function (angular) {
    "use strict";
    angular.module('entity.directive.editableMultiselect', [])
        .directive('editableMultiselect', function () {
            return {
                restrict: 'E',
                templateUrl: MapasCulturais.templateUrl.editableMultiselect,
                scope: {
                    entityProperty: '@',
                    emptyLabel: '@'
                },
                link: function ($scope, el, attrs) {
                    $scope.terms = Object.keys(MapasCulturais.entity.definition[attrs.entityProperty].options);
                    $scope.values = ['Quilombolas','Outro'];
                    $scope.allowOther = MapasCulturais.entity.definition[attrs.entityProperty].allowOther;
                    $scope.allowOtherText = MapasCulturais.entity.definition[attrs.entityProperty].allowOtherText;
                }
            };
        });
})(angular);