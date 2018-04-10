(function (angular) {
    "use strict";

    angular.module('mc.directive.editBox', [])
    .factory('EditBox', function () {
        function setPosition($box, target) {
            if ($box.hasClass('mc-left')) {
                $box.position({
                    my: 'right-20 center',
                    at: 'left center',
                    of: target
                });

            } else if ($box.hasClass('mc-right')) {
                $box.position({
                    my: 'left+20 center',
                    at: 'right center',
                    of: target
                });

            } else if ($box.hasClass('mc-top')) {
                $box.position({
                    my: 'center bottom-20',
                    at: 'center top',
                    of: target
                });

            } else if ($box.hasClass('mc-bottom')) {
                $box.position({
                    my: 'center top+20',
                    at: 'center bottom',
                    of: target
                });
            }
        }
        ;


        var editBox = {
            openEditboxes: {},
            register: function (editboxId) {
                if (this.openEditboxes[editboxId] && document.getElementById(editboxId))
                    throw new Error('EditBox with id ' + editboxId + ' already exists');

                $('.js-editable-field').on('shown', function(){ 
                    var maxSize = $(this).data('maxlength');
                    $('#charCounter').text('');
                    if(maxSize){
                        setTimeout(function(){
                            $('#charCounter').text($('textarea[onkeyup]').val().length + '/' + maxSize );
                        },500);
                    }
                });

                this.openEditboxes[editboxId] = false;

                var $box = jQuery('#' + editboxId);
                var $submitInput = $box.find('input:text');
                $submitInput.on('keyup', function (event) {
                    if (event.keyCode === 13) {
                        $box.find('button[type="submit"]').click();
                    }
                });
            },
            open: function (editboxId, $event) {
                if (typeof this.openEditboxes[editboxId] === 'undefined')
                    throw new Error('EditBox with id ' + editboxId + ' does not exists');

                    // close all
                    for (var id in this.openEditboxes) {
                        this.close(id);
                    }


                    this.openEditboxes[editboxId] = true;

                    var $box = jQuery('#' + editboxId).find('>div.edit-box');
                    $box.show();

                    jQuery('#' + editboxId).trigger('open');

                    var $firstInput = $($box.find('input,select,textarea').get(0));
                    $firstInput.focus();


                    setTimeout(function () {
                        setPosition($box, $event.target);
                    });

                },
                close: function (editboxId) {
                    if (typeof this.openEditboxes[editboxId] === 'undefined')
                        throw new Error('EditBox with id ' + editboxId + ' does not exists');

                    this.openEditboxes[editboxId] = false;

                    var $box = jQuery('#' + editboxId).find('>div.edit-box');
                    $box.hide();

                    jQuery('#' + editboxId).trigger('close');
                }
            };

            jQuery('body').on('keyup', 'edit-box', function (event) {
                if (event.keyCode === 27) {
                    editBox.close(this.id);
                }
            });

            return editBox;
        })


    .directive('editBox', ['EditBox', function (EditBox) {
        return {
            restrict: 'E',
            templateUrl: MapasCulturais.templateUrl.editBox,
            transclude: true,
            scope: {
                spinnerCondition: '=',
                onOpen: '=',
                onSubmit: '=',
                onCancel: '='
            },
            link: function ($scope, el, attrs) {
                if (!attrs.id)
                    throw new Error('EditBox id is required');

                $scope.editbox = EditBox;
                $scope.cancelLabel = attrs.cancelLabel;

                EditBox.register(attrs.id);

                $scope.args = attrs;

                $scope.spinnerUrl = MapasCulturais.spinnerUrl;

                $scope.classes = {
                    'mc-bottom': attrs.position === 'bottom' || !attrs.position,
                    'mc-top': attrs.position === 'top',
                    'mc-left': attrs.position === 'left',
                    'mc-right': attrs.position === 'right'
                };

                $scope.submit = function () {
                    if (angular.isFunction($scope.onSubmit)) {
                        $scope.onSubmit(attrs);
                    }
                };

                $scope.cancel = function () {
                    if (attrs.closeOnCancel)
                        EditBox.close(attrs.id);

                    if (angular.isFunction($scope.onCancel)) {
                        $scope.onCancel(attrs);
                    }
                    jQuery('#' + attrs.id).trigger('cancel');
                };

                if (angular.isFunction($scope.onOpen)) {
                    jQuery('#' + attrs.id).on('open', function () {
                        $scope.onOpen();
                    });
                }
            }
        };
    }]);
})(angular);