(function (angular) {
    "use strict";
    var module = angular.module('entity.module.project', ['ngSanitize', 'checklist-model']);

    module.config(['$httpProvider', function ($httpProvider) {
        $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
        $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
        $httpProvider.defaults.transformRequest = function (data) {
            var result = angular.isObject(data) && String(data) !== '[object File]' ? $.param(data) : data;

            return result;
        };
    }]);

    module.factory('ProjectEventsService', ['$http', '$rootScope', 'UrlService', function ($http, $rootScope, UrlService) {
        var url = new UrlService('project');

        return {
            getUrl: function(action){
                return url.create(action, MapasCulturais.entity.id);
            },

            publish: function(ids){
             var url = this.getUrl('publishEvents');

             return $http.post(url, {ids: ids}).
             success(function (data, status) {
                $rootScope.$emit('project.publishEvents', {message: "Project events was published", data: data, status: status});
            }).
             error(function (data, status) {
                $rootScope.$emit('error', {message: "Cannot publish project events", data: data, status: status});
            });


         },

         unpublish: function(ids){
            var url = this.getUrl('unpublishEvents');

            return $http.post(url, {ids: ids}).
            success(function (data, status) {
                $rootScope.$emit('project.unpublishEvents', {message: "Project events was unpublished", data: data, status: status});
            }).
            error(function (data, status) {
                $rootScope.$emit('error', {message: "Cannot unpublish project events", data: data, status: status});
            });
        }
    };
}]);

module.controller('ProjectEventsController', ['$scope', '$rootScope', '$timeout', 'ProjectEventsService', 'EditBox', '$http', 'UrlService', function ($scope, $rootScope, $timeout, ProjectEventsService, EditBox, $http, UrlService) {
    $scope.events = $scope.data.entity.events.slice();
    $scope.numSelectedEvents = 0;

    var labels = MapasCulturais.gettext.moduleProject;

    $scope.events.forEach(function(evt){
        evt.statusText = '';

        if(evt.status == 1){
            evt.statusText = labels['statusPublished'];
        } else if(evt.status == 0){
            evt.statusText = labels['statusDraft'];
        }
    });

    $scope.$watch('events', function(){
        var num = 0;
        $scope.events.forEach(function(e){
            if(e.selected){
                num++;
            }
        });

        $scope.numSelectedEvents = num;
    }, true);

    $scope.selectAll = function(){
        $scope.events.forEach(function(e){
            if(!e.hidden){
                e.selected = true;
            }
        });
    };

    $scope.deselectAll = function(){
        $scope.events.forEach(function(e){
            if(!e.hidden){
                e.selected = false;
            }
        });
    };

    $scope.eventFilterTimeout = null;

    $scope.filterEvents = function(){
        $timeout.cancel($scope.eventFilterTimeout);
        $scope.eventFilterTimeout = $timeout(function() {
            var keywords = $scope.data.eventFilter.toLowerCase().split(' ');

            $scope.events.forEach(function(evt,i){
                var show = true;
                keywords.forEach(function(keyword){
                    keyword = keyword.trim();
                    var match = false;
                    if(evt.name.toLowerCase().indexOf(keyword) >= 0){
                        match = true;
                    }else if(evt.owner.name.toLowerCase().indexOf($scope.data.eventFilter.toLowerCase()) >= 0){
                        match = true;
                    }else if(evt.statusText.indexOf(keyword) >= 0){
                        match = true;
                    }else if(evt.classificacaoEtaria.toLowerCase().indexOf(keyword) >= 0){
                        match = true;
                    }else{
                        evt.occurrences.forEach(function(o){
                            if(o.space.name.toLowerCase().indexOf($scope.data.eventFilter.toLowerCase()) >= 0){
                                match = true;
                            }
                        });

                        evt.terms.linguagem.forEach(function(term){
                            if(term.toLowerCase().indexOf(keyword) >= 0){
                                match = true;
                            }
                        });
                    }

                    show = show && match;

                });
                evt.hidden = !show;

            });
        },500);

    };

    $scope.processing = false;

    $scope.publishSelectedEvents = function(){
        var ids = [],
        events = [];

        if($scope.data.processing){
            return;
        }

        $scope.events.forEach(function(e,i){
            if(e.selected){
                ids.push(e.id);
                events.push(e);
            }
        });

        if(!ids.length){
            return;
        }

        $scope.data.processingText = labels['publishing...'];

        $scope.data.processing = true;

        ProjectEventsService.publish(ids.toString()).success(function(){
            MapasCulturais.Messages.success(labels['eventsPublished']);
            events.forEach(function(e){
                e.status = 1;
                e.statusText = labels['statusPublished'];
            });

            $scope.data.processing = false;
        });
    };

    $scope.unpublishSelectedEvents = function(){
        var ids = [],
        events = [];

        if($scope.data.processing){
            return;
        }

        $scope.events.forEach(function(e,i){
            if(e.selected){
                ids.push(e.id);
                events.push(e);
            }
        });

        if(!ids.length){
            return;
        }

        $scope.data.processingText = labels['savingAsDraft'];

        $scope.data.processing = true;

        ProjectEventsService.unpublish(ids.toString()).success(function(){
            MapasCulturais.Messages.success(labels['savedAsDraft']);
            events.forEach(function(e){
                e.status = 0;
                e.statusText = labels['statusDraft'];
            });

            $scope.data.processing = false;
        });
    };


    $scope.toggle = false;
}]);

module.controller('ProjectController', ['$scope', '$rootScope', '$timeout', 'EditBox', '$http', 'UrlService', function ($scope, $rootScope, $timeout, EditBox, $http, UrlService) {
    var adjustingBoxPosition = false;
    var labels = MapasCulturais.gettext.moduleProject;

    $scope.editbox = EditBox;
    $scope.data = angular.extend({
        uploadSpinner: false,
        spinner: false,
        
        relationApiQuery: {}
    }, MapasCulturais);

    $scope.fns = {};

    $scope.hideStatusInfo = function(){
        jQuery('#status-info').slideUp('fast');
    };

    $scope.openEditBox = function(id, e){
        EditBox.open(id, e);
    };

}]);

})(angular);
