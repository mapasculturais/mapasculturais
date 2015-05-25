(function(angular){
    "use strict";

    var module = angular.module('Notifications', []);

    module.factory('NotificationService', ['$log','$http', '$q', '$rootScope', function($log, $http, $q, $rootScope){

        var service = {};

        service.url = MapasCulturais.baseURL + 'notification/';

        service.get = function (){
            var deferred = $q.defer();
            $http.get(
                MapasCulturais.baseURL+'api/notification/find/?&@select=id,status,isRequest,createTimestamp,message,approveUrl,request.permissionTo.approve,request.permissionTo.reject,request.requesterUser.id&user=eq(@me)&@ORDER=createTimestamp%20DESC'
            ).success(function(data){
                deferred.resolve(data);
            }).error(function(){
                deferred.reject('There was an error');
            });
            return deferred.promise;
        };
        service.updateOne = function(id, action){
            var deferred = $q.defer();
            $http.get(
                service.url+action+'/'+id)
            .success(function(data){
                deferred.resolve(data);
            }).error(function(){
                deferred.reject('There was an error');
            });
            return deferred.promise;
        };
        return service;
    }]);

    module.controller('NotificationController', ['$log', '$sce', '$scope', '$rootScope', '$interval', 'NotificationService', function($log, $sce, $scope, $rootScope, $interval, NotificationService){

        $scope.panelURI = MapasCulturais.baseURL+'panel';
        $scope.MapasCulturais = MapasCulturais;

        MapasCulturais.notifications.forEach(function(value,index){
            MapasCulturais.notifications[index].message = $sce.trustAsHtml(value.message);
        });
        $scope.data = MapasCulturais.notifications;

        var getNotifications = function (){
            NotificationService.get().then(function(data){
                if(data){
                    data.forEach(function(value,index){
                        data[index].message = $sce.trustAsHtml(value.message);
                    });
                    $scope.data = data;
                }
            });
        };

        $interval(function(){
            getNotifications();
        }, 60*1000);

        $scope.approve = function(id){
            NotificationService.updateOne(id,'approve').then(getNotifications);
        };
        $scope.reject = function(id){
            NotificationService.updateOne(id,'reject').then(getNotifications);
        };
        $scope.delete = function(id){
            NotificationService.updateOne(id,'delete').then(getNotifications);
        };

        $scope.adjustScroll = function(){
            jQuery('.notifications .submenu ul').slimScroll({
                position: 'right',
                distance: '0px',
                color: '#000',
                height: '316px',
                alwaysVisible: true,
                railVisible: true
            });
        };
    }]);

    module.directive('onLastRepeat', function() {
        return function(scope, element, attrs) {
            if (scope.$last) { // all are rendered
                scope.$evalAsync(attrs.onLastRepeat);
            }
        };
    });

    angular.element(document).ready(function(){
        var app = null;
        //checks existence of default search and Entity (singles') angular modules
        ['search', 'Entity'].forEach(function(moduleName){
            if(!app){
                try{
                    app = angular.module(moduleName);
                }catch(e){}
            }
        });
        //if not found, bootstraps notification modules
        if(!app)
            angular.bootstrap(document, ['Notifications']);
        app = angular.module('Notifications');
    });

})(angular);


