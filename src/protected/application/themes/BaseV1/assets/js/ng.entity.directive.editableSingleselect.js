(function (angular) {
    "use strict";
    angular.module('entity.directive.editableSingleselect', [])
        .directive('editableSingleselect', ['EditBox', '$log', function (EditBox, $log) {
            return {
                restrict: 'E',
                templateUrl: MapasCulturais.templateUrl.editableSingleselect,
                scope: {
                    entityProperty: '@',
                    emptyLabel: '@',
                    boxTitle: '@',
                    helpText: '@'
                },
                link: function ($scope, el, attrs) {
                    var def = MapasCulturais.entity.definition[attrs.entityProperty];
                    var entity = MapasCulturais.entity.object;

                    $scope.editBox = EditBox;
                    
                    $scope.terms = [];
                    
                    def.optionsOrder.forEach(function(e){
                        $scope.terms.push({
                            label: def.options[e],
                            value: e
                        });
                    });

                    $scope.data = {
                        inputVal: entity[$scope.entityProperty],
                        isEditable: MapasCulturais.isEditable,
                        allowOther: def.allowOther,
                        allowOtherText: def.allowOtherText,
                        editBoxId: 'editable-singleselect-' + $scope.entityProperty,
                        value: entity[$scope.entityProperty],
                        boxTitle: $scope.boxTitle
                    };

                    function resetValue(){
                        if(entity[$scope.entityProperty]){
                            $scope.data.value = entity[$scope.entityProperty];
                            $scope.data.displayValue = entity[$scope.entityProperty];
                            $scope.data.inputValue = entity[$scope.entityProperty];
                        }else{
                            entity[$scope.entityProperty] = '';
                            $scope.data.value = '';
                            $scope.data.displayValue = $scope.emptyLabel;
                        }
                    }

                    resetValue();

                    $scope.displayValue = function(){
                        if($scope.terms[$scope.data.inputVal]){
                           return $scope.terms[$scope.data.inputVal];
                        } else {
                            return $scope.data.inputVal || $scope.emptyLabel;
                        }
                    };


                    $scope.openEditBox = function($event){
                        if(MapasCulturais.isEditable){
                            EditBox.open($scope.data.editBoxId, $event);
                        }
                    };
                    
                    $scope.submit = function(){
                        var $input = jQuery('#' + $scope.entityProperty);
                        
                        entity[$scope.entityProperty] = $scope.data.value;

                        $scope.data.inputVal = entity[$scope.entityProperty];

                        $scope.data.displayValue = $scope.inputVal ? $scope.inputVal : $scope.emptyLabel;

                        $input.editable('setValue', $scope.data.inputVal);

                        EditBox.close($scope.data.editBoxId);
                    };
                    
                    $scope.cancel = function(){
                        resetValue();
                    };
                }
            };
        }]);
})(angular);