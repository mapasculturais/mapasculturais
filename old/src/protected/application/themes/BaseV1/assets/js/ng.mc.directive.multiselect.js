(function (angular) {
    "use strict";
    angular.module('mc.directive.multiselect', [])
        .directive('multiselect', function () {
            return {
                restrict: 'E',
                templateUrl: MapasCulturais.templateUrl.multiselect,
                scope: {
                    'allowOther': '@',
                    'allowOtherText': '@',
                    'terms': '=',
                    'values': '='
                },
                link: function ($scope, element, attribute) {
                    
                    $scope.cfg = {
                        show: false,
                        allowOtherText: $scope.allowOtherText,
                        outros: ''
                    };
                    
                    function sanitize(term) {
                        return term;
                    }
                    
                    var terms = $scope.terms.map(function (term) {
                        return sanitize(term);
                    });


                    // define a variavel de escopo "outros", model do input outros
                    function setOutros(virgula) {
                        var _outros = [];
                        $scope.values.forEach(function (term) {
                            var has = true;
                            terms.forEach(function(t){
                                if(t.value === term){
                                    has = false;
                                }
                            });
                            if (has) {
                                _outros.push(term);
                            }
                        });

                        $scope.cfg.outros = _outros.join('; ');

                        if (virgula && $scope.cfg.outros.trim().length > 0) {
                            $scope.cfg.outros += ';';
                        }
                    }


                    // verifica se um checkbox deve estar checkado ou não
                    $scope.checked = function (term) {
                        term = sanitize(term);
                        return $scope.values.indexOf(term) >= 0;
                    };

                    // função chamada no click do checkbox
                    $scope.toggleTerm = function (term) {
                        term = sanitize(term);

                        if (!angular.isArray($scope.values)) {
                            $scope.values = [term];
                        } else {
                            var idx = $scope.values.indexOf(term);

                            if (idx < 0) {
                                $scope.values.push(term);
                            } else {
                                $scope.values.splice(idx, 1);
                            }
                        }
                    };

                    // função chamada pelo input text "outros"
                    $scope.update = function (e) {
                        if (!e || e && e.keyCode === 191 && !e.shiftKey) {
                            var _outros = $scope.cfg.outros.split(';').map(function (term) {
                                return term.trim();
                            });


                            _outros.forEach(function (term, i) {
                                term = _outros[i] = term.trim();

                                if (term.length && $scope.values.indexOf(term) === -1) {
                                    $scope.values.push(term);
                                }
                            });
                            $scope.values.forEach(function (term, i) {
                                if (terms.indexOf(term) === -1 && _outros.indexOf(term) === -1) {
                                    $scope.values.splice(i, 1);
                                }
                            });
                            setOutros(e && e.keyCode === 191);
                        }
                    };
                    
                    
                    setOutros();
                    $scope.cfg.show = $scope.cfg.outros.length > 0;
                }
            };
        });
})(angular);