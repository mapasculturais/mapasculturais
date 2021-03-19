(function (angular) {
    "use strict";

    var app = angular.module('ng.module.chat', [
        "mc.directive.editBox",
        "mc.module.notifications",
        "ngSanitize"
    ]);

    app.config(['$httpProvider', function ($httpProvider) {
        $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
        $httpProvider.defaults.transformRequest = function (data) {
            var result = angular.isObject(data) && String(data) !== '[object File]' ? $.param(data) : data;

            return result;
        };
    }]);

    app.factory('ChatService', ['$http', '$rootScope', function ($http, $rootScope) {
        return {
            serviceProperty: null,
            getUrl: function () {
                return MapasCulturais.baseURL // + controllerId  + '/' + actionName 
            },
            doSomething: function (param) {
                var data = {
                    prop: name
                };
                return $http.post(this.getUrl(), data).
                success(function (data, status) {
                    $rootScope.$emit('something', { message: "Something was done", data: data, status: status });
                }).
                    error(function (data, status) {
                        $rootScope.$emit('error', { message: "Cannot do something", data: data, status: status });
                    });
                },
                
            find: function (data) {

                var qdata = '';

                return $http.get(qdata).
                    success(function (data, status) {
                        $rootScope.$emit('something', { message: "Something was done", data: data, status: status });
                    }).
                    error(function (data, status) {
                        $rootScope.$emit('error', { message: "Cannot do something", data: data, status: status });
                    });

            },
            
            create: function (message) {
                
                // var url = MapasCulturais.createUrl('chatMessage', {});

                // return $http.post(url, message).
                //     success(function (data, status) {
                //         $rootScope.$emit('something', { message: "Something was done", data: data, status: status });
                //     }).
                //     error(function (data, status) {
                //         $rootScope.$emit('error', { message: "Cannot do something", data: data, status: status });
                //     });
            },
        };
    }]);

    app.controller('ChatController', ['$scope', '$rootScope', '$timeout', 'ChatService', 'EditBox', function ($scope, $rootScope, $timeout, ChatService, EditBox) {
        var adjustingBoxPosition = false;
        $scope.editbox = EditBox;
        $scope.data = {
            threadId: null,
            messages: [],
            spinner: false,
            
            apiQuery: {

            }
        };

        $scope.setThreadId = function(threadId) {
            $scope.data.threadId = threadId;
        }

        var adjustBoxPosition = function () {
            setTimeout(function () {
                adjustingBoxPosition = true;
                $('#module-name-owner-button').click();
                adjustingBoxPosition = false;
            });
        };

        $scope.sendMessage = function (message) {
            ChatService.create();

            var newMessage = {
                title: 'Nome',
                body: message
            }

            // user_id
            // chat_thread_id
            $scope.data.messages.push(newMessage);
            $scope.data.newMessage = '';

        }

        $rootScope.$on('repeatDone:findEntity:find-entity-module-name-owner', adjustBoxPosition);

        $scope.$watch('data.spinner', function (ov, nv) {
            if (ov && !nv)
                adjustBoxPosition();
        });

        ChatService.find($scope.data.threadId).success(function (data, status, headers) {

            //$scope.data.apiMetadata = JSON.parse(headers()['api-metadata']);
            $scope.data.messages = data;
          
        });

    }]);
})(angular);