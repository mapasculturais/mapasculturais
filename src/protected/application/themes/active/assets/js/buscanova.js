(function(angular){

    var app = angular.module('search', ['ng-mapasculturais', 'SearchService', 'SearchSpatial', 'angularSpinner']);

    app.controller('SearchController', ['$scope', '$location', 'SearchService', "$rootScope", /*"ngProgress", "progress",*/ function($scope, $location, SearchService, $rootScope/*, ngProgress, progress*/){

		// $scope.$watch('[agentSearch.types,agentSearch.areas,agentSearch.searchTerm,spaceSearch.types,spaceSearch.areas,spaceSearch.searchTerm,spaceAccessibility,isVerified]', function(){
		// 	$scope.searchManager.update();
		// });


	 	$scope.searchManager = {
	 		fue: 1,

		 	setupSearchEntity : function (properties){
				return {
	                class : properties.class,
	                hash : properties.hash || '',
	                label : properties.label ||'',
	                cssClass : properties.cssClass ||'',
	                enabled : properties.enabled || false,
	                inactive : properties.inactive || false,
	                showFilters : properties.showFilters || false,
	                types : properties.types || MapasCulturais.entityTypes[properties.class],
	                areas : properties.areas || MapasCulturais.taxonomyTerms.area.map(function(el){ return {name: el}; }).slice(),
	                searchTerm : properties.searchTerm || '',
	                termInputValue : properties.termInputValue || '',
	                results : properties.results || [],
	                markers : properties.markers || [],
	                allResultsCache : [],
	                filters : {

	                },
	                typeFilters : properties.typeFilters || function(){ return this.types.map( function (e){ if(e.selected) return e.id }).filter(function(e){if(e) return e}); },
        			areaFilters : properties.areaFilters || function(){ return this.areas.map( function (e){ if(e.selected) return e.name }).filter(function(e){if(e) return e}); },
        			hasFilters : properties.hasFilters || function(){ return (this.typeFilters().length>0 || this.areaFilters().length>0 || this.searchTerm.length>0 ); },
	                cleanFilters : properties.cleanFilters || function (){
	                	this.types.map(function(e){e.selected = false;});
            			this.areas.map(function(e){e.selected = false;});
            			this.searchTerm  = '';
	                	this.termInputValue  = '';
	                },
	                search : function (){

	                },
	                clearResults : function (){
	                	delete this.results;
	                	delete this.markers;
	                	this.searchTerm  = '';
	                	this.termInputValue  = '';
	                	this.results = [];
	                	this.markers = [];
	                	this.cleanFilters();
	                },
	            }
		 	},
	 		isSearchCombined : function () { return $scope.combinedSearc ===true },
		 	map : {
		 		target : '',//window.leaflet.map
		 		resultLayer: null

		 	},
            entities : {
                agent : {hash:'agentes', label:'Agente', cssClass : 'agente'},
                space : {hash:'espacos', label:'Espaço', cssClass : 'espaco',
                	hasFilters : function () { return ($scope.spaceAccessibility || this.typeFilters().length>0 || this.areaFilters().length>0 || this.searchTerm.length>0 ); },
	                cleanFilters :  function () { $scope.spaceAccessibility = false; this.types.map(function(e){e.selected = false;}); this.areas.map(function(e){e.selected = false;}); },
            	},
            },
            setupEntities : function (){
            	for(var entity in this.entities){
            		var options = this.entities[entity];
            		options.class = entity;
                	this.entities[entity] = this.setupSearchEntity(options);
                }
            },

            initMap:  function() {
            	
            	var self = this;
	            window.leaflet.map.removeLayer(window.leaflet.marker);

                // CLEAR REMOVE LAYERS
	            if(self.map.resultLayer){
	            	
                    //remove all markers clearing layers
                    self.map.resultLayer.clearLayers();

                    //remove drawing if more than one
                    if(window.leaflet.map.drawnItems){
                        if(!$scope.searchManager.filterLocation) {
                            window.leaflet.map.drawnItems.clearLayers();
                            if(window.leaflet.locationMarker) { window.leaflet.map.removeLayer(window.leaflet.locationMarker);}
                        }else if(Object.keys(window.leaflet.map.drawnItems._layers).length > 1) {
                            var lastLayer = window.leaflet.map.drawnItems.getLayers().pop();
                            window.leaflet.map.drawnItems.clearLayers();
                            window.leaflet.map.drawnItems.addLayer(lastLayer);
                        }
                    }

   	            	return;
	            }


	            self.map.resultLayer = new L.markerClusterGroup(
	                { spiderfyOnMaxZoom: true, showCoverageOnHover: false, zoomToBoundsOnClick: false, spiderfyDistanceMultiplier:1.3,
						maxClusterRadius: 60,
						iconCreateFunction: function (cluster) {
							var getChildMarkers = function getChildMarkers(cluster){
								var markers = cluster._markers.slice();
								cluster._childClusters.forEach(function(child_cluste){
									markers = markers.concat(getChildMarkers(child_cluste));
								});
								return markers;
							};

							var markers = getChildMarkers(cluster);

							var hasAgent = false, hasSpace = false;

							for(var i in markers){
								if(markers[i].entityType === 'agent')
									hasAgent = true;
								
								if(markers[i].entityType === 'space')
									hasSpace = true;
								
								if(hasAgent && hasSpace)
									break;
							}
							
							var iconClass = 'leaflet-cluster';

							if(hasAgent)
								iconClass += ' cluster-agent';

							if(hasSpace)
								iconClass += ' cluster-space';
							
							
							return L.divIcon({ html: cluster.getChildCount(), className: iconClass, iconSize: L.point(40, 40) });
						},
	                }
	            );

	            var __c = 0;
	            var _addLayer = self.map.resultLayer._addLayer;

	            self.map.resultLayer._addLayer = function(layer, zoom){
	            	var r = _addLayer.apply(this,[layer, zoom]);// console.log(layer, zoom, __c++)
	            	
	            	var p = layer.__parent;

	            	while(p){
	            		p.hasEntityType = p.hasEntityType || {};
	            		p.hasEntityType[layer.entityType] = true;
	            		p = p.__parent;
	            	}
	            	
	            	return r;
	            };

	            self.map.resultLayer.addTo(window.leaflet.map);

	            self.map.resultLayer.on('clusterclick', function (a) {
	
	                if(a.layer._childCount <= 6)
	                    a.layer.spiderfy();
	                else{
	                    a.layer.zoomToBounds();
	                }
	                // for (i in a.layer._group._featureGroup._layers) {
	                //     a.layer._group._featureGroup._layers[i].showLabel();
	                // };
	            });
	            self.map.resultLayer.on('clustermouseover', function(a){
	            	console.log(a.layer);
	            	//a.layer.spiderfy()
		            });
	            $scope.markers = self.map.resultLayer;
	        },

            init : function(){
            	window.SEARCHMAN = this;

        		var self = this;
		        angular.element(document).ready(function(){
		        	self.initMap();
		        });


				this.setupEntities();
				this.setInitialState();

				

				//gambi reescrevendo:
            	$scope.agentSearch = this.entities.agent;
            	$scope.spaceSearch = this.entities.space;
            	$scope.isSelected = this.util.isSelected;
            	//$scope.searchTermKeyUp = this.util.searchTermKeyUp;
            	$scope.numberFixedLength = this.util.numberFixedLength;
		        $scope.selectAgentType = function ($index) {
		            $scope.agentSearch.types.selected=$index;
		            $scope.agentSearch.types.forEach(function(e){ e.selected=false; });
		            if($index !== undefined)
		                $scope.agentSearch.types[$scope.agentSearch.types.selected].selected=true;
		            $scope.searchManager.updateEntity('agent');
		        };
				$scope.searchTermKeyUp = function($event){
		 			if($event.keyCode === 13) {
		 				$scope.searchManager.entities[$event.target.dataset.entity].searchTerm =  $event.target.value;
		                $scope.searchManager.updateEntity($event.target.dataset.entity);
		            }
		 		};
	            $scope.cleanAllFilters = function (){
	            	$scope.searchManager.getEnabledEntities().forEach(function(entity){
	            		$scope.searchManager.entities[entity].clearResults();
	            		$scope.searchManager.entities[entity].cleanFilters();
	            	});
	            	$scope.filterVerified = false;

                    // remove as layers de localização
                    $scope.searchManager.filterLocation = false;
                    window.leaflet.map.drawnItems.clearLayers();
                    if(window.leaflet.locationMarker) { window.leaflet.map.removeLayer(window.leaflet.locationMarker);}

	            	$scope.searchManager.update();
	            };


		        $scope.filterVerified = false;
		        $scope.toggleCombined = this.toggleCombined;
            },
            setInitialState : function (){
            	$scope.viewLoading = true;
            	this.disableAllEntities();
            	//console.log($location);
            	
           //  	$scope.$on('$locationHashStart', function(event, newUrl, oldUrl){
        			// console.log($location);
           //  	});
            	if($location.path() == '/'+this.entities.space.hash){
            		//$location.path('');
            		this.entities.space.enabled = this.entities.space.showFilters = true;
            		this.updateEntity('space');
            	} else {
            		this.entities.agent.enabled = this.entities.agent.showFilters = true;
            		this.updateEntity('agent');
            	}
            },
            disableAllEntities : function() {
            	Object.keys(this.entities).forEach( //lista todas as chaves do objeto como um array para poder usar forEach
            		function(entity){
            			this.entities[entity].enabled = false;
            			this.entities[entity].showFilters = false;
            			this.entities[entity].clearResults();
            		},this // para usar this dentro do callback do forEach
        		);
            },
            enableAllEntities : function ( ) {
            	Object.keys(this.entities).forEach(function(entity){this.entities[entity].enabled = true},this);
        	},
            getEnabledEntities : function () {
            	return Object.keys(this.entities).filter ( this.util.isElementEnabled, this.entities );
            },
            getDisabledEntities : function () {
            	return Object.keys(this.entities).filter ( this.util.isElementDisabled, this.entities );
            },
            getEnabledEntitiesMarkers : function () {
				// var arr =[];
            	// this.getEnabledEntities().map(function(entity){arr.concat(this.entities[entity].markers)}, this);
            	// console.log('concat',arr)
            	return this.entities.agent.markers.concat(this.entities.space.markers);
            },
            hideAllFilterBars : function () {
            	Object.keys(this.entities).forEach ( function (entity) { this.entities[entity].showFilters=false }, this );
            },
            clearAllDisabledEntities : function () {
            	this.getDisabledEntities().forEach ( function(entity) { this.entities[entity].clearResults(); },this );
            },
            tabClick : function (entity) {
				$scope.viewLoading = true;
            	if(!$scope.combinedSearch) {
            	
            		// if(this.entities[entity].enabled)
            		// 	return;
            		this.hideAllFilterBars();
            		this.disableAllEntities();
            		this.entities[entity].showFilters = true;
            		this.entities[entity].enabled = true;
        			//this.updateEntity(entity)
        		}else{//combined
        		
        			// if the entity is already enabled and it's the last one enabled, avoid disabling
        			if(this.entities[entity].enabled){
        				if(this.getEnabledEntities().length == 1){
        					$scope.viewLoading = false;
        					return;
        				}else{
        					this.entities[entity].enabled = false;
        					this.clearAllDisabledEntities();
        				}
        				
        			}else{ //enable and search an entity
        				this.entities[entity].enabled = true;
    					//this.updateEntity(entity);
        			}
    				
    				//this.entities[entity].enabled = !this.entities[entity].enabled;	
    				this.clearAllDisabledEntities();
        		}
        		this.update();
            },
            tabOver : function (entity) {
            	if($scope.combinedSearch){
       	 			this.hideAllFilterBars();
          	  		this.entities[entity].showFilters = true;
            	}
            },
            toggleCombined : function () {
            	var self = $scope.searchManager;
            	if(!$scope.combinedSearch) {
            		$scope.combinedSearch = true;
            		self.enableAllEntities();
            		self.update();
            	}else{
            		$scope.combinedSearch = false;
            		if(self.getEnabledEntities().length > 1){
            			self.disableAllEntities();
            			self.setInitialState();
            		}
            		else{
            			//keep state
            			//self.update();
            		}
            	}
            },
            updateEntity : function (entity) {
            	var self = this;
            	$scope.viewLoading = true;

                var searchData = this.getSearchDataForEntity(entity);

    			SearchService(entity, searchData, 1).success(function(results){
	            	self.processResults(self.entities[entity], results);
	            	//if(results == 0)
	            });
            },

            getSearchDataForEntity : function (entity) {
	            var searchEntity = this.entities[entity];
	            var searchTerm = searchEntity.searchTerm.replace(' ', '*');
	            var searchEntityTypes = searchEntity.typeFilters();
	            var searchEntityAreas = searchEntity.areaFilters();

	            var searchData = {};

	            searchData.name = 'ilike(*'+searchTerm+'*)';
	            if(searchEntityTypes.length) searchData.type = 'in('+searchEntityTypes+')';
	            if(searchEntityAreas.length) searchData['term:area'] = 'in('+searchEntityAreas+')';

	            if(entity == 'space' && $scope.spaceAccessibility) searchData.acessibilidade = 'eq(Sim)';
	            if($scope.filterVerified) searchData.isVerified = 'eq(true)';

                var filterLocation = $scope.searchManager.filterLocation;

                if(filterLocation)
                    searchData._geoLocation = 'GEONEAR('+filterLocation.lng+','+filterLocation.lat+','+filterLocation.radius+')';


                return searchData;
            },

            update : function(){
            	var self = this;
            	this.getEnabledEntities().forEach(function(entity){
            		self.updateEntity(entity);
        			//console.log(self.entities[entity]);
            	});
            	return false;
            },

            createMarkers: function(searchEntity, results){
            	//console.log('process results', results);
    			var entity = searchEntity.class;
    			
                searchEntity.results = results;

                searchEntity.resultsWithoutMarker = 0;
                delete searchEntity.markers;
                searchEntity.markers = [];
                results.forEach(function(item){
                	var icon = '';
                	var label = '';
					var mi = entity + '-' + item.id;

					window.lmarkers = window.lmarkers || {};

                    if(true || !window.lmarkers[mi]){
                    	//console.log(item.files)
                        if(item.location.latitude == 0 && item.location.longitude == 0){
                            searchEntity.resultsWithoutMarker++;
                            //console.log(searchEntity.resultsWithoutMarker.lengt);
                            return;
                        }
                       	
                        label = item.name
                     //    + '<br>'
                     //    + '<span style="font-weight:normal;font-style:italic">'
                     //    	+ searchEntity.label +' ('+(item.type?item.type.name:'')
                    	// + ')</span>';
                        icon = entity;
                        //console.log('icon', icon);
                        if(entity == 'agent' && item.type.name == 'Coletivo'){
                            icon = 'coletivo';	
                        }

                        window.lmarkers[mi] = new L.marker(
	                                new L.LatLng(item.location.latitude,item.location.longitude),
	                                window.leaflet.iconOptions[icon]
	                            )
	                            .bindLabel(label)
	                            .on('click', function(){
	                            	var listItem = document.querySelector('#'+entity+'-result-'+item.id);
	                                var itemURL = listItem.querySelector(' a.js-single-url');
	                                var infobox = document.querySelector('#infobox');
	                                var infoboxContent = infobox.querySelector('article');
									infoboxContent.innerHTML = listItem.innerHTML;
									infobox.style.display = 'block';
									infobox.className = 'objeto';
									infobox.classList.add(searchEntity.cssClass);
	                                console.log(listItem);
	                                //itemURL.setAttribute('target', '_blank');
	                                //a.click();

	                            });

                        window.lmarkers[mi].entityType = entity;

                    }
                    	

                    searchEntity.markers.push(window.lmarkers[mi]);
                });
            },

            updateMap: function(){
            	console.log('enabled count',this.getEnabledEntities().length);
            	console.log('processing',this.fue);
            	console.log('loading',$scope.viewLoading);
            	if(this.getEnabledEntities().length == this.fue){
					this.initMap();
					$scope.markers.addLayers(this.getEnabledEntitiesMarkers());
					this.fue = 1;
					$scope.viewLoading = false;
    			}else{
					this.fue++;
				}
            },

            processResults : function (searchEntity, results) {
            	this.createMarkers(searchEntity, results);

                this.updateMap();

            },

		 	util:{
		 		isSelected : function(element) { return element && element.selected === true;},
		 		isElementEnabled : function(element) { return this[element] && this[element].enabled === true;},
		 		isElementDisabled : function(element) { return this[element] && this[element].enabled === false;},
	 			numberFixedLength : function (n, len) {
		            var num = parseInt(n, 10);
		            len = parseInt(len, 10);
		            if (isNaN(num) || isNaN(len)) { return n;}
		            num = ''+num;
		            while (num.length < len) { num = '0'+num;}
		            return num;
		        },

		 	},
        };
        $scope.searchManager.init();



		$scope.$watch(function(){
			//console.log($location.search());
			return $location.search();
		});


    }]); //End SearchController

})(angular);