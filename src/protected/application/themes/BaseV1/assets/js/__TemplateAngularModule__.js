(function (angular) {
    "use strict";

    var module = angular.module('_ModuleName_', ['ngSanitize']);

    module.config(['$httpProvider', function ($httpProvider) {
            $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
            $httpProvider.defaults.transformRequest = function (data) {
                var result = angular.isObject(data) && String(data) !== '[object File]' ? $.param(data) : data;

                return result;
            };
        }]);

    module.factory('_ModuleName_Service', ['$http', '$rootScope', function ($http, $rootScope) {
            return {
                serviceProperty: null,
                getUrl: function(){
                    return MapasCulturais.baseURL // + controllerId  + '/' + actionName 
                },
                doSomething: function (param) {
                    var data = {
                        prop: name
                    };
                    return $http.post(this.getUrl(), data).
                            success(function (data, status) {
                                $rootScope.$emit('something', {message: "Something was done", data: data, status: status});
                            }).
                            error(function (data, status) {
                                $rootScope.$emit('error', {message: "Cannot do something", data: data, status: status});
                            });
                }
            };
        }]);

    module.controller('_ModuleName_Controller', ['$scope', '$rootScope', '$timeout', '_ModuleName_Service', 'EditBox', function ($scope, $rootScope, $timeout, _ModuleName_Service, EditBox) {
            var adjustingBoxPosition = false;
            $scope.editbox = EditBox;
            $scope.data = {
                spinner: false,
                apiQuery: {
                    '@permissions': MapasCulturais.entity.userHasControl ? '!@control' : '@control'
                }
            };
            if (MapasCulturais.roles.indexOf('superAdmin') !== -1) {
                $scope.data.apiQuery['@permissions'] = '@control';
            }

            var adjustBoxPosition = function () {
                setTimeout(function () {
                    adjustingBoxPosition = true;
                    $('#module-name-owner-button').click();
                    adjustingBoxPosition = false;
                });
            };

            $rootScope.$on('repeatDone:findEntity:find-entity-module-name-owner', adjustBoxPosition);

            $scope.$watch('data.spinner', function (ov, nv) {
                if (ov && !nv)
                    adjustBoxPosition();
            });

            $scope.requestEntity = function (e) {
                _ModuleName_Service.setOwnerTo(e.id).success(function (data, status) {
                    if (status === 202) {
                        MapasCulturais.Messages.alert('Sua requisição foi para mudança de propriedade deste ' + MapasCulturais.entity.getTypeName() + ' para o agente <strong>' + e.name + '</strong> foi enviada.');
                    } else {
                        $('.js-owner-name').html('<a href="' + e.singleUrl + '" rel="noopener noreferrer">' + e.name + '</a>');
                        $('.js-owner-description').html(e.shortDescription);
                        try {
                            $('.js-owner-avatar').attr('src', e['@files:avatar.avatarSmall'].url);
                        } catch (e) {
                            $('.js-owner-avatar').attr('src', MapasCulturais.defaultAvatarURL);
                        }
                    }
                });

                EditBox.close('editbox-module-name-owner');
            };

            $('#editbox-module-name-owner').on('open', function () {
                if (!adjustingBoxPosition)
                    $('#find-entity-module-name-owner').trigger('find');
            });

        }]);
})(angular);