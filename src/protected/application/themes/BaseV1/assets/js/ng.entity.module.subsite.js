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

    module.controller('ConfigFilterSubsiteController', ['$scope', '$timeout', 'EditBox', function($scope, $timeout, EditBox){

        $scope.filters = MapasCulturais.user_filters__subsite;
        $scope.conf_filters = MapasCulturais.user_filters__conf;
        $scope.readable_names = MapasCulturais.readable_names;

        for (var entity in $scope.filters){

            if ($scope.filters[entity][0])
                $scope.filters[entity] = JSON.parse($scope.filters[entity]);
            else
                $scope.filters[entity] = [];
        }

        $scope.add_filter = function(entity, attrs) {
            $scope.filter_entity = entity;
            $scope.new_filter = {};
            $('#filter-error').hide();
            EditBox.open('new-filter');
        };

        $scope.delete_filter = function(entitiy_filter, filter) {

            entitiy_filter.splice(entitiy_filter.indexOf(filter), 1);

        };

        $scope.save_filter = function(attrs) {

            if (!(
                $scope.new_filter.label &&
                $scope.new_filter.field
            )){
                $('#filter-error').show(400, function(){
                    $('#filter-error').delay(6000).hide(400);
                });
                return;
            };

            $scope.filters[$scope.filter_entity].push($scope.new_filter);

            $('#user_filters__' + $scope.filter_entity).editable('setValue', JSON.stringify($scope.filters[$scope.filter_entity]));

            EditBox.close(attrs.id);

        };

        $scope.up_filter = function(entity, entitiy_filter) {
            // todo: index filter -1
        };

        $scope.down_filter = function(entity, entitiy_filter) {
            // todo: index filter +1
        };


        console.log('MapasCulturais', MapasCulturais);
        console.log('$scope', $scope);

    }]);

})(angular);
