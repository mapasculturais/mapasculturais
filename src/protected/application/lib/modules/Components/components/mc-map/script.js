app.component('mc-map', {
    template: $TEMPLATES['mc-map'],

    // define os eventos que este componente emite
    emits: ['ready', 'openPopup', 'closePopup'],

    components: {
        LMap: VueLeaflet.LMap,
        LTileLayer: VueLeaflet.LTileLayer,
        LControlLayers: VueLeaflet.LControlLayers,
        // LIcon: VueLeaflet.LIcon,
        // LMarker: VueLeaflet.LMarker,
        // LTooltip: VueLeaflet.LTooltip,
        // LPopup: VueLeaflet.LPopup,
        // LPolyline: VueLeaflet.LPolyline,
        // LPolygon: VueLeaflet.LPolygon,
        // LRectangle: VueLeaflet.LRectangle,
    },

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('mc-map');
        return { hasSlot, text };
    },

    beforeMount() {
        const $map = this;
        const group = L.markerClusterGroup({
            maxClusterRadius: 40,
            iconCreateFunction: function(cluster) {
                const entities = $map.countClusterEntityTypes(cluster);
                const count = cluster.getChildCount();
                let className = '';
                if (entities.agent && entities.space && entities.event) {
                    className = 'agent_space_event__background mc-map-marker mc-map-cluster'
                } else if (entities.agent && entities.space) {
                    className = 'agent_space__background mc-map-marker mc-map-cluster'
                } else if (entities.agent && entities.event) {
                    className = 'agent_event__background mc-map-marker mc-map-cluster'
                } else if (entities.space && entities.event) {
                    className = 'space_event__background mc-map-marker mc-map-cluster'
                } else if (entities.agent) {
                    className = 'agent__background mc-map-marker mc-map-cluster'
                } else if (entities.space) {
                    className = 'space__background mc-map-marker mc-map-cluster'
                } else if (entities.event) {
                    className = 'event__background mc-map-marker mc-map-cluster'
                }

                return L.divIcon({className: '', html: `<div class="${className}">${count}</div>`});
            }
        });

        this.markersGroup = group;
    },

    created() {
        if (!this.center.length && this.center.lat !== 0 && this.center.lng !== 0) {
            this.defaultZoom = 16;
        }        
    },
    mounted() {
        window.addEventListener('mc-map-filter-open', this.closePopups);
    },
    unmounted() {
        window.removeEventListener('mc-map-filter-open', this.closePopups);
    },

    beforeUpdate() {
        this.populateMarkerClusterGroup();
    },
    
    props: {
        center: {
            type: Object,
            default: $MAPAS.config.map.center
        },

        entities: {
            type: Array,
            default: []
        }
    },

    data() {
        return {
            tileServer: $MAPAS.config.map.tileServer,
            defaultZoom: $MAPAS.config.map.defaultZoom,
            approximateZoom: $MAPAS.config.map.approximateZoom,
            preciseZoom: $MAPAS.config.map.preciseZoom,
            maxZoom: $MAPAS.config.map.maxZoom,
            minZoom: $MAPAS.config.map.minZoom,
            popupEntity: null,
        };
    },

    computed: {},

    methods: {
        async handleMapSetup () {
            const leaflet = Vue.toRaw(this.$refs.map.leafletObject);
            leaflet.markersGroup = this.markersGroup;
            leaflet.addLayer(leaflet.markersGroup);
            leaflet.on('popupopen', function(e) {
                var px = leaflet.project(e.target._popup._latlng); // find the pixel location on the map where the popup anchor is
                px.y -= e.target._popup._container.clientHeight/2; // find the height of the popup container, divide by 2, subtract from the Y axis of marker location
                leaflet.panTo(leaflet.unproject(px),{animate: true}); // pan to new center
            });
            this.$emit('ready', leaflet);
        },

        createMarker (entity) {
            const $this = this;
            const leaflet = Vue.toRaw(this.$refs.map.leafletObject);
            const options = { title: entity.name, clickable: true, draggable: false };
            const marker = L.marker(entity.location, options);
            
            marker.entity = entity;

            if(this.hasSlot('popup')) {
                marker.on('click', () => {
                    const api = new API(entity.__objectType || entity['@entityType']);
                    const entityPromise = api.findOne(entity.id);
    
                    $this.$emit('openPopup', {marker, leaflet, entityPromise, entity});
                    

                    entityPromise.then((entity) => {
                        $this.popupEntity = entity;
                        $this.$nextTick(() => {
                            L.popup()
                                .setLatLng(entity.location)
                                .setContent($this.$refs.popup.innerHTML)
                                .openOn(leaflet)
                                .on('remove', () => {
                                    $this.$emit('closePopup', {marker, leaflet, entity});
                                });
    
                        });
                    });
                });
            }

            return marker;
        },
        closePopups() {
            this.$refs.map.leafletObject.closePopup();
        },

        countClusterEntityTypes(cluster, result) {
            result = result || {
                agent: 0,
                space: 0,
                event: 0,
            };

            for (let child of cluster._childClusters) {
                this.countClusterEntityTypes(child, result);
            }

            for (let marker of cluster._markers) {
                const entity = marker.entity;
                const entityType = entity['@icon'] || entity.__objectType || entity['@entityType'];
                result[entityType]++
            }
            
            return result;
        },

        populateMarkerClusterGroup () {
            this.currentMarkers = this.currentMarkers || {};

            this.updateTimeout = setTimeout(() => {
                const $map = this;
                const icons = {
                    agent1: $map?.$refs?.agent1?.outerHTML,
                    agent2: $map?.$refs?.agent2?.outerHTML,
                    space: $map?.$refs?.space?.outerHTML,
                    event: $map?.$refs?.event?.outerHTML
                };
                const markersToAdd = [];
                const markersToRemove = [];
                const markersOfEntities = {};

                for (let entity of this.entities) {
                    if (!this.currentMarkers[entity.__objectId]) {
                        const marker =  this.createMarker(entity);
                        let objectType = entity['@icon'] || entity.__objectType || entity['@entityType'];

                        if(objectType == 'agent') {
                            objectType += entity.type.id;
                        }

                        marker.setIcon(L.divIcon({ className: '', html: icons[objectType]}));

                        this.currentMarkers[entity.__objectId] = marker;

                        markersToAdd.push(marker);
                    }
                    markersOfEntities[entity.__objectId] = this.currentMarkers[entity.__objectId];
                }

                for (let objectId in this.currentMarkers) {
                    if (!markersOfEntities[objectId]) {
                        markersToRemove.push(this.currentMarkers[objectId]);
                        delete this.currentMarkers[objectId];
                    }
                }
                this.markersGroup.addLayers(markersToAdd);
                this.markersGroup.removeLayers(markersToRemove);
            }, 100);
        }
    },
});