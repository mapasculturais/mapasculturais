(function(angular) {
	"use strict";

	var module = angular.module('entity.module.relatedSeals', [ 'ngSanitize' ]);
    
    var labels = MapasCulturais.gettext.relatedSeals;

	module
			.config([
					'$httpProvider',
					function($httpProvider) {
						$httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
						$httpProvider.defaults.transformRequest = function(data) {
							var result = angular.isObject(data)
									&& String(data) !== '[object File]' ? $
									.param(data) : data;

							return result;
						};
					} ]);

	module
			.factory(
					'RelatedSealsService',
					[
							'$http',
							'$rootScope',
							function($http, $rootScope) {
								var controllerId = null, entityId = null, baseUrl = MapasCulturais.baseURL
										.substr(-1) === '/' ? MapasCulturais.baseURL
										: MapasCulturais.baseURL + '/';

								try {
									controllerId = MapasCulturais.request.controller;
								} catch (e) {
								}
								;
								try {
									entityId = MapasCulturais.entity.id;
								} catch (e) {
								}
								;

								return {
									controllerId : controllerId,

									entityId : entityId,

									getUrl : function(action) {
										return baseUrl + controllerId + '/'
												+ action + '/' + entityId;
									},

									create : function(sealId) {
										return $http
												.post(
														this
																.getUrl('createSealRelation'),
														{
															sealId : sealId
														})
												.success(
														function(data, status) {
															if (status === 202) {
																MapasCulturais.Messages
																		.alert(labels['requestSent'].replace('{{seal}}', '<strong>'+data.Seal.name+'</strong>'));
															}
															$rootScope
																	.$emit(
																			'relatedSeal.created',
																			data);
														})
												.error(
														function(data, status) {
															$rootScope
																	.$emit(
																			'error',
																			{
																				message : "Cannot create related seal",
																				data : data,
																				status : status
																			});
														});
									},

									remove : function(sealId) {
										return $http
												.post(
														this
																.getUrl('removeSealRelation'),
														{
															sealId : sealId
														})
												.success(
														function(data, status) {
															$rootScope
																	.$emit(
																			'relatedSeal.removed',
																			data);
														})
												.error(
														function(data, status) {
															$rootScope
																	.$emit(
																			'error',
																			{
																				message : "Cannot remove related seal",
																				data : data,
																				status : status
																			});
														});
									},
								};
							} ]);

	module
			.controller(
					'RelatedSealsController',
					[
							'$scope',
							'$rootScope',
							'RelatedSealsService',
							'EditBox',
							function($scope, $rootScope, RelatedSealsService,
									EditBox) {
								var sealFound = false;
								$scope.editbox = EditBox;
						        
								$scope.canRelateSeal = MapasCulturais.canRelateSeal;
						        $scope.seals = [];
						        $scope.relations = MapasCulturais.entity.sealRelations;
						        
						        for(var i in MapasCulturais.allowedSeals) {
						        	sealFound = false;
						        	for(var y in $scope.relations) {
							            if($scope.relations[y].seal.id === MapasCulturais.allowedSeals[i].id) {
							            	sealFound = true;
							            	break;
							            }
						            }
						        	if(sealFound === false) {
						        		$scope.seals.push(MapasCulturais.allowedSeals[i]);
						        	}
						        }
						        
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
						            for(var i in $scope.seals) {
							            if($scope.seals[i].id === entity.id)
							            	$scope.seals.splice(i,1);
						            }
						            
						            RelatedSealsService.create(entity.id).
						                    success(function(data){
						                        $scope.relations.push(data);
						                    });
						        };
						        
						        $scope.deleteRelation = function(relation){
						            var oldRelations = $scope.relations.slice();
						            var i = $scope.relations.indexOf(relation);
						            
						            $scope.relations.splice(i,1);
						            
						            for(var i in MapasCulturais.allowedSeals) {
							        	sealFound = false;
							            if(relation.seal.id === MapasCulturais.allowedSeals[i].id) {
							            	$scope.seals.push(MapasCulturais.allowedSeals[i]);
							            	break;
							            }
							        }
						            
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

							} ]);
})(angular);
