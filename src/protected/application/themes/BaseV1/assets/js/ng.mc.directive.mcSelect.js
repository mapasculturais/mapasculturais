(function (angular) {
    "use strict";

    angular.module('mc.directive.mcSelect', [])
        .directive('mcSelect', [function () {
                return {
                    restrict: 'E',
                    templateUrl: MapasCulturais.templateUrl.MCSelect,
                    transclude: true,
                    scope: {
                        data: '=',
                        model: '=',
                        placeholder: '@',
                        setter: '=',
                        getter: '='
                    },
                    link: function ($scope, el, attrs) {
                        $scope.classes = attrs.classes;

                        $scope.selectItem = function (item, $event) {
                            if (angular.isFunction($scope.setter)) {
                                $scope.setter($scope.model, item);
                            } else {
                                $scope.model = item.value;
                            }
                        },
                            $scope.getSelectedValue = function () {
                                if ($scope.model && angular.isFunction($scope.getter)) {
                                    return $scope.getter($scope.model);
                                } else {
                                    return $scope.model;
                                }
                            };

                        $scope.getSelectedItem = function () {
                            var item = null,
                                selectedValue = $scope.getSelectedValue();

                            $scope.data.forEach(function (e) {
                                if (e.value == selectedValue)
                                    item = e;
                            });
                            return item;
                        };

                        $scope.getSelectedLabel = function () {
                            var item = $scope.getSelectedItem();

                            if (item) {
                                return item.label;
                            } else {
                                return $scope.placeholder;
                            }
                        };

                        $scope.isSelected = function (item) {
                            return item.value == $scope.getSelectedValue();
                        }
                    }
                };
            }]);
})(angular);