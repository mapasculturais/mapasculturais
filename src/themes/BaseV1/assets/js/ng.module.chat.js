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

                var qdata = '/api/chatMessage/find?@select=id,createTimestamp,payload,user.profile.{id,name,singleUrl}&thread=EQ(' + data + ')';

                return $http.get(qdata).
                    success(function (data, status) {
                        for (var i = 0; i < data.length; i++) {
                            data[i].date = moment(data[i].createTimestamp.date).format('DD/MM/YYYY');
                            data[i].time = moment(data[i].createTimestamp.date).format('HH:mm');
                        }
                        $rootScope.$emit('something', { message: "Something was done", data: data, status: status });
                    }).
                    error(function (data, status) {
                        $rootScope.$emit('error', { message: "Cannot do something", data: data, status: status });
                    });

            },
            
            create: function (data) {
              
                var url = MapasCulturais.createUrl('chatMessage');

                return $http.post(url, {thread: data.thread, payload: data.payload}).
                    success(function (data, status) {
                        data.date = moment(data.createTimestamp.date).format('DD/MM/YYYY');
                        data.time = moment(data.createTimestamp.date).format('HH:mm');
                        $rootScope.$emit('something', { message: "Something was done", data: data, status: status });
                    }).
                    error(function (data, status) {
                        $rootScope.$emit('error', { message: "Cannot do something", data: data, status: status });
                    });
            },
        };
    }]);

    app.controller('ChatController', ['$scope', '$rootScope', '$timeout', 'ChatService', 'EditBox', function ($scope, $rootScope, $timeout, ChatService, EditBox) {
        var adjustingBoxPosition = false;
        $scope.editbox = EditBox;
        $scope.data = {
            threadId: null,
            messages: [],
            newMessage: '',
            previousMessage: {},
            spinner: false,
            sending: false,
            chatFocusTime: 60000,
            currentUserId: MapasCulturais.userProfile.id,
            chatFocus: false,
            
            apiQuery: {

            }
        };

        $scope.setFocus = function (fieldId) {   
            if(!fieldId){
                fieldId = "event";
            }  

            document.querySelector('.txt-'+fieldId).focus();
        }

        $scope.toogleTalk = function (fieldId) { 
            if(!fieldId){
                fieldId = "event";
            } 

            var chat = document.querySelector('.chat-'+fieldId);            
            chat.classList.toggle('hidden')
        }
        
        $scope.checkToggleTalk = function(fieldId){
            if(!fieldId){
                fieldId = "event";
            } 

            var chat = document.querySelector('.chat-'+fieldId);       
            
            if(chat.classList.contains("hidden")){
                chat.classList.toggle('hidden')
            }
        }

        $scope.getClassName = function(fieldId){
            if(!fieldId){
                return "event";
            } 

            return fieldId;
        }

        $scope.init = function(threadId) {
            $scope.data.threadId = threadId;
        }

        $scope.isChatClosed = function() {
            return $rootScope.closedChats && $rootScope.closedChats[$scope.data.threadId];
        }

        $scope.$watch('data.chatFocus', function(new_val, old_val) {
            
            if(new_val != old_val){
                clearInterval($scope.data.interval);
                $scope.data.chatFocusTime = new_val ? 10000 : 60000;
                $scope.data.interval = setInterval(getLatestMessages, $scope.data.chatFocusTime);           
            }
        });

        var adjustBoxPosition = function () {
            setTimeout(function () {
                adjustingBoxPosition = true;
                $('#module-name-owner-button').click();
                adjustingBoxPosition = false;
            });
        };

        var getLatestMessages = function () {
            ChatService.find($scope.data.threadId).success(function (data, status, headers) {
                $scope.data.messages.forEach(function (current) {
                    
                    var found = false;
                    data.forEach(function(returnApi){
                        if(current.id == returnApi.id){
                            found = true;
                        }
                    });

                    if(!found){
                        data.push(current);
                    }
                });

                $scope.data.messages = data;
            });
        };

        // Verifica se pressionou CTRl+ENTER
        $scope.handleCtrlEnterAction = function (e) {
            var chatMessage = this.data.newMessage;
            if (e.ctrlKey && e.key === 'Enter' && chatMessage.trim() !== '') {
                e.preventDefault();
                $scope.sendMessage(chatMessage);
            }
        };

        $scope.sendMessage = function (message) {
            if($scope.data.sending){
                return;
            }

            var newMessage = {
                thread: $scope.data.threadId,
                payload: message
            };

            $scope.data.sending = true;

            ChatService.create(newMessage).success(function (data, status, headers) {
                $scope.data.messages.push(data);
                $scope.data.newMessage = '';
                $scope.data.sending = false;
            });


        }

        $rootScope.$on('repeatDone:findEntity:find-entity-module-name-owner', adjustBoxPosition);

        $scope.$watch('data.spinner', function (ov, nv) {
            if (ov && !nv)
                adjustBoxPosition();
        });

        setTimeout(() => {
            ChatService.find($scope.data.threadId).success(function (data, status, headers) {
                $scope.data.messages = data;
            });

            // @todo: Mover para local adequado
            $("textarea.new-message").each(function () {
                this.setAttribute("style", "height:" + (this.scrollHeight) + "px;overflow-y:hidden;min-height:52px");
            }).on("input", function () {
                this.style.height = "auto";
                this.style.height = (this.scrollHeight) + "px";
            });
        }, 0);

        $scope.data.interval = setInterval(getLatestMessages, $scope.data.chatFocusTime);

    }]);
})(angular);