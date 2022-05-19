(function (angular) {
    "use strict";
    
    var module = angular.module('usermanager.app', ['search.service.find', 'infinite-scroll', 'mc.module.notifications']);

    module.filter('capitalize', function() {
        return function(input) {
          return (!!input) ? input.charAt(0).toUpperCase() + input.substr(1).toLowerCase() : '';
        }
    });

    module.factory('userManagermentService', ['$http', '$rootScope', '$q', function($http, $rootScope, $q) {
        
        var baseUrl = MapasCulturais.baseURL.substr(-1) === '/' ?  MapasCulturais.baseURL : MapasCulturais.baseURL + '/';

        return {
            
            getUrl: function(entity, action){
                return baseUrl + entity + '/' + action;
            },

            getAgents: function(userId) {
                return $http.get(this.getUrl('api/agent', 'find') + '?@select=id,name,subsite.name,singleUrl,deleteUrl,archiveUrl,publishUrl,unarchiveUrl,undeleteUrl,destroyUrl,status,__agentRelations.hasControl,__agentRelations.agent.userId&user=EQ(' + userId + ')');
            },

            getRelatedsAgentControl: function(userId) {
                return $http.get(this.getUrl('user', 'relatedsAgentsControl') + '?userId=' + userId);
            },

            getSpaces: function(userId) {
                return $http.get(this.getUrl('api/space', 'find') + '?@select=id,name,subsite.name,singleUrl,deleteUrl,archiveUrl,publishUrl,unarchiveUrl,undeleteUrl,destroyUrl,status,__agentRelations.hasControl,__agentRelations.agent.userId&user=EQ(' + userId + ')');
            },
            
            getRelatedsSpacesControl: function(userId) {
                return $http.get(this.getUrl('user', 'relatedsSpacesControl') + '?userId=' + userId);
            },

            getEvents: function(userId) {
                return $http.get(this.getUrl('user', 'events') + '?userId=' + userId);
            },

            getRelatedsEventsControl: function(userId) {
                return $http.get(this.getUrl('user', 'relatedsEventsControl') + '?userId=' + userId);
            },

            getHistory: function(userId) {
                return $http.get(this.getUrl('user', 'history') + '?userId=' + userId);
            },
        };
    }]);

    module.controller('UserManagermentController', ['$scope', '$rootScope', '$window', '$timeout', 'searchService', 'userManagermentService', function ($scope, $rootScope, $window, $timeout, searchService, userManagermentService) {
        var timeoutTime = 300;

        var rls = [];
        if (MapasCulturais.infoAdmin && MapasCulturais.infoAdmin.roles)
            rls = MapasCulturais.infoAdmin.roles;

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
            },
            infoAdmin : {
                roles: rls
            }
        }
        
        $scope.hasSubsites = function () {
            if(MapasCulturais.infoAdmin && MapasCulturais.infoAdmin.roles && MapasCulturais.infoAdmin.roles.users){
                return Object.keys(MapasCulturais.infoAdmin.roles.users).length > 1;
            } else {
                return false;
            }
        };

        $scope.spinnerShow = true;

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
            $scope.spinnerShow = false;
        });

        $scope.addMore = function(entity) {
            $scope.spinnerShow = true;
            var entityName = "";
            if($scope.data.global.viewMode !== 'list')
                return;

            if(entity !== $scope.data.global.filterEntity)
                return;

            if($rootScope.isPaginating)
                return;

            if(entity == 'opportunity') {
                entityName = 'opportunities';
            } else {
                entityName = entity + 's'
            }

            if($scope[entityName].length === 0 || $scope[entityName].length < 10)
                return;

            $rootScope.pagination[entity]++;
            // para não chamar 2 vezes o search quando está carregando a primeira página (o filtro mudou)
            if($rootScope.pagination[entity] > 1)
                $rootScope.$emit('resultPagination', $scope.data);
        };

        $scope.load = function ($userId) {
            $scope.user = { 'id':$userId, 
                            'agents': {'spinnerShow':true},
                            'spaces': {'spinnerShow':true},
                            'events': {'spinnerShow':true},
                       'permissions': {'spinnerShow':true},
                           'history': {'spinnerShow':true}
                         }            
            $scope.loadHistory($userId);
        }

        $scope.loadHistory = function($userId) {
            $scope.user.history.spinnerShow = true;
            userManagermentService.getHistory($userId)
                .success(function (data) {
                    data.forEach(function(el){
                        el.objectType = el.objectType.substring(24);
                        el.createTimestamp.date = new Date(el.createTimestamp.date);
                    });
                    $scope.user.history.list = data;
                })
                .then(function (data) {
                    $scope.user.history.spinnerShow = false;
                });
        }
        
        $scope.hasAdmin = function($subsite) {
            if(!$subsite)
                return false;
                
            for (var i = 0; i < MapasCulturais.subsitesAdmin.length; i++) {
                var element = MapasCulturais.subsitesAdmin[i];
                if (element.id == $subsite.id) {
                    return true;
                }
            }
            return false;
        }

        $scope.hasControl = function($list, $entity) {
            var $useId = MapasCulturais.userId;
            if (!$list) {
                return false;
            }

            for (var i = 0; i < $list.length; i++) {
                var element = $list[i];
                if(element.hasControl == true && element[$entity].userId == $useId) {
                    return true;
                }
            }
            return false;
        }

        $scope.selectGroupAdmin = 'saasSuperAdmin';
        $scope.selectSubsite = 'MapasCulturais';

        function isValidCPF(cpf){
            return cpf.match(/^\d{3}\.\d{3}\.\d{3}\-\d{2}$/) || cpf.match(/^\d{11}$/);
        }

        if($('#user-managerment-search-form').length) {
            $('#campo-de-busca').focus();
            $('#search-filter .submenu-dropdown li').click(function() {
                $scope.spinnerShow = true;

                var params = {
                    entity: $(this).data('entity'),
                    keyword: $('#campo-de-busca').val()
                };

                $scope.data.userManagerment =  true;
                $scope.data.global.filterEntity = params.entity;
                $scope.data[params.entity] = {
                    keyword: !isValidCPF(params.keyword) ? params.keyword : '',
                    showAdvancedFilters:false,
                    filters: isValidCPF(params.keyword) ? {documento: `eq(${params.keyword})`} : {}
                };
                

                $window.$timout = $timeout;
                $timeout.cancel($scope.timer);
                $scope.timer = $timeout(function() {
                    $rootScope.$emit('searchDataChange', $scope.data);
                }, timeoutTime);
                var $dropdown = $(this).parents('.dropdown'),
                $submenu = $dropdown.find('.submenu-dropdown');
                $submenu.hide();
            });
            
            $('#campo-de-busca').on('keydown', function(event){
                if(event.keyCode === 13) {
                    event.preventDefault();
                    $('#search-filter .submenu-dropdown li#agents-filter').click();
                } else if(event.keyCode === 27) {
                    $(this).attr('css', '');
                    $(this).blur();
                    $('#campo-de-busca').focus();
                    $('#campo-de-busca').val('')
                    return false;
                }
            });
        }

        $('.user-managerment-infos table.entity-table caption').click(function() {
            $(this).closest('table').find('tbody').fadeToggle("fast", "linear");
            $(this).closest('table').find('thead').fadeToggle("fast", "linear");
        });

        $('#user-managerment-adminChangePassword').click(function() {
            let password = $('#admin-set-user-password').val();
            let email = $('#email-to-admin-set-password').val();
            $.post(MapasCulturais.baseURL + 'auth/adminchangeuserpassword', {password: password, email: email}, function(r){
                MapasCulturais.Modal.close('#admin-change-user-password');
                MapasCulturais.Messages.success('Senha alterada com sucesso');
                $('#admin-set-user-password').val("");
            });
        })

        $('#user-managerment-adminChangeEmail').click(function() {
            let new_email = $('#new-email').val();
            let email = $('#email-to-admin-set-email').val();
            $.post(MapasCulturais.baseURL + 'auth/adminchangeuseremail', {new_email: new_email, email: email}, function(r){
                if(r.error) {
                    alert(r.error);
                    return;
                }
                MapasCulturais.Modal.close('#admin-change-user-email');
                MapasCulturais.Messages.success('Email alterado com sucesso');
                location.reload();
            });
        })

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
            $('#funcao-do-agente-user-managerment .js-options li').click(function() {
                var element_selected = $(this).parent().parent().parent().find('.js-selected span');
                var roleToRemove = element_selected.data('role');
                var roleToAdd = $(this).data('role');
                var label = $(this).find('span').html();
                var subsite_id = element_selected.data('subsite');
                var change = function() {
                    element_selected.html(label);
                    element_selected.data('role', roleToAdd);
                };
                if(roleToRemove)
                    $.post(MapasCulturais.baseURL + 'agent/removeRole/' + MapasCulturais.userProfileId, {role: roleToRemove, subsiteId: subsite_id}, function(r){ if(r && !r.error) change(); });

                if(roleToAdd)
                    $.post(MapasCulturais.baseURL + 'agent/addRole/' + MapasCulturais.userProfileId, {role: roleToAdd, subsiteId: subsite_id}, function(r){ if(r && !r.error) change(); });

                MapasCulturais.Messages.success("Permissão atribuida");
            });
        }

        $(".tablinks").click(function() {
            var tab = $(this).data('tab');
            var entity = $(this).data('entity');
            $('#' + entity + ' .tab-content-table').hide();
            $('#' + entity + ' .tab-table button').removeClass("active");
            $('#' + entity + ' #' + tab).show();
            $(this).addClass("active");
        });
    }]);
})(angular);