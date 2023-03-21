(function (angular) {
    "use strict";
    angular.module('mc.directive.singleselect', [])
        .directive('singleselect', function () {
            return {
                restrict: 'E',
                templateUrl: MapasCulturais.templateUrl.singleselect,
                scope: {
                    'name': '@',
                    'value': '=',
                    'terms': '=',
                    'allowOther': '@',
                    'allowOtherText': '@',
                    'otherValue': '=',
                },
                link: function ($scope, element, attribute) {
                    function sanitize(term){
                        if(!term){
                            term = '';
                        }
                        return term.trim();
                    }

                    $scope.data = {
                        name: $scope.name,
                        value: $scope.value,
                        terms: $scope.terms,
                        allowOther: $scope.allowOther,
                        allowOtherText: $scope.allowOtherText
                    };

                    $scope.clickOther = function(){
                        $scope.data.value = $scope.data.otherValue || '';
                        $scope.data.showOther = true;
                    };

                    if($scope.data.value && !$scope.terms[sanitize($scope.data.value)]){
                        $scope.data.showOther = true;
                    }

                    $scope.notOther = function(){
                        $scope.data.showOther = false;
                    };

                    $scope.$watch('data.value', function(a,b){
                        if ($scope.data.showOther)
                            $scope.data.otherValue = $scope.value;
                        $scope.value = $scope.data.value;
                    });
                }
            };
        });
})(angular);