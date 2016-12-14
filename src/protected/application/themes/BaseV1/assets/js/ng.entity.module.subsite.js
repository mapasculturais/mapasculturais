(function (angular) {
    "use strict";

    var module = angular.module('entity.module.subsite', ['ngSanitize']);

    module.controller('SealsSubSiteController', ['$scope', '$rootScope', 'EditBox', function ($scope, $rootScope, EditBox) {
            $scope.editbox = EditBox;
            $scope.seals = [];
            $scope.allowedSeals = MapasCulturais.allowedSeals;
            $scope.entity = MapasCulturais.entity.object;
            $scope.verifiedSeals = MapasCulturais.entity.object.verifiedSeals;
            $scope.showCreateDialog = {};
            $scope.isEditable = MapasCulturais.isEditable;
            $scope.data = {};

            for (var i in MapasCulturais.allowedSeals)
                if ($scope.verifiedSeals.indexOf("" + MapasCulturais.allowedSeals[i].id + "") < 0)
                    $scope.seals.push(MapasCulturais.allowedSeals[i]);

            $scope.setSeal = function (entity) {
                var found = $scope.seals.indexOf(entity);
                $scope.seals.splice(found, 1);

                $scope.verifiedSeals.push(entity.id);
                $('#verifiedSeals').editable('setValue', $scope.verifiedSeals.join(';'));

                EditBox.close('set-seal-subsite');
            };

            $scope.removeSeal = function (sealId) {
                var found = $scope.verifiedSeals.indexOf(sealId);

                for (var i in MapasCulturais.allowedSeals) {
                    if (MapasCulturais.allowedSeals[i].id == $scope.verifiedSeals[found]) {
                        $scope.seals.push(MapasCulturais.allowedSeals[i]);
                    }
                }

                $scope.verifiedSeals.splice(found, 1);
                $('#verifiedSeals').editable('setValue', $scope.verifiedSeals.join(';'));
            };

            $scope.getArrIndexBySealId = function (sealId) {
                sealId = parseInt(sealId);
                for (var found in $scope.allowedSeals) {
                    if ($scope.allowedSeals[found].id == sealId) {
                        break;
                        break;
                    }
                }
                return found;
            };

            $scope.avatarUrl = function (url) {
                if (url) {
                    return url;
                } else {
                    return MapasCulturais.assets.avatarSeal;
                }
            };
        }]);
})(angular);
