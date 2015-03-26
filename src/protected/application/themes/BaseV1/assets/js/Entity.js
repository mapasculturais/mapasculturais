(function(angular){
    "use strict";

    var app = angular.module('Entity', ['RelatedAgents', 'ChangeOwner', 'Project', 'Notifications', 'ngSanitize']);

    app.factory('UrlService', [function(){
        return function(controller){
            this.create = function(action, params){
                if(params == parseInt(params)){ // params is an integer, so it is an id
                    return MapasCulturais.createUrl(controller, action, [params]);
                }else{
                    return MapasCulturais.createUrl(controller, action, params);
                }
            };
        };
    }]);

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

                var pagination = this;

                function success () {
                    success_cb.call(pagination,arguments);
                    executing = false;
                }

                function error () {
                    error_cb.call(pagination,arguments);
                    executing = false;
                }

                return {
                    reset: function (){
                        page = 0;
                    },

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

                $scope.noMoreResults = false;

                // pagination at the end of container scroll
                var $el = jQuery(el[0]);
                var $container = $el.find('.result-container');

                $container.scroll(function(){
                    var containerInnerHeight = this.scrollHeight;
                    var containerScroll = jQuery(this).scrollTop();
                    var containerHeight = jQuery(this).height();
                    var bottomY = containerInnerHeight - containerHeight - containerScroll;
                    if(bottomY < containerHeight && !$scope.noMoreResults && !$scope.paginating){
                        $scope.paginating = true;
                        $scope.find(10);
                    }
                }).bind( 'mousewheel DOMMouseScroll', function ( e ) {
                    var e0 = e.originalEvent,
                        delta = e0.wheelDelta || -e0.detail;

                    this.scrollTop += ( delta < 0 ? 1 : -1 ) * 30;
                    e.preventDefault();
                });

                $scope.avatarUrl = function(entity){
                    if(entity['@files:avatar.avatarSmall'])
                        return entity['@files:avatar.avatarSmall'].url;
                    else
                        return MapasCulturais.defaultAvatarURL;
                };

                $scope.find = function(time){
                    if(timeouts.find)
                        $timeout.cancel(timeouts.find);

                    time = time || 1000;

                    FindService.cancel();

                    var s = $scope.searchText.trim().replace(' ', '*');

                    if(parseInt(s) != s && s.length < 2) {
                        return;
                    }

                    var query = angular.isObject($scope.apiQuery) ? $scope.apiQuery : {};

                    if($scope.lastS != s){
                        $scope.lastS = s;
                        $scope.result = [];
                        $scope.noMoreResults = false;
                        $scope.pagination = FindService.pagination($scope.entity, 20, query, function(data, status){
                            $scope.processResult(data, status);
                            $scope.spinnerCondition = false;
                            $scope.paginating = false;
                        });
                    }


                    query.name = 'ILIKE(*' + s + '*)';
                    timeouts.find = $timeout(function(){
                        $scope.spinnerCondition = true;
                        $scope.pagination.nextPage();
                    },time);
                };

                $scope.processResult = function(data, status){
                    data = data[0];
                    if(angular.isFunction($scope.filter))
                        data = $scope.filter(data, status);

                    if(data.length > 0){
                        $scope.result = $scope.result.concat(data);
                    }else if($scope.result.length === 0){
                        $scope.noEntityFound = true;

                        $timeout(function(){
                            $scope.noEntityFound = false;
                        },3000);
                    }else{
                        // final dos resultados
                        $scope.noMoreResults = true;
                    }

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
                if(this.openEditboxes[editboxId] && document.getElementById(editboxId))
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

                // close all
                for(var id in this.openEditboxes){
                    this.close(id);
                }

                this.openEditboxes[editboxId] = true;

                var $box = jQuery('#' + editboxId).find('>div.edit-box');
                $box.show();

                jQuery('#' + editboxId).trigger('open');

                var $firstInput = $($box.find('input,select,textarea').get(0));
                $firstInput.focus();


                setTimeout(function(){ setPosition($box, $event.target); });
            },

            close: function(editboxId){
                if(typeof this.openEditboxes[editboxId] === 'undefined')
                    throw new Error('EditBox with id ' + editboxId + ' does not exists');

                this.openEditboxes[editboxId] = false;

                var $box = jQuery('#' + editboxId).find('>div.edit-box');
                $box.hide();

                jQuery('#' + editboxId).trigger('close');
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
                        $scope.onSubmit(attrs);
                    }
                };

                $scope.cancel = function(){
                    if(attrs.closeOnCancel)
                        EditBox.close(attrs.id);

                    if(angular.isFunction($scope.onCancel)){
                        $scope.onCancel(attrs);
                    }
                    jQuery('#' + attrs.id).trigger('cancel');
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
                placeholder: '@',
                setter: '=',
                getter: '='
            },
            link: function($scope, el, attrs) {
                $scope.classes = attrs.classes;

                $scope.selectItem = function(item, $event){
                    if(angular.isFunction($scope.setter)){
                        $scope.setter($scope.model, item);
                    }else{
                        $scope.model = item.value;
                    }
                },

                $scope.getSelectedValue = function(){
                    if($scope.model && angular.isFunction($scope.getter)){
                        return $scope.getter($scope.model);
                    }else{
                        return $scope.model;
                    }
                };

                $scope.getSelectedItem = function(){
                    var item = null,
                        selectedValue = $scope.getSelectedValue();

                    $scope.data.forEach(function(e){
                        if(e.value == selectedValue)
                            item = e;
                    });
                    return item;
                };

                $scope.getSelectedLabel = function(){
                    var item = $scope.getSelectedItem();

                    if(item){
                        return item.label;
                    }else{
                        return $scope.placeholder;
                    }
                };

                $scope.isSelected = function(item){
                    return item.value == $scope.getSelectedValue();
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