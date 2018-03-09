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

        console.log(MapasCulturais);

        $scope.filters = MapasCulturais.user_filters__subsite;

        for (var entity in $scope.filters){
            console.log(entity);
            console.log($scope.filters[entity]);
            if ($scope.filters[entity][0])
                $scope.filters[entity] = JSON.parse($scope.filters[entity]);
            else
                $scope.filters[entity] = {};
        }

        $scope.config_filters = [];
        $scope.config_filters.event = MapasCulturais.EntitiesDescription.event;
        $scope.config_filters.space = MapasCulturais.EntitiesDescription.space;
        $scope.config_filters.agent = MapasCulturais.EntitiesDescription.agent;
        $scope.config_filters.project = MapasCulturais.EntitiesDescription.project;
        $scope.config_filters.opportunity = MapasCulturais.EntitiesDescription.opportunity;

        $scope.add_filter = function(entity, attrs) {
            $scope.filter_entity = entity;
            // todo: set options
            $scope.new_filter = {};
            EditBox.open('new-filter');
        };

        $scope.delete_filter = function(entitiy_filter, filter) {
            console.log('filters', entitiy_filter, filter);
            entitiy_filter.splice(entitiy_filter.indexOf(filter), 1);
        };

        $scope.save_filter = function(attrs) {
            console.log($scope);

            if (!(
                $scope.new_filter.label &&
                $scope.new_filter.placeholder &&
                $scope.new_filter.param &&
                $scope.new_filter.fieldType &&
                $scope.new_filter.value
            )){
                $scope.filter_error = true;
                console.log('falta campo');
                // todo: remove error message
                return;
            };

            var filters = $('#user_filters__' + $scope.filter_entity).val();

            if (!filters)
                filters = {};
            else
                filters = JSON.parse(filters);

            filters[Object.keys(filters).length] = $scope.new_filter;

            $scope.filters[$scope.filter_entity] = filters;

            $('#user_filters__' + $scope.filter_entity).editable('setValue', JSON.stringify(filters));

            EditBox.close(attrs.id);
        };

        $scope.up_filter = function(entity, entitiy_filter) {
            // todo: index filter -1
        };

        $scope.down_filter = function(entity, entitiy_filter) {
            // todo: index filter +1
        };

    }]);

})(angular);
