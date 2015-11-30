(function (angular) {
    "use strict";
    angular.module('mc.directive.multiselect', [])
        .directive('multiselect', function () {
            return {
                restrict: 'E',
                templateUrl: MapasCulturais.templateUrl.multiselect,
                scope: {
                    label: '@',
                    allowOther: '@',
                    allowOtherText: '@',
                },
                link: function ($scope, el, attrs) {
                    $scope.label = attrs.label;
                }
            };
        });
})(angular);