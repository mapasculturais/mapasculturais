(function (angular) {
    "use strict";
    angular.module('entity.directive.editableMultiselect', [])
        .directive('editableMultiselect', ['EditBox', '$log', function (EditBox, $log) {
            return {
                restrict: 'E',
                templateUrl: MapasCulturais.templateUrl.editableMultiselect,
                scope: {
                    entityProperty: '@',
                    emptyLabel: '@',
                    boxTitle: '@',
                },
                link: function ($scope, el, attrs) {
                    var def = MapasCulturais.entity.definition[attrs.entityProperty];

                    $scope.editBox = EditBox;
                    
                    $scope.terms = Object.keys(def.options);
                    $scope.values = ['Quilombolas','Outro'];
                    $scope.allowOther = def.allowOther;
                    $scope.allowOtherText = def.allowOtherText;

//                    $scope.boxTitle = ;

                    $log.log($scope.title);

                    $scope.editBoxId = 'editable-multiselect-' + $scope.entityProperty;
                }
            };
        }]);
})(angular);