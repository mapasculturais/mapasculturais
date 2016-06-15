(function(angular){
    "use strict";
    
    var module = angular.module('entity.module.relatedSeals', ['ngSanitize']);

    module.config(['$httpProvider',function($httpProvider){
        $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
        $httpProvider.defaults.transformRequest = function(data) {
            var result = angular.isObject(data) && String(data) !== '[object File]' ? $.param(data) : data;
            
            return result;
        };
    }]);
    
    module.factory('RelatedSealsService', ['$http', '$rootScope', function($http, $rootScope){
        var controllerId = null, 
            entityId = null,
            baseUrl = MapasCulturais.baseURL.substr(-1) === '/' ?  MapasCulturais.baseURL : MapasCulturais.baseURL + '/';
    
        try{ controllerId = MapasCulturais.request.controller; }catch (e){};
        try{ entityId = MapasCulturais.entity.id; }catch (e){};
        
        return {
            controllerId: controllerId,
            
            entityId: entityId,
            
            getUrl: function(action){
                return baseUrl + controllerId + '/' + action + '/' + entityId;
            },
            
            create: function(sealId){
                return $http.post(this.getUrl('createSealRelation'), {sealId: sealId}).
                        success(function(data, status){
                            if(status === 202){
                                MapasCulturais.Messages.alert('Sua requisição para relacionar o selo <strong>' + data.Seal.name + '</strong> foi enviada.');
                            }
                            $rootScope.$emit('relatedSeal.created', data);
                        }).
                        error(function(data, status){
                            $rootScope.$emit('error', { message: "Cannot create related seal", data: data, status: status });
                        });
            },
            
            remove: function(sealId){
                return $http.post(this.getUrl('removeSealRelation'), {sealId: sealId}).
                    success(function(data, status){
                        $rootScope.$emit('relatedSeal.removed', data);
                    }).
                    error(function(data, status){
                        $rootScope.$emit('error', { message: "Cannot remove related seal", data: data, status: status });
                    }); 
            },
        };
    }]);
    
    module.controller('RelatedSealsController', ['$scope', '$rootScope', 'RelatedSealsService', 'EditBox', function($scope, $rootScope, RelatedSealsService, EditBox) {
        $scope.editbox = EditBox;
        
        $scope.seals = [];
        
        $scope.relations = MapasCulturais.entity.sealRelations;
        
        for(var i in MapasCulturais.allowedSeals)
            $scope.seals.push(MapasCulturais.allowedSeals[i]);
        
        $scope.showCreateDialog = {};
        
        $scope.isEditable = MapasCulturais.isEditable;
        
        $scope.data = {};
        
        $scope.avatarUrl = function(url){
            if(url)
                return url;
            else
                return MapasCulturais.assets.avatarSeal;
        };
        
        $scope.createRelation = function(entity){
            var _scope = this.$parent;
            
            RelatedSealsService.create(entity.id).
                    success(function(data){
                        $scope.relations.push(data);
                    });
        };
        
        $scope.deleteRelation = function(relation){
            var oldRelations = $scope.relations.slice();
            var i = $scope.relations.indexOf(relation);
            
            $scope.relations.splice(i,1);
            
            RelatedSealsService.remove(relation.seal.id).
                    error(function(){
                    	$scope.relations = oldRelations;
                    });
        };
        
        $scope.sealRelated = function(seal) {
        	var related = $scope.relations.find(function(r){
        		if(r.seal.id === seal.id) {
        			return r;
        		};
        	});
        	
        	return related;
        };
        
        
        $scope.filterResult = function( data, status ){
            if(relations.length > 0){
                var ids = relations.map( function( el ){ return el.Seal.id; } );

                data = data.filter( function( e ){ 
                    if( ids.indexOf( e.id ) === -1 ) 
                        return e;
                } );
            }
            return data;
        };
    }]);
})(angular);