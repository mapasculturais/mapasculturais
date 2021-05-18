(function (angular) {
    "use strict";
    var module = angular.module('ng.support', []);

    module.config(['$httpProvider', function ($httpProvider) {
        $httpProvider.defaults.headers.put['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
        $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
        $httpProvider.defaults.transformRequest = function (data) {
            var result = angular.isObject(data) && String(data) !== '[object File]' ? $.param(data) : data;
            return result;
        };
    }]);

    module.controller('SupportModal', ['$scope', 'SupportService', '$window', function ($scope, SupportService, $window) {
        $scope.data = {
            openModal: false,
            userPermissions: {},
            defaultAvatar: MapasCulturais.assets.avatarAgent,
            fields: MapasCulturais.entity.registrationFieldConfigurations.concat(MapasCulturais.entity.registrationFileConfigurations),
            selectAll: false,
            selectedExist: false,
        };

        $scope.data.fields.sort(function (a, b) {            
            return a.displayOrder - b.displayOrder;
        });

        $scope.returnTypeDescription = function(fieldType) {
            var fields = MapasCulturais.registrationFieldTypes.reduce(function (prev, curr) {
                prev[curr.slug] = curr;
                return prev;
            }, {});

            return fields[fieldType].name;
        }

        $scope.data.fields.map(function(item){
            if(item.fieldType == "file"){
                item.ref = item.groupName;
                item.typeDescription = 'Arquivo'; // @todo: internacionalizar
            }else{
                item.ref = item.fieldName;
                item.typeDescription = $scope.returnTypeDescription(item.fieldType);
            }
        });

        $scope.selectedExist = function(){
            $scope.data.selectedExist = false;
            $scope.data.fields.forEach(function(field){
                if(field.checked){
                    $scope.data.selectedExist = true;
                    return;
                }
            });
        }

        $scope.selectAll = function(){            
            $scope.data.selectAll = !$scope.data.selectAll; 
            $scope.data.selectedExist = $scope.data.selectAll;        
            $scope.data.fields.forEach(function(field){
                field.checked = $scope.data.selectAll;
            });
        }

        $scope.clearChecked = function(){
            $scope.data.fields.forEach(function(field){
                field.checked = false;
                $scope.data.selectAll = false;
                $scope.data.selectedExist = false;
            }); 
        }

        $scope.getSelectedFields = function(){
            return $scope.data.fields.filter(function(field){
                return field.checked;
            });
        }

        $scope.setPermissionsOnSelected = function(agentId){
            $scope.data.setPermissions = {};
            $scope.data.fields.forEach(function(field){
                if(field.checked){
                    $scope.all = true;
                    $scope.data.setPermissions[field.ref] = $scope.data.permission;
                    $scope.data.userPermissions[field.ref] = $scope.data.permission;
                }
            });

            SupportService.savePermission(MapasCulturais.entity.id, agentId, $scope.data.setPermissions).success(function (data, status, headers) {
                $scope.all = false;
                $scope.clearChecked();
                MapasCulturais.Messages.success('Permissoes salvas com sucesso.');
            });
        }
        
        $scope.savePermission = function (agentId) {
            SupportService.savePermission(MapasCulturais.entity.id, agentId, $scope.data.userPermissions).success(function (data, status, headers) {
                MapasCulturais.Messages.success('Permissoes salvas com sucesso.');
            });
        }

        // script para remover o scroll do body quando o modal está aberto
        var modal = document.querySelectorAll('.bg-support-modal');
        modal.forEach((each)=>{
            var contains = each.classList.contains('open');
            var body = document.querySelector('body');
            var observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    if (mutation.attributeName == "class") {
                        var currentClass = mutation.target.classList.contains('open');
                        if (contains !== currentClass) {
                            contains = currentClass;
                            if (currentClass) {
                                body.classList.add('modal-oppened');
                            } else {
                                body.classList.remove('modal-oppened');
                            }
                        }
                    }
                });
            });
            observer.observe(each, { attributes: true });
        });
    }]);

    module.controller('Support', ['$scope', 'SupportService', '$window', function ($scope, SupportService, $window) {
        $scope.data = {
            agents: [],
            agentsRelations: [],
            agentsRelationsIgnoreSearch: []
        };

        SupportService.getAgentsRelation(MapasCulturais.entity.id).success(function (data, status, headers) {
            data.forEach(function (item){
                $scope.data.agentsRelationsIgnoreSearch.push(item.agent.id);                
            });
            $scope.data.agentsRelations = data;
        });
        
        $scope.avatarUrl = function(entity){
            if(entity['@files:avatar.avatarSmall'])
                return entity['@files:avatar.avatarSmall'].url;
            else if(entity.avatar && entity.avatar.avatarSmall)
                return entity.avatar.avatarSmall.url;
            else
                return MapasCulturais.defaultAvatarURL;
        };

        $scope.findAgents = function(){            
            $scope.searchTimeOut = null;
            $scope.data.spinner = true;
            $scope.searchTimeOut = setTimeout(function() {
                MapasCulturais.searchIgnore = $scope.data.agentsRelationsIgnoreSearch.join([',']) || null; 
                              
                SupportService.findAgents(MapasCulturais.searchIgnore, $scope.data.searchAgents).success(function (data, status, headers) {
                    $scope.data.agents = data;
                    $scope.data.spinner = false;
                 });
            },1500);
        }

       
        $scope.editBoxCancel = function(){
            MapasCulturais.EditBox.close('#add-age');
            $scope.data.agents = {};  
            $scope.data.searchAgents = "";
        }

        $scope.editBoxOpen = function(){
            MapasCulturais.EditBox.open('#add-age');         
        }

        $scope.selectAgent = function(agent){
            var data = {
                agentId: agent.id,
                group: '@support'
            };
         
            
            SupportService.createRelation(MapasCulturais.entity.id, data).success(function (data, status, headers) {
                MapasCulturais.Messages.success('Permissoes salvas com sucesso.');
                $scope.data.agentsRelations.push(data);
                $scope.data.agents = {};
                $scope.data.searchAgents = "";
                $scope.data.agentsRelationsIgnoreSearch.push(data.agent.id);
                MapasCulturais.EditBox.close('#add-age');                
            });
        }

        
        $scope.deleteAgentRelation = function(agentId){
            var data = {
                agentId: agentId,
                group: '@support'
            };

          
            if(confirm('Voce realmente deseja remover a relação deste agente?')){
                SupportService.deleteRelation(MapasCulturais.entity.id, data).success(function (data, status, headers) {
                    $scope.data.agentsRelations.forEach(function (item, index){
                        if(item.agent.id == agentId){
                            $scope.data.agentsRelations.splice(index,1);
                        }                        
                    });   

                    $scope.data.agentsRelationsIgnoreSearch.forEach(function (item, index){
                        if(item == agentId){
                            $scope.data.agentsRelationsIgnoreSearch.splice(index,1);
                        }                       
                    }); 
                    
                    MapasCulturais.Messages.success('Agente removido com sucesso!');
                });
            }
        }


    }]);

    module.controller('SupportForm',['$scope', 'SupportService','$window', function($scope, SupportService, $window){        
        $scope.userAllowedFields = MapasCulturais.userAllowedFields
        
        $scope.canUserEdit = function(field){
            if(MapasCulturais.entity.hasControl == true){
                return true;
            }

            if (field.fieldType == 'file'){
                if (MapasCulturais.userAllowedFields[field.groupName] == 'rw'){
                    return true
                }
                return false
            }
            if (MapasCulturais.userAllowedFields[field.fieldName] == 'rw'){
                return true
            }
        return false

        };    
    }]);
    module.factory('SupportService', ['$http', '$rootScope', 'UrlService', function ($http, $rootScope, UrlService) {
        return {
            getAgentsRelation: function (opportunityId, data) {

                var url = MapasCulturais.createUrl('support', 'getAgentsRelation', {opportunityId});

                return $http.get(url, data).
                    success(function (data, status, headers) {
                        $rootScope.$emit('registration.create', { message: "Reports found", data: data, status: status });
                    }).
                    error(function (data, status) {
                        $rootScope.$emit('error', { message: "Reports not found for this opportunity", data: data, status: status });
                    });
            },
            savePermission: function (opportunityId, agentId, data) {

                var url = MapasCulturais.createUrl('support', 'opportunityPermissions', {opportunityId, agentId});

                return $http.put(url, data).
                    success(function (data, status, headers) {
                        $rootScope.$emit('registration.create', { message: "Reports found", data: data, status: status });
                    }).
                    error(function (data, status) {
                        $rootScope.$emit('error', { message: "Reports not found for this opportunity", data: data, status: status });
                    });
            },
            findAgents: function (searchIgnore, data) {
                
                var complete = "";
                if(searchIgnore){
                    complete = '&id=!IN('+searchIgnore+')';
                }

                var qdata = '?@files=(avatar.avatarSmall):url&@limit=20&@order=name&@page=1&@select=id,name,type,shortDescription,terms&name=ILIKE(*'+data+'*)&parent=NULL()&status=GT(0)&type=EQ(1)'+complete;

                var url = MapasCulturais.createUrl('api/agent', 'find') + qdata;

                return $http.get(url, data).
                    success(function (data, status, headers) {
                        $rootScope.$emit('registration.create', { message: "Reports found", data: data, status: status });
                    }).
                    error(function (data, status) {
                        $rootScope.$emit('error', { message: "Reports not found for this opportunity", data: data, status: status });
                    });
            },
            createRelation: function (opportunityId, data) {
                var url = MapasCulturais.createUrl('opportunity', 'createAgentRelation', [opportunityId]);

                return $http.post(url, data).
                    success(function (data, status, headers) {
                        $rootScope.$emit('registration.create', { message: "Reports found", data: data, status: status });
                    }).
                    error(function (data, status) {
                        $rootScope.$emit('error', { message: "Reports not found for this opportunity", data: data, status: status });
                    });
            },
            deleteRelation: function (opportunityId, data) {
                var url = MapasCulturais.createUrl('opportunity', 'removeAgentRelation', [opportunityId]);

                return $http.post(url, data).
                    success(function (data, status, headers) {
                        $rootScope.$emit('registration.create', { message: "Reports found", data: data, status: status });
                    }).
                    error(function (data, status) {
                        $rootScope.$emit('error', { message: "Reports not found for this opportunity", data: data, status: status });
                    });
            },
        };
    }]);

})(angular);
