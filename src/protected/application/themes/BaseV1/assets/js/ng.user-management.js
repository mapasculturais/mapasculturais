(function (angular) {
    "use strict";
    
    var module = angular.module('ng.usermanager.app', ['search.service.find']);

    module.filter('capitalize', function() {
        return function(input) {
          return (!!input) ? input.charAt(0).toUpperCase() + input.substr(1).toLowerCase() : '';
        }
    });

    module.factory('userManagermentService', ['$http', '$rootScope', '$q', function($http, $rootScope, $q) {
        
        var baseUrl = MapasCulturais.baseURL.substr(-1) === '/' ?  MapasCulturais.baseURL : MapasCulturais.baseURL + '/';

        return {
            
            getUrl: function(entity, action){
                return `${baseUrl}${entity}/${action}`;
            },

            getAgents: function(userId) {
                return $http.get(this.getUrl('api/agent', 'find') + `?@select=id,name,subsite.name,singleUrl&user=EQ(${userId})`);
            },

            getRelatedsAgentControl: function(userId) {
                return $http.get(this.getUrl('user', 'relatedsAgentsControl') + `?userId=${userId}`);
            },

            getSpaces: function(userId) {
                return $http.get(this.getUrl('api/space', 'find') + `?@select=id,name,subsite.name,singleUrl&user=EQ(${userId})`);
            },
            
            getRelatedsSpacesControl: function(userId) {
                return $http.get(this.getUrl('user', 'relatedsSpacesControl') + `?userId=${userId}`);
            },

            getEvents: function(userId) {
                return $http.get(this.getUrl('user', 'events') + `?userId=${userId}`);
            },

            getRelatedsEventsControl: function(userId) {
                return $http.get(this.getUrl('user', 'relatedsEventsControl') + `?userId=${userId}`);
            },

            getHistory: function(userId) {
                return $http.get(this.getUrl('user', 'history') + `?userId=${userId}`);
            },
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

        $scope.load = function ($userId) {
            $scope.user = { 'id':$userId, 
                            'agents': {'spinnerShow':true},
                            'spaces': {'spinnerShow':true},
                            'events': {'spinnerShow':true},
                       'permissions': {'spinnerShow':true},
                           'history': {'spinnerShow':true}
                         }
            $scope.loadAgent($userId);
            $scope.loadSpace($userId);
            $scope.loadEvents($userId);
            $scope.loadHistory($userId);
        }

        $scope.loadAgent = function ($userId) {
            $scope.user.agents.spinnerShow = true;
            userManagermentService.getAgents($userId)
                .success(function (data) {
                    $scope.user.agents.list = data;
                    $scope.loadRelatedsAgentControl($userId);
                })
                .error(function (data) {
                    $scope.user.agents.spinnerShow = false;
                });
        }

        $scope.loadRelatedsAgentControl =  function($userId) {
            $scope.user.agents.spinnerShow = true;
            $scope.user.agents.relatedsAgents = [];
            userManagermentService.getRelatedsAgentControl($userId)
                .success(function (data) {
                    $scope.user.agents.relatedsAgents = data;
                })
                .then(function (data) {
                    $scope.user.agents.spinnerShow = false;
                });
        }

        $scope.loadSpace = function ($userId) {
            $scope.user.spaces.spinnerShow = true;
            userManagermentService.getSpaces($userId)
                .success(function (data) {
                    $scope.user.spaces.list = data;                    
                    $scope.loadRelatedsSpacesControl($userId);
                })
                .error(function (data) {
                    $scope.user.spaces.spinnerShow = false;
                });
        }

        $scope.loadRelatedsSpacesControl =  function($userId) {
            $scope.user.spaces.spinnerShow = true;
            $scope.user.spaces.relatedsSpaces = [];
            userManagermentService.getRelatedsSpacesControl($userId)
                .success(function (data) {
                    $scope.user.spaces.relatedsSpaces = data;
                })
                .then(function (data) {
                    $scope.user.spaces.spinnerShow = false;
                });
        }

        $scope.loadEvents = function ($userId) {
            $scope.user.events.spinnerShow = true;
            userManagermentService.getEvents($userId)
                .success(function (data) {
                    $scope.user.events.list = data;
                    $scope.loadRelatedsEventsControl($userId);
                })
                .error(function (data) {
                    $scope.user.spaces.spinnerShow = false;
                });
        }

        $scope.loadRelatedsEventsControl =  function($userId) {
            $scope.user.events.spinnerShow = true;
            $scope.user.events.relatedsSpaces = [];
            userManagermentService.getRelatedsEventsControl($userId)
                .success(function (data) {
                    $scope.user.events.relatedsSpaces = data;
                })
                .then(function (data) {
                    $scope.user.events.spinnerShow = false;
                });
        }

        $scope.loadHistory = function($userId) {
            $scope.user.history.spinnerShow = true;
            userManagermentService.getHistory($userId)
                .success(function (data) {
                    $scope.user.history.list = data;
                })
                .then(function (data) {
                    $scope.user.history.spinnerShow = false;
                });
        }
        
        if($('#user-managerment-search-form').length) {
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
                var $dropdown = $(this).parents('.dropdown'),
                $submenu = $dropdown.find('.submenu-dropdown');
                $submenu.hide();
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

        $('.user-managerment-infos table.entity-table caption').click(function() {
            $(this).closest('table').find('tbody').fadeToggle("fast", "linear");
            $(this).closest('table').find('thead').fadeToggle("fast", "linear");
        });

        $('#user-managerment-addRole').click(function() {
            var subsite_id = $('#subsiteList').val();
            var roleToAdd = $('#permissionList').val();
            $.post(MapasCulturais.baseURL + 'agent/addRole/' + MapasCulturais.userProfileId, {role: roleToAdd, subsiteId: subsite_id}, function(r){
                if(r && !r.error)
                    MapasCulturais.Modal.close('#add-roles');
                    location.reload();
            });
        });

        if($('#funcao-do-agente-user-managerment').length) {
            $('#funcao-do-agente-user-managerment .js-options li').click(function(){
                var roleToRemove = $('#funcao-do-agente-user-managerment .js-selected span').data('role');
                var roleToAdd = $(this).data('role');
                var label = $(this).find('span').html();
                var subsite_id = $('#funcao-do-agente-user-managerment .js-selected span').data('subsite');
                
                var element_selected = $(this).parent().parent().parent().find('.js-selected span');
                var change = function() {
                    element_selected.html(label);
                    element_selected.data('role', roleToAdd);
                    //$('#funcao-do-agente-user-managerment .js-selected span').html(label);
                    //$('#funcao-do-agente-user-managerment .js-selected span').data('role', roleToAdd);
                };
                if(roleToRemove)
                    $.post(MapasCulturais.baseURL + 'agent/removeRole/' + MapasCulturais.userProfileId, {role: roleToRemove, subsiteId: subsite_id}, function(r){ if(r && !r.error) change(); });
    
                if(roleToAdd)
                    $.post(MapasCulturais.baseURL + 'agent/addRole/' + MapasCulturais.userProfileId, {role: roleToAdd, subsiteId: subsite_id}, function(r){ if(r && !r.error) change(); });

                MapasCulturais.Messages.success("Permissão atribuida");
            });
        }


    }]);
})(angular);

        

