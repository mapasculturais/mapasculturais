(function(angular){
    "use strict";

    var app = angular.module('Entity', ['RelatedAgents', 'ChangeOwner', 'Project', 'Notifications', 'ngSanitize']);

    app.factory('FindService', ['$rootScope', '$http', '$q', function($rootScope, $http, $q){
        var baseUrl = MapasCulturais.baseURL + '/api/';
        var canceller;
        
        function extend (query){
            return angular.extend(query, {
                "@select": 'id,name,type,shortDescription,terms',
                "@files": '(avatar.avatarSmall):url',
                "@order": 'name'
            });
        };

        function request (url, query, success_cb, error_cb){
            cancelRequest();
            
            query = extend(query);
            
            
            canceller = $q.defer();
            
            var p = $http({
                url: url,
                method: "GET",
                timeout: canceller.promise,
                cache:true,
                params: query
            });

            if( angular.isFunction( success_cb ) ){
                p.success( success_cb );
            }

            if( angular.isFunction( error_cb ) ){
                p.error( error_cb );
            }
        };
        
        function cancelRequest(){
            if(canceller){
                canceller.resolve();
            }
        }

        return {
            
            cancel: function(){
               cancelRequest();
            },

            find: function(entity, query, success_cb, error_cb){
                var url = baseUrl + entity + '/find';

                request(url, query, success_cb, error_cb);
            },

            findOne: function(entity, query, success_cb, error_cb){
                var url = baseUrl + entity + '/find';

                request(url, query, success_cb, error_cb);
            },

            pagination: function(entity, resultsPerPage, query, success_cb, error_cb){
                var url = baseUrl + entity + '/find',
                    page = 0, executing = false;

                function success () {
                    success_cb();
                    executing = false;
                }

                function error () {
                    error_cb();
                    executing = false;
                }

                return {
                    nextPage: function () {
                        if(!executing){
                            executing = true;

                            page++;

                            query['@page'] = page;
                            query['@limit'] = resultsPerPage;

                            request(url, query, success, error);
                        }
                    },

                    currentPage: function (){
                        if(this._page > 0 && !executing){
                            executing = true;

                            query['@page'] = page;
                            query['@limit'] = resultsPerPage;

                            request(url, query, success, error);
                        }
                    },

                    previousPage: function () {
                        if(this._page > 0 && !executing){
                            executing = true;

                            page--;

                            query['@page'] = page;
                            query['@limit'] = resultsPerPage;

                            request(url, query, success, error);
                        }
                    }
                };
            }
        };
    }]);

    app.controller('EntityController',['$scope', '$timeout', 'RelatedAgents', 'ChangeOwner', 'Project', function($scope, $timeout, RelatedAgents, ChangeOwner, Project){
        $scope.openEditBox = function(editboxId){

        };
    }]);

    app.directive('findEntity', ['$timeout', 'FindService', function($timeout, FindService){
        var timeouts = {};

        return {
            restrict: 'E',
            templateUrl: MapasCulturais.templateUrl.findEntity,
            scope:{
                spinnerCondition: '=',
                entity: '@',
                noResultsText: '@',
                filter: '=',
                select: '=',
                onRepeatDone: '=',
                apiQuery: '='
            },

            link: function($scope, el, attrs){

                $scope.attrs = attrs;

                $scope.result = [];

                $scope.searchText = '';

                $scope.noEntityFound = false;

                $scope.avatarUrl = function(entity){
                    if(entity['@files:avatar.avatarSmall'])
                        return entity['@files:avatar.avatarSmall'].url;
                    else
                        return MapasCulturais.defaultAvatarURL;
                };

                $scope.find = function(){
                    if(timeouts.find)
                        $timeout.cancel(timeouts.find);
                    
                    FindService.cancel();

                    var s = $scope.searchText.trim().replace(' ', '*');

                    var query = angular.isObject($scope.apiQuery) ? $scope.apiQuery : {};

                    query.name = 'ILIKE(*' + s + '*)';
                    timeouts.find = $timeout(function(){
                        $scope.spinnerCondition = true;
                        FindService.find($scope.entity, query, function(data, status){
                            $scope.processResult(data, status);
                            $scope.spinnerCondition = false;
                        });
                    },500);
                };

                $scope.processResult = function(data, status){
                    if(angular.isFunction($scope.filter))
                        data = $scope.filter(data, status);

                    if(data.length === 0){
                        $scope.noEntityFound = true;

                        $timeout(function(){
                            $scope.noEntityFound = false;
                        },3000);
                    }

                    $scope.result = data;
                };

                jQuery(el).on('find', function(){
                    $scope.find();
                });
            }
        };
    }]);



    app.factory('EditBox', function(){
        function setPosition($box, target){
            if($box.hasClass('mc-left')){
                $box.position({
                    my: 'right-20 center',
                    at: 'left center',
                    of: target
                });

            }else if($box.hasClass('mc-right')){
                $box.position({
                    my: 'left+20 center',
                    at: 'right center',
                    of: target
                });

            }else if($box.hasClass('mc-top')){
                $box.position({
                    my: 'center bottom-20',
                    at: 'center top',
                    of: target
                });

            }else if($box.hasClass('mc-bottom')){
                $box.position({
                    my: 'center top+20',
                    at: 'center bottom',
                    of: target
                });
            }
        };


        var editBox = {
            openEditboxes: {},

            register: function(editboxId){
                if(this.openEditboxes[editboxId])
                    throw new Error('EditBox with id ' + editboxId + ' already exists');

                this.openEditboxes[editboxId] = false;

                var $box = jQuery('#' + editboxId);
                var $submitInput = $box.find('input:text');
                $submitInput.on('keyup', function(event){
                    if(event.keyCode === 13){
                        $box.find('button[type="submit"]').click();
                    }
                });
            },

            open: function(editboxId, $event){
                
                if(typeof this.openEditboxes[editboxId] === 'undefined')
                    throw new Error('EditBox with id ' + editboxId + ' does not exists');

                this.openEditboxes[editboxId] = true;

                var $box = jQuery('#' + editboxId).find('>div.edit-box');
                $box.show();

                jQuery('#' + editboxId).trigger('open');

                var $firstInput = $box.find('input:first,select:first,textarea:first');
                $firstInput.focus();
                setPosition($box, $event.target);
            },

            close: function(editboxId){
                if(typeof this.openEditboxes[editboxId] === 'undefined')
                    throw new Error('EditBox with id ' + editboxId + ' does not exists');

                this.openEditboxes[editboxId] = false;

                var $box = jQuery('#' + editboxId).find('>div.edit-box');
                $box.hide();
            }
        };

        jQuery('body').on('keyup', 'edit-box', function(event){
            if(event.keyCode === 27){
                editBox.close(this.id);
            }
        });

        return editBox;
    });


    app.directive('editBox', ['EditBox', function(EditBox) {
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

            link: function($scope, el, attrs) {
                if(!attrs.id)
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

                $scope.submit = function(){
                    if(angular.isFunction($scope.onSubmit)){
                        $scope.onSubmit();
                    }
                };

                $scope.cancel = function(){
                    if(attrs.closeOnCancel)
                        EditBox.close(attrs.id);

                    if(angular.isFunction($scope.onCancel)){
                        $scope.onCancel();
                    }
                };

                if(angular.isFunction($scope.onOpen)){
                    jQuery('#'+attrs.id).on('open', function(){ $scope.onOpen(); });
                }
            }
        };
    }]);

    app.directive('mcSelect', [function() {
        return {
            restrict: 'E',
            templateUrl: MapasCulturais.templateUrl.MCSelect,
            transclude: true,

            scope: {
                data: '=',
                model: '=',
                placeholder: '@'
            },
            link: function($scope, el, attrs) {
                $scope.classes = attrs.classes;
                $scope.selectItem = function(item, $event){
                    $($event.target).parents('.js-submenu-dropdown').hide();
                    setTimeout(function(){
                        $($event.target).parents('.js-submenu-dropdown').css('display','');
                    },500);
                    
                    $scope.model = item;
                }
            }
        };
    }]);


    app.directive('onRepeatDone', ['$rootScope', function($rootScope) {
        return function($scope, element, attrs) {
            if ($scope.$last) {
                $rootScope.$emit('repeatDone:' + attrs.onRepeatDone);
            }
        };
    }]);


})(angular);