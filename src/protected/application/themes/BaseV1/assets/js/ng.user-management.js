(function (angular) {
    "use strict";        
    
    var module = angular.module('ng.usermanager.app', ['search.service.find']);

    module.factory('userManagermentService', ['$http', '$rootScope', '$q', function($http, $rootScope, $q) {
        
        var baseUrl = MapasCulturais.baseURL.substr(-1) === '/' ?  MapasCulturais.baseURL + 'api/' : MapasCulturais.baseURL + '/api/';

        return {
            
            getUrl: function(entity, action){
                return baseUrl + `${entity}/` + action;
            },
            
            getUser: function(idUser) {
                return $http.get(this.getUrl('user', 'findOne') + `?@select=*&id=EQ(${idUser})`);
            },

            getAgents: function(agentsIds) {
                return $http.get(this.getUrl('agent', 'find') + `?@select=id,name,,subsite.name,singleUrl&id=IN(${agentsIds.join()})`);                
            }
            
        };
    }]);

    module.controller('UserManagermentController', ['$scope', '$rootScope', '$window', '$timeout', 'searchService', 'userManagermentService', function ($scope, $rootScope, $window, $timeout, searchService, userManagermentService) {
        var timeoutTime = 300;

        $rootScope.resetPagination = function() {
            $rootScope.pagination = {
                agent: 1,
                space: 1,
                event: 1,
                project: 1,
                opportunity: 1,
            };
        }

        $scope.data = {
            global : {
                filterEntity: null,
                viewMode: 'list',
                locationFilters: {
                    enabled: null
                }
            }
        }        

        $rootScope.$on('searchResultsReady', function(ev, results){
            if($scope.data.global.viewMode !== 'list')
                return;
            $rootScope.isPaginating = false;

            if(results.paginating) {
                $scope.agents = $scope.agents.concat(results.agent ? results.agent : []);
                $scope.events = $scope.events.concat(results.event ? results.event : []);
                $scope.spaces = $scope.spaces.concat(results.space ? results.space : []);
                $scope.projects = $scope.projects.concat(results.project ? results.project : []);
                $scope.opportunities = $scope.opportunities.concat(results.opportunity ? results.opportunity : []);
            } else {
                $scope.agents = results.agent ? results.agent : [];
                $scope.events = results.event ? results.event : [];
                $scope.spaces = results.space ? results.space : [];
                $scope.projects = results.project ? results.project : [];
                $scope.opportunities = results.opportunity ? results.opportunity : [];
            }
        });

        $scope.loadAgent = function ( ) {
            $scope.user.agents.spinnerShow = true;
            userManagermentService.getAgents($scope.user.agents)
                .success(function (data) {
                    console.log("success");
                    $scope.user.agents.list = data;
                    $scope.user.agents.spinnerShow = false;
                    console.log($scope.user.agents);
                })
                .error(function (data) {
                    $scope.user.agents.spinnerShow = false;
                });
            
            // for (var agent in $scope.user.agents) {
            //     var idAgent = $scope.user.agents[agent];
            //     agents[idAgent] = "AgentAPI";
            // }
        }
        
        $scope.getUserByAgent = function($idAgent) {
            $scope.user = {};
            userManagermentService.getUser($idAgent).
                success(function (data) {
                    $scope.user = data;
                    $scope.user.lastLoginTimestamp.date = Date.parse($scope.user.lastLoginTimestamp.date);
                    $scope.user.createTimestamp.date = Date.parse($scope.user.createTimestamp.date);
                    MapasCulturais.Modal.open('#user-managerment-dialog');
                    
                    $scope.loadAgent();

                }).error(function(data) {
                    console.log("erro!");
                });
        }


        if($('#user-managerment-search-form').length){
            $('#campo-de-busca').focus();
            $('#search-filter .submenu-dropdown li').click(function() {
                var params = {
                    entity: $(this).data('entity'),
                    keyword: $('#campo-de-busca').val()
                };

                $scope.data.global.filterEntity = params.entity;
                $scope.data[params.entity] = {
                    keyword: params.keyword,
                    showAdvancedFilters:false,
                    filters: {}
                };

                $window.$timout = $timeout;
                $timeout.cancel($scope.timer);
                $scope.timer = $timeout(function() {
                    $rootScope.$emit('searchDataChange', $scope.data);
                }, timeoutTime);
            }).on('keydown', function(event){
                if(event.keyCode === 13 || event.keyCode === 32){
                    event.preventDefault();
                    $(this).click();
                } else if(event.keyCode === 27) {
                    $(this).attr('css', '');
                    $(this).blur();
                    $('#campo-de-busca').focus();
                    return false;
                }
            });
        }

    }]);
})(angular);

        

