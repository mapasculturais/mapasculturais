


/*
 Copyright (c) 2013, Jason Sanford
 Leaflet Vector Layers is a library for showing geometry objects
 from multiple geoweb services in a Leaflet map
*/

(function(a){a.lvector={VERSION:"1.5.1",noConflict:function(){a.lvector=this._originallvector;return this},_originallvector:a.lvector}})(this);

/*Seccionei a parte inicial do do arquivo dist e adicionei a classe baes layer com os eventos mouseover*/
/*Ver https://github.com/bmcbride/leaflet-vector-layers/blob/680e9b33131d580bca3817c8da18925e37de0ca2/src/layer/Layer.js*/

/*
 * lvector.Layer is a base class for rendering vector layers on a Leaflet map. It's inherited by AGS, A2E, CartoDB, GeoIQ, etc.
 */

lvector.Layer = L.Class.extend({

    //
    // Default options for all layers
    //
    options: {
        fields: "",
        scaleRange: null,
        map: null,
        uniqueField: null,
        visibleAtScale: true,
        dynamic: false,
        autoUpdate: false,
        autoUpdateInterval: null,
        popupTemplate: null,
        popupOptions: {},
        singlePopup: false,
        symbology: null,
        showAll: false
    },

    initialize: function(options) {
        L.Util.setOptions(this, options);
    },

    //
    // Show this layer on the map provided
    //
    setMap: function(map) {
        if (map && this.options.map) {
            return;
        }
        if (map) {
            this.options.map = map;
            if (this.options.scaleRange && this.options.scaleRange instanceof Array && this.options.scaleRange.length === 2) {
                var z = this.options.map.getZoom();
                var sr = this.options.scaleRange;
                this.options.visibleAtScale = (z >= sr[0] && z <= sr[1]);
            }
            this._show();
        } else if (this.options.map) {
            this._hide();
            this.options.map = map;
        }
    },

    //
    // Get the map (if any) that the layer has been added to
    //
    getMap: function() {
        return this.options.map;
    },

    setOptions: function(o) {
        // TODO - Merge new options (o) with current options (this.options)
    },

    _show: function() {
        this._addIdleListener();
        if (this.options.scaleRange && this.options.scaleRange instanceof Array && this.options.scaleRange.length === 2) {
            this._addZoomChangeListener();
        }
        if (this.options.visibleAtScale) {
            if (this.options.autoUpdate && this.options.autoUpdateInterval) {
                var me = this;
                this._autoUpdateInterval = setInterval(function() {
                    me._getFeatures();
                }, this.options.autoUpdateInterval);
            }
            this.options.map.fire("moveend").fire("zoomend");
        }
    },

    _hide: function() {
        if (this._idleListener) {
            this.options.map.off("moveend", this._idleListener);
        }
        if (this._zoomChangeListener) {
            this.options.map.off("zoomend", this._zoomChangeListener);
        }
        if (this._autoUpdateInterval) {
            clearInterval(this._autoUpdateInterval);
        }
        this._clearFeatures();
        this._lastQueriedBounds = null;
        if (this._gotAll) {
            this._gotAll = false;
        }
    },

    //
    // Hide the vectors in the layer. This might get called if the layer is still on but out of scaleRange.
    //
    _hideVectors: function() {
        // TODO: There's probably an easier way to first check for "singlePopup" option then just remove the one
        //       instead of checking for "assocatedFeatures"
        for (var i = 0; i < this._vectors.length; i++) {
            if (this._vectors[i].vector) {
                this.options.map.removeLayer(this._vectors[i].vector);
                if (this._vectors[i].popup) {
                    this.options.map.removeLayer(this._vectors[i].popup);
                } else if (this.popup && this.popup.associatedFeature && this.popup.associatedFeature == this._vectors[i]) {
                    this.options.map.removeLayer(this.popup);
                    this.popup = null;
                }
            }
            if (this._vectors[i].vectors && this._vectors[i].vectors.length) {
                for (var i2 = 0; i2 < this._vectors[i].vectors.length; i2++) {
                    this.options.map.removeLayer(this._vectors[i].vectors[i2]);
                    if (this._vectors[i].vectors[i2].popup) {
                        this.options.map.removeLayer(this._vectors[i].vectors[i2].popup);
                    } else if (this.popup && this.popup.associatedFeature && this.popup.associatedFeature == this._vectors[i]) {
                        this.options.map.removeLayer(this.popup);
                        this.popup = null;
                    }
                }
            }
        }
    },

    //
    // Show the vectors in the layer. This might get called if the layer is on and came back into scaleRange.
    //
    _showVectors: function() {
        for (var i = 0; i < this._vectors.length; i++) {
            if (this._vectors[i].vector) {
                this.options.map.addLayer(this._vectors[i].vector);
            }
            if (this._vectors[i].vectors && this._vectors[i].vectors.length) {
                for (var i2 = 0; i2 < this._vectors[i].vectors.length; i2++) {
                    this.options.map.addLayer(this._vectors[i].vectors[i2]);
                }
            }
        }
    },

    //
    // Hide the vectors, then empty the vectory holding array
    //
    _clearFeatures: function() {
        // TODO - Check to see if we even need to hide these before we remove them from the DOM
        this._hideVectors();
        this._vectors = [];
    },

    //
    // Add an event hanlder to detect a zoom change on the map
    //
    _addZoomChangeListener: function() {
        //
        // "this" means something different inside the on method. Assign it to "me".
        //
        var me = this;

        me._zoomChangeListener = me._zoomChangeListenerTemplate();

        this.options.map.on("zoomend", me._zoomChangeListener, me);
    },

    _zoomChangeListenerTemplate: function() {
        //
        // Whenever the map's zoom changes, check the layer's visibility (this.options.visibleAtScale)
        //
        var me = this;
        return function() {
            me._checkLayerVisibility();
        }
    },

    //
    // This gets fired when the map is panned or zoomed
    //
    _idleListenerTemplate: function() {
        var me = this;
        return function() {
            if (me.options.visibleAtScale) {
                //
                // Do they use the showAll parameter to load all features once?
                //
                if (me.options.showAll) {
                    //
                    // Have we already loaded these features
                    //
                    if (!me._gotAll) {
                        //
                        // Grab the features and note that we've already loaded them (no need to _getFeatures again
                        //
                        me._getFeatures();
                        me._gotAll = true;
                    }
                } else {
                    me._getFeatures();
                }
            }
        }
    },

    //
    // Add an event hanlder to detect an idle (pan or zoom) on the map
    //
    _addIdleListener: function() {
        //
        // "this" means something different inside the on method. Assign it to "me".
        //
        var me = this;

        me._idleListener = me._idleListenerTemplate();

        //
        // Whenever the map idles (pan or zoom) get the features in the current map extent
        //
        this.options.map.on("moveend", me._idleListener, me);
    },

    //
    // Get the current map zoom and check to see if the layer should still be visible
    //
    _checkLayerVisibility: function() {
        //
        // Store current visibility so we can see if it changed
        //
        var visibilityBefore = this.options.visibleAtScale;

        //
        // Check current map scale and see if it's in this layer's range
        //
        var z = this.options.map.getZoom();
        var sr = this.options.scaleRange;
        this.options.visibleAtScale = (z >= sr[0] && z <= sr[1]);

        //
        // Check to see if the visibility has changed
        //
        if (visibilityBefore !== this.options.visibleAtScale) {
            //
            // It did, hide or show vectors
            //
            this[this.options.visibleAtScale ? "_showVectors" : "_hideVectors"]();
        }

        //
        // Check to see if we need to set or clear any intervals for auto-updating layers
        //
        if (visibilityBefore && !this.options.visibleAtScale && this._autoUpdateInterval) {
            clearInterval(this._autoUpdateInterval);
        } else if (!visibilityBefore && this.options.autoUpdate && this.options.autoUpdateInterval) {
            var me = this;
            this._autoUpdateInterval = setInterval(function() {
                me._getFeatures();
            }, this.options.autoUpdateInterval);
        }

    },

    //
    // Set the Popup content for the feature
    //
    _setPopupContent: function(feature) {
        //
        // Store previous Popup content so we can check to see if it changed. If it didn't no sense changing the content as this has an ugly flashing effect.
        //
        var previousContent = feature.popupContent;

        //
        // Esri calls them attributes. GeoJSON calls them properties.
        //
        var atts = feature.attributes || feature.properties;

        var popupContent;

        //
        // Check to see if it's a string-based popupTemplate or function
        //
        if (typeof this.options.popupTemplate == "string") {
            //
            // Store the string-based popupTemplate
            //
            popupContent = this.options.popupTemplate;

            //
            // Loop through the properties and replace mustache-wrapped property names with actual values
            //
            for (var prop in atts) {
                var re = new RegExp("{" + prop + "}", "g");
                popupContent = popupContent.replace(re, atts[prop]);
            }
        } else if (typeof this.options.popupTemplate == "function") {
            //
            // It's a function-based popupTempmlate, so just call this function and pass properties
            //
            popupContent = this.options.popupTemplate(atts);
        } else {
            //
            // Ummm, that's all we support. Seeya!
            //
            return;
        }

        //
        // Store the Popup content
        //
        feature.popupContent = popupContent;

        //
        // Check to see if popupContent has changed and if so setContent
        //
        if (feature.popup) {
            // The Popup is associated with a feature
            if (feature.popupContent !== previousContent) {
                feature.popup.setContent(feature.popupContent);
            }
        } else if (this.popup && this.popup.associatedFeature == feature) {
            // The Popup is associated with the layer (singlePopup: true)
            if (feature.popupContent !== previousContent) {
                this.popup.setContent(feature.popupContent);
            }
        }
    },

    //
    // Show the feature's (or layer's) Popup
    //
    _showPopup: function(feature, event) {
        //
        // Popups on Lines and Polygons are opened slightly different, make note of it
        //
        var isLineOrPolygon = event.latlng;

        // Set the popupAnchor if a marker was clicked
        if (!isLineOrPolygon) {
            L.Util.extend(this.options.popupOptions, {
                offset: event.target.options.icon.options.popupAnchor
            });
        }

        //
        // Create a variable to hold a reference to the object that owns the Popup so we can show it later
        //
        var ownsPopup;

        //
        // If the layer isn't set to show a single Popup
        //
        if (!this.options.singlePopup) {
            //
            // Create a Popup and store it in the feature
            //
            feature.popup = new L.Popup(this.options.popupOptions, feature.vector);
            ownsPopup = feature;
        } else {
            if (this.popup) {
                //
                // If the layer already has an Popup created, close and delete it
                //
                this.options.map.removeLayer(this.popup);
                this.popup = null;
            }

            //
            // Create a new Popup
            //
            this.popup = new L.Popup(this.options.popupOptions, feature.vector);

            //
            // Store the associated feature reference in the Popup so we can close and clear it later
            //
            this.popup.associatedFeature = feature;

            ownsPopup = this;
        }

        ownsPopup.popup.setLatLng(isLineOrPolygon ? event.latlng : event.target.getLatLng());
        ownsPopup.popup.setContent(feature.popupContent);
        this.options.map.addLayer(ownsPopup.popup);
    },

    //
    // Optional click event
    //
    _fireClickEvent: function (feature, event) {
        this.options.clickEvent(feature, event)
    },

    //
    // Optional mouseover event
    //
    _fireMouseoverEvent: function (feature, event) {
        this.options.mouseoverEvent(feature, event)
    },

    //
    // Optional mouseout event
    //
    _fireMouseoutEvent: function (feature, event) {
        this.options.mouseoutEvent(feature, event)
    },

    //
    // Get the appropriate Google Maps vector options for this feature
    //
    _getFeatureVectorOptions: function(feature) {
        //
        // Create an empty vectorOptions object to add to, or leave as is if no symbology can be found
        //
        var vectorOptions = {};

        //
        // Esri calls them attributes. GeoJSON calls them properties.
        //
        var atts = feature.attributes || feature.properties;

        //
        // Is there a symbology set for this layer?
        //
        if (this.options.symbology) {
            switch (this.options.symbology.type) {
                case "single":
                    //
                    // It's a single symbology for all features so just set the key/value pairs in vectorOptions
                    //
                    for (var key in this.options.symbology.vectorOptions) {
                        vectorOptions[key] = this.options.symbology.vectorOptions[key];
                        if (vectorOptions.title) {
                            for (var prop in atts) {
                                var re = new RegExp("{" + prop + "}", "g");
                                vectorOptions.title = vectorOptions.title.replace(re, atts[prop]);
                            }
                        }
                    }
                    break;
                case "unique":
                    //
                    // It's a unique symbology. Check if the feature's property value matches that in the symbology and style accordingly
                    //
                    var att = this.options.symbology.property;
                    for (var i = 0, len = this.options.symbology.values.length; i < len; i++) {
                        if (atts[att] == this.options.symbology.values[i].value) {
                            for (var key in this.options.symbology.values[i].vectorOptions) {
                                vectorOptions[key] = this.options.symbology.values[i].vectorOptions[key];
                                if (vectorOptions.title) {
                                    for (var prop in atts) {
                                        var re = new RegExp("{" + prop + "}", "g");
                                        vectorOptions.title = vectorOptions.title.replace(re, atts[prop]);
                                    }
                                }
                            }
                        }
                    }
                    break;
                case "range":
                    //
                    // It's a range symbology. Check if the feature's property value is in the range set in the symbology and style accordingly
                    //
                    var att = this.options.symbology.property;
                    for (var i = 0, len = this.options.symbology.ranges.length; i < len; i++) {
                        if (atts[att] >= this.options.symbology.ranges[i].range[0] && atts[att] <= this.options.symbology.ranges[i].range[1]) {
                            for (var key in this.options.symbology.ranges[i].vectorOptions) {
                                vectorOptions[key] = this.options.symbology.ranges[i].vectorOptions[key];
                                if (vectorOptions.title) {
                                    for (var prop in atts) {
                                        var re = new RegExp("{" + prop + "}", "g");
                                        vectorOptions.title = vectorOptions.title.replace(re, atts[prop]);
                                    }
                                }
                            }
                        }
                    }
                    break;
            }
        }
        return vectorOptions;
    },

    //
    // Check to see if any attributes have changed
    //
    _getPropertiesChanged: function(oldAtts, newAtts) {
        var changed = false;
        for (var key in oldAtts) {
            if (oldAtts[key] != newAtts[key]) {
                changed = true;
            }
        }
        return changed;
    },

    //
    // Check to see if a particular property changed
    //
    _getPropertyChanged: function(oldAtts, newAtts, property) {
        return !(oldAtts[property] == newAtts[property]);
    },

    //
    // Check to see if the geometry has changed
    //
    _getGeometryChanged: function(oldGeom, newGeom) {
        //
        // TODO: make this work for points, linestrings and polygons
        //
        var changed = false;
        if (oldGeom.coordinates && oldGeom.coordinates instanceof Array) {
            //
            // It's GeoJSON
            //

            //
            // For now only checking for point changes
            //
            if (!(oldGeom.coordinates[0] == newGeom.coordinates[0] && oldGeom.coordinates[1] == newGeom.coordinates[1])) {
                changed = true;
            }
        } else {
            //
            // It's EsriJSON
            //

            //
            // For now only checking for point changes
            //
            if (!(oldGeom.x == newGeom.x && oldGeom.y == newGeom.y)) {
                changed = true;
            }
        }
        return changed;
    },

    _makeJsonpRequest: function(url) {
        var head = document.getElementsByTagName("head")[0];
        var script = document.createElement("script");
        script.type = "text/javascript";
        script.src = url;
        head.appendChild(script);
    },

    _processFeatures: function(data) {
        //
        // Sometimes requests take a while to come back and
        // the user might have turned the layer off
        //
        if (!this.options.map) {
            return;
        }
        var bounds = this.options.map.getBounds();

        // Check to see if the _lastQueriedBounds is the same as the new bounds
        // If true, don't bother querying again.
        if (this._lastQueriedBounds && this._lastQueriedBounds.equals(bounds) && !this.options.autoUpdate) {
            return;
        }

        // Store the bounds in the _lastQueriedBounds member so we don't have
        // to query the layer again if someone simply turns a layer on/off
        this._lastQueriedBounds = bounds;

        // If necessary, convert data to make it look like a GeoJSON FeatureCollection
        // PRWSF returns GeoJSON, but not in a FeatureCollection. Make it one.
        if (this instanceof lvector.PRWSF) {
            data.features = data.rows;
            delete data.rows;
            for (var i = 0, len = data.features.length; i < len; i++) {
                data.features[i].type = "Feature"; // Not really necessary, but let's follow the GeoJSON spec for a Feature
                data.features[i].properties = {};
                for (var prop in data.features[i].row) {
                    if (prop == "geojson") {
                        data.features[i].geometry = data.features[i].row.geojson;
                    } else {
                        data.features[i].properties[prop] = data.features[i].row[prop];
                    }
                }
                delete data.features[i].row;
            }
        }
        // GISCloud returns GeoJSON, but not in a FeatureCollection. Make it one.
        if (this instanceof lvector.GISCloud) {
            data.features = data.data;
            delete data.data;
            for (var i = 0, len = data.features.length; i < len; i++) {
                data.features[i].type = "Feature"; // Not really necessary, but let's follow the GeoJSON spec for a Feature
                data.features[i].properties = data.features[i].data;
                data.features[i].properties.id = data.features[i].__id;
                delete data.features[i].data;
                data.features[i].geometry = data.features[i].__geometry;
                delete data.features[i].__geometry;
            }
        }

        // If "data.features" exists and there's more than one feature in the array
        if (data && data.features && data.features.length) {

            // Loop through the return features
            for (var i = 0; i < data.features.length; i++) {

                // if AGS layer type assigned "attributes" to "properties" to keep everything looking like GeoJSON Features
                if (this instanceof lvector.EsriJSONLayer) {
                    data.features[i].properties = data.features[i].attributes;
                    delete data.features[i].attributes;
                }

                // All objects are assumed to be false until proven true (remember COPS?)
                var onMap = false;

                // If we have a "uniqueField" for this layer
                if (this.options.uniqueField) {

                    // Loop through all of the features currently on the map
                    for (var i2 = 0; i2 < this._vectors.length; i2++) {

                        // Does the "uniqueField" property for this feature match the feature on the map
                        if (data.features[i].properties[this.options.uniqueField] == this._vectors[i2].properties[this.options.uniqueField]) {
                            // The feature is already on the map
                            onMap = true;

                            // We're only concerned about updating layers that are dynamic (options.dynamic = true).
                            if (this.options.dynamic) {

                                // The feature's geometry might have changed, let's check.
                                if (this._getGeometryChanged(this._vectors[i2].geometry, data.features[i].geometry)) {

                                    // Check to see if it's a point feature, these are the only ones we're updating for now
                                    if (!isNaN(data.features[i].geometry.coordinates[0]) && !isNaN(data.features[i].geometry.coordinates[1])) {
                                        this._vectors[i2].geometry = data.features[i].geometry;
                                        this._vectors[i2].vector.setLatLng(new L.LatLng(this._vectors[i2].geometry.coordinates[1], this._vectors[i2].geometry.coordinates[0]));
                                    }

                                }

                                var propertiesChanged = this._getPropertiesChanged(this._vectors[i2].properties, data.features[i].properties);

                                if (propertiesChanged) {
                                    var symbologyPropertyChanged = this._getPropertyChanged(this._vectors[i2].properties, data.features[i].properties, this.options.symbology.property);
                                    this._vectors[i2].properties = data.features[i].properties;
                                    if (this.options.popupTemplate) {
                                        this._setPopupContent(this._vectors[i2]);
                                    }
                                    if (this.options.symbology && this.options.symbology.type != "single" && symbologyPropertyChanged) {
                                        if (this._vectors[i2].vectors) {
                                            for (var i3 = 0, len3 = this._vectors[i2].vectors.length; i3 < len3; i3++) {
                                                if (this._vectors[i2].vectors[i3].setStyle) {
                                                    // It's a LineString or Polygon, so use setStyle
                                                    this._vectors[i2].vectors[i3].setStyle(this._getFeatureVectorOptions(this._vectors[i2]));
                                                } else if (this._vectors[i2].vectors[i3].setIcon) {
                                                    // It's a Point, so use setIcon
                                                    this._vectors[i2].vectors[i3].setIcon(this._getFeatureVectorOptions(this._vectors[i2]).icon);
                                                }
                                            }
                                        } else if (this._vectors[i2].vector) {
                                            if (this._vectors[i2].vector.setStyle) {
                                                // It's a LineString or Polygon, so use setStyle
                                                this._vectors[i2].vector.setStyle(this._getFeatureVectorOptions(this._vectors[i2]));
                                            } else if (this._vectors[i2].vector.setIcon) {
                                                // It's a Point, so use setIcon
                                                this._vectors[i2].vector.setIcon(this._getFeatureVectorOptions(this._vectors[i2]).icon);
                                            }
                                        }
                                    }
                                }

                            }

                        }

                    }

                }

                // If the feature isn't already or the map OR the "uniqueField" attribute doesn't exist
                if (!onMap || !this.options.uniqueField) {

                    if (this instanceof lvector.GeoJSONLayer) {
                        // Convert GeoJSON to Leaflet vector (Point, Polyline, Polygon)
                        var vector_or_vectors = this._geoJsonGeometryToLeaflet(data.features[i].geometry, this._getFeatureVectorOptions(data.features[i]));
                        data.features[i][vector_or_vectors instanceof Array ? "vectors" : "vector"] = vector_or_vectors;
                    } else if (this instanceof lvector.EsriJSONLayer) {
                        // Convert Esri JSON to Google Maps vector (Point, Polyline, Polygon)
                        var vector_or_vectors = this._esriJsonGeometryToLeaflet(data.features[i].geometry, this._getFeatureVectorOptions(data.features[i]));
                        data.features[i][vector_or_vectors instanceof Array ? "vectors" : "vector"] = vector_or_vectors;
                    }

                    // Show the vector or vectors on the map
                    if (data.features[i].vector) {
                        this.options.map.addLayer(data.features[i].vector);
                    } else if (data.features[i].vectors && data.features[i].vectors.length) {
                        for (var i3 = 0; i3 < data.features[i].vectors.length; i3++) {
                            this.options.map.addLayer(data.features[i].vectors[i3]);
                        }
                    }

                    // Store the vector in an array so we can remove it later
                    this._vectors.push(data.features[i]);

                    if (this.options.popupTemplate) {

                        var me = this;
                        var feature = data.features[i];

                        this._setPopupContent(feature);

                        (function(feature){
                            if (feature.vector) {
                                feature.vector.on("click", function(event) {
                                    me._showPopup(feature, event);
                                });
                            } else if (feature.vectors) {
                                for (var i3 = 0, len = feature.vectors.length; i3 < len; i3++) {
                                    feature.vectors[i3].on("click", function(event) {
                                        me._showPopup(feature, event);
                                    });
                                }
                            }
                        }(feature));

                    }

                    if (this.options.clickEvent) {

                        var me = this;
                        var feature = data.features[i];

                        (function(feature){
                            if (feature.vector) {
                                feature.vector.on("click", function(event) {
                                    me._fireClickEvent(feature, event);
                                });
                            } else if (feature.vectors) {
                                for (var i3 = 0, len = feature.vectors.length; i3 < len; i3++) {
                                    feature.vectors[i3].on("click", function(event) {
                                        me._fireClickEvent(feature, event);
                                    });
                                }
                            }
                        }(feature));

                    }

                    if (this.options.mouseoverEvent) {
                        var me = this;
                        var feature = data.features[i];

                        (function(feature){
                            if (feature.vector) {
                                feature.vector.on("mouseover", function(event) {
                                    me._fireMouseoverEvent(feature, event);
                                });
                            } else if (feature.vectors) {
                                for (var i3 = 0, len = feature.vectors.length; i3 < len; i3++) {
                                    feature.vectors[i3].on("mouseover", function(event) {
                                        me._fireMouseoverEvent(feature, event);
                                    });
                                }
                            }
                        }(feature));

                    }

                    if (this.options.mouseoutEvent) {

                        var me = this;
                        var feature = data.features[i];

                        (function(feature){
                            if (feature.vector) {
                                feature.vector.on("mouseout", function(event) {
                                    me._fireMouseoutEvent(feature, event);
                                });
                            } else if (feature.vectors) {
                                for (var i3 = 0, len = feature.vectors.length; i3 < len; i3++) {
                                    feature.vectors[i3].on("mouseout", function(event) {
                                        me._fireMouseoutEvent(feature, event);
                                    });
                                }
                            }
                        }(feature));

                    }

                }

            }

        }

    }
});




lvector.LayerOUT=L.Class.extend({options:{fields:"",scaleRange:null,map:null,uniqueField:null,visibleAtScale:!0,dynamic:!1,autoUpdate:!1,autoUpdateInterval:null,popupTemplate:null,popupOptions:{},singlePopup:!1,symbology:null,showAll:!1},initialize:function(a){L.Util.setOptions(this,a)},setMap:function(a){if(!a||!this.options.map)if(a){this.options.map=a;if(this.options.scaleRange&&this.options.scaleRange instanceof Array&&this.options.scaleRange.length===2){var a=this.options.map.getZoom(),b=this.options.scaleRange;
this.options.visibleAtScale=a>=b[0]&&a<=b[1]}this._show()}else if(this.options.map)this._hide(),this.options.map=a},getMap:function(){return this.options.map},setOptions:function(){},_show:function(){this._addIdleListener();this.options.scaleRange&&this.options.scaleRange instanceof Array&&this.options.scaleRange.length===2&&this._addZoomChangeListener();if(this.options.visibleAtScale){if(this.options.autoUpdate&&this.options.autoUpdateInterval){var a=this;this._autoUpdateInterval=setInterval(function(){a._getFeatures()},
this.options.autoUpdateInterval)}this.options.map.fire("moveend").fire("zoomend")}},_hide:function(){this._idleListener&&this.options.map.off("moveend",this._idleListener);this._zoomChangeListener&&this.options.map.off("zoomend",this._zoomChangeListener);this._autoUpdateInterval&&clearInterval(this._autoUpdateInterval);this._clearFeatures();this._lastQueriedBounds=null;if(this._gotAll)this._gotAll=!1},_hideVectors:function(){for(var a=0;a<this._vectors.length;a++){if(this._vectors[a].vector)if(this.options.map.removeLayer(this._vectors[a].vector),
this._vectors[a].popup)this.options.map.removeLayer(this._vectors[a].popup);else if(this.popup&&this.popup.associatedFeature&&this.popup.associatedFeature==this._vectors[a])this.options.map.removeLayer(this.popup),this.popup=null;if(this._vectors[a].vectors&&this._vectors[a].vectors.length)for(var b=0;b<this._vectors[a].vectors.length;b++)if(this.options.map.removeLayer(this._vectors[a].vectors[b]),this._vectors[a].vectors[b].popup)this.options.map.removeLayer(this._vectors[a].vectors[b].popup);else if(this.popup&&
this.popup.associatedFeature&&this.popup.associatedFeature==this._vectors[a])this.options.map.removeLayer(this.popup),this.popup=null}},_showVectors:function(){for(var a=0;a<this._vectors.length;a++)if(this._vectors[a].vector&&this.options.map.addLayer(this._vectors[a].vector),this._vectors[a].vectors&&this._vectors[a].vectors.length)for(var b=0;b<this._vectors[a].vectors.length;b++)this.options.map.addLayer(this._vectors[a].vectors[b])},_clearFeatures:function(){this._hideVectors();this._vectors=
[]},_addZoomChangeListener:function(){this._zoomChangeListener=this._zoomChangeListenerTemplate();this.options.map.on("zoomend",this._zoomChangeListener,this)},_zoomChangeListenerTemplate:function(){var a=this;return function(){a._checkLayerVisibility()}},_idleListenerTemplate:function(){var a=this;return function(){if(a.options.visibleAtScale)if(a.options.showAll){if(!a._gotAll)a._getFeatures(),a._gotAll=!0}else a._getFeatures()}},_addIdleListener:function(){this._idleListener=this._idleListenerTemplate();
this.options.map.on("moveend",this._idleListener,this)},_checkLayerVisibility:function(){var a=this.options.visibleAtScale,b=this.options.map.getZoom(),d=this.options.scaleRange;this.options.visibleAtScale=b>=d[0]&&b<=d[1];if(a!==this.options.visibleAtScale)this[this.options.visibleAtScale?"_showVectors":"_hideVectors"]();if(a&&!this.options.visibleAtScale&&this._autoUpdateInterval)clearInterval(this._autoUpdateInterval);else if(!a&&this.options.autoUpdate&&this.options.autoUpdateInterval){var e=
this;this._autoUpdateInterval=setInterval(function(){e._getFeatures()},this.options.autoUpdateInterval)}},_setPopupContent:function(a){var b=a.popupContent,d=a.attributes||a.properties,e;if(typeof this.options.popupTemplate=="string"){e=this.options.popupTemplate;for(var c in d)e=e.replace(RegExp("{"+c+"}","g"),d[c])}else if(typeof this.options.popupTemplate=="function")e=this.options.popupTemplate(d);else return;a.popupContent=e;a.popup?a.popupContent!==b&&a.popup.setContent(a.popupContent):this.popup&&
this.popup.associatedFeature==a&&a.popupContent!==b&&this.popup.setContent(a.popupContent)},_showPopup:function(a,b){var d=b.latlng;d||L.Util.extend(this.options.popupOptions,{offset:b.target.options.icon.options.popupAnchor});var e;if(this.options.singlePopup){if(this.popup)this.options.map.removeLayer(this.popup),this.popup=null;this.popup=new L.Popup(this.options.popupOptions,a.vector);this.popup.associatedFeature=a;e=this}else a.popup=new L.Popup(this.options.popupOptions,a.vector),e=a;e.popup.setLatLng(d?
b.latlng:b.target.getLatLng());e.popup.setContent(a.popupContent);this.options.map.addLayer(e.popup)},_fireClickEvent:function(a,b){this.options.clickEvent(a,b)},_getFeatureVectorOptions:function(a){var b={},a=a.attributes||a.properties;if(this.options.symbology)switch(this.options.symbology.type){case "single":for(var d in this.options.symbology.vectorOptions)if(b[d]=this.options.symbology.vectorOptions[d],b.title)for(var e in a){var c=RegExp("{"+e+"}","g");b.title=b.title.replace(c,a[e])}break;
case "unique":for(var f=this.options.symbology.property,g=0,h=this.options.symbology.values.length;g<h;g++)if(a[f]==this.options.symbology.values[g].value)for(d in this.options.symbology.values[g].vectorOptions)if(b[d]=this.options.symbology.values[g].vectorOptions[d],b.title)for(e in a)c=RegExp("{"+e+"}","g"),b.title=b.title.replace(c,a[e]);break;case "range":f=this.options.symbology.property;g=0;for(h=this.options.symbology.ranges.length;g<h;g++)if(a[f]>=this.options.symbology.ranges[g].range[0]&&
a[f]<=this.options.symbology.ranges[g].range[1])for(d in this.options.symbology.ranges[g].vectorOptions)if(b[d]=this.options.symbology.ranges[g].vectorOptions[d],b.title)for(e in a)c=RegExp("{"+e+"}","g"),b.title=b.title.replace(c,a[e])}return b},_getPropertiesChanged:function(a,b){var d=!1,e;for(e in a)a[e]!=b[e]&&(d=!0);return d},_getPropertyChanged:function(a,b,d){return a[d]!=b[d]},_getGeometryChanged:function(a,b){var d=!1;a.coordinates&&a.coordinates instanceof Array?a.coordinates[0]==b.coordinates[0]&&
a.coordinates[1]==b.coordinates[1]||(d=!0):a.x==b.x&&a.y==b.y||(d=!0);return d},_makeJsonpRequest:function(a){var b=document.getElementsByTagName("head")[0],d=document.createElement("script");d.type="text/javascript";d.src=a;b.appendChild(d)},_processFeatures:function(a){if(this.options.map){var b=this.options.map.getBounds();if(!this._lastQueriedBounds||!this._lastQueriedBounds.equals(b)||this.options.autoUpdate){this._lastQueriedBounds=b;featuresHaveIds=a.features&&a.features.length&&a.features[0].id?
!0:!1;!this.options.uniqueField&&!featuresHaveIds&&this._clearFeatures();if(this instanceof lvector.PRWSF){a.features=a.rows;delete a.rows;for(var b=0,d=a.features.length;b<d;b++){a.features[b].type="Feature";a.features[b].properties={};for(var e in a.features[b].row)e=="geojson"?a.features[b].geometry=a.features[b].row.geojson:a.features[b].properties[e]=a.features[b].row[e];delete a.features[b].row}}if(this instanceof lvector.GISCloud){a.features=a.data;delete a.data;b=0;for(d=a.features.length;b<
d;b++)a.features[b].type="Feature",a.features[b].properties=a.features[b].data,a.features[b].properties.id=a.features[b].__id,delete a.features[b].data,a.features[b].geometry=a.features[b].__geometry,delete a.features[b].__geometry}if(a&&a.features&&a.features.length)for(b=0;b<a.features.length;b++){if(this instanceof lvector.EsriJSONLayer)a.features[b].properties=a.features[b].attributes,delete a.features[b].attributes;e=!1;d=a.features[b].id?!0:!1;if(this.options.uniqueField||d)for(var c=0;c<this._vectors.length;c++){var f=
this._vectors[c].id?!0:!1;if(d&&f&&a.features[b].id==this._vectors[c].id||this.options.uniqueField&&a.features[b].properties[this.options.uniqueField]==this._vectors[c].properties[this.options.uniqueField])if(e=!0,this.options.dynamic){if(this._getGeometryChanged(this._vectors[c].geometry,a.features[b].geometry)&&!isNaN(a.features[b].geometry.coordinates[0])&&!isNaN(a.features[b].geometry.coordinates[1]))this._vectors[c].geometry=a.features[b].geometry,this._vectors[c].vector.setLatLng(new L.LatLng(this._vectors[c].geometry.coordinates[1],
this._vectors[c].geometry.coordinates[0]));if(this._getPropertiesChanged(this._vectors[c].properties,a.features[b].properties)&&(f=this._getPropertyChanged(this._vectors[c].properties,a.features[b].properties,this.options.symbology.property),this._vectors[c].properties=a.features[b].properties,this.options.popupTemplate&&this._setPopupContent(this._vectors[c]),this.options.symbology&&this.options.symbology.type!="single"&&f))if(this._vectors[c].vectors)for(var f=0,g=this._vectors[c].vectors.length;f<
g;f++)this._vectors[c].vectors[f].setStyle?this._vectors[c].vectors[f].setStyle(this._getFeatureVectorOptions(this._vectors[c])):this._vectors[c].vectors[f].setIcon&&this._vectors[c].vectors[f].setIcon(this._getFeatureVectorOptions(this._vectors[c]).icon);else this._vectors[c].vector&&(this._vectors[c].vector.setStyle?this._vectors[c].vector.setStyle(this._getFeatureVectorOptions(this._vectors[c])):this._vectors[c].vector.setIcon&&this._vectors[c].vector.setIcon(this._getFeatureVectorOptions(this._vectors[c]).icon))}}if(!e){this instanceof
lvector.GeoJSONLayer?(e=this._geoJsonGeometryToLeaflet(a.features[b].geometry,this._getFeatureVectorOptions(a.features[b])),a.features[b][e instanceof Array?"vectors":"vector"]=e):this instanceof lvector.EsriJSONLayer&&(e=this._esriJsonGeometryToLeaflet(a.features[b].geometry,this._getFeatureVectorOptions(a.features[b])),a.features[b][e instanceof Array?"vectors":"vector"]=e);if(a.features[b].vector)this.options.map.addLayer(a.features[b].vector);else if(a.features[b].vectors&&a.features[b].vectors.length)for(f=
0;f<a.features[b].vectors.length;f++)this.options.map.addLayer(a.features[b].vectors[f]);this._vectors.push(a.features[b]);if(this.options.popupTemplate){var h=this;e=a.features[b];this._setPopupContent(e);(function(a){if(a.vector)a.vector.on("click",function(b){console.log(h);h._showPopup(a,b)});else if(a.vectors)for(var b=0,c=a.vectors.length;b<c;b++)a.vectors[b].on("click",function(b){h._showPopup(a,b)})})(e)}this.options.clickEvent&&(h=this,e=a.features[b],function(a){if(a.vector)a.vector.on("click",function(b){h._fireClickEvent(a,
b)});else if(a.vectors)for(var b=0,c=a.vectors.length;b<c;b++)a.vectors[b].on("click",function(b){h._fireClickEvent(a,b)})}(e))}}}}}});lvector.GeoJSONLayer=lvector.Layer.extend({_geoJsonGeometryToLeaflet:function(a,b){var d,e;switch(a.type){case "Point":d=b.circleMarker?new L.CircleMarker(new L.LatLng(a.coordinates[1],a.coordinates[0]),b):new L.Marker(new L.LatLng(a.coordinates[1],a.coordinates[0]),b);break;case "MultiPoint":e=[];for(var c=0,f=a.coordinates.length;c<f;c++)e.push(new L.Marker(new L.LatLng(a.coordinates[c][1],a.coordinates[c][0]),b));break;case "LineString":for(var g=[],c=0,f=a.coordinates.length;c<f;c++)g.push(new L.LatLng(a.coordinates[c][1],
a.coordinates[c][0]));d=new L.Polyline(g,b);break;case "MultiLineString":e=[];c=0;for(f=a.coordinates.length;c<f;c++){for(var g=[],h=0,j=a.coordinates[c].length;h<j;h++)g.push(new L.LatLng(a.coordinates[c][h][1],a.coordinates[c][h][0]));e.push(new L.Polyline(g,b))}break;case "Polygon":for(var i=[],c=0,f=a.coordinates.length;c<f;c++){g=[];h=0;for(j=a.coordinates[c].length;h<j;h++)g.push(new L.LatLng(a.coordinates[c][h][1],a.coordinates[c][h][0]));i.push(g)}d=new L.Polygon(i,b);break;case "MultiPolygon":e=
[];c=0;for(f=a.coordinates.length;c<f;c++){i=[];h=0;for(j=a.coordinates[c].length;h<j;h++){for(var g=[],k=0,l=a.coordinates[c][h].length;k<l;k++)g.push(new L.LatLng(a.coordinates[c][h][k][1],a.coordinates[c][h][k][0]));i.push(g)}e.push(new L.Polygon(i,b))}break;case "GeometryCollection":e=[];c=0;for(f=a.geometries.length;c<f;c++)e.push(this._geoJsonGeometryToLeaflet(a.geometries[c],b))}return d||e}});lvector.EsriJSONLayer=lvector.Layer.extend({_esriJsonGeometryToLeaflet:function(a,b){var d,e;if(a.x&&a.y)d=new L.Marker(new L.LatLng(a.y,a.x),b);else if(a.points){e=[];for(var c=0,f=a.points.length;c<f;c++)e.push(new L.Marker(new L.LatLng(a.points[c].y,a.points[c].x),b))}else if(a.paths)if(a.paths.length>1){e=[];c=0;for(f=a.paths.length;c<f;c++){for(var g=[],h=0,j=a.paths[c].length;h<j;h++)g.push(new L.LatLng(a.paths[c][h][1],a.paths[c][h][0]));e.push(new L.Polyline(g,b))}}else{g=[];c=0;for(f=a.paths[0].length;c<
f;c++)g.push(new L.LatLng(a.paths[0][c][1],a.paths[0][c][0]));d=new L.Polyline(g,b)}else if(a.rings)if(a.rings.length>1){e=[];c=0;for(f=a.rings.length;c<f;c++){for(var i=[],g=[],h=0,j=a.rings[c].length;h<j;h++)g.push(new L.LatLng(a.rings[c][h][1],a.rings[c][h][0]));i.push(g);e.push(new L.Polygon(i,b))}}else{i=[];g=[];c=0;for(f=a.rings[0].length;c<f;c++)g.push(new L.LatLng(a.rings[0][c][1],a.rings[0][c][0]));i.push(g);d=new L.Polygon(i,b)}return d||e}});lvector.AGS=lvector.EsriJSONLayer.extend({initialize:function(a){for(var b=0,d=this._requiredParams.length;b<d;b++)if(!a[this._requiredParams[b]])throw Error('No "'+this._requiredParams[b]+'" parameter found.');this._globalPointer="AGS_"+Math.floor(Math.random()*1E5);window[this._globalPointer]=this;a.url.substr(a.url.length-1,1)!=="/"&&(a.url+="/");this._originalOptions=L.Util.extend({},a);if(a.esriOptions)if(typeof a.esriOptions=="object")L.Util.extend(a,this._convertEsriOptions(a.esriOptions));
else{this._getEsriOptions();return}lvector.Layer.prototype.initialize.call(this,a);if(this.options.where)this.options.where=encodeURIComponent(this.options.where);this._vectors=[];if(this.options.map){if(this.options.scaleRange&&this.options.scaleRange instanceof Array&&this.options.scaleRange.length===2)a=this.options.map.getZoom(),b=this.options.scaleRange,this.options.visibleAtScale=a>=b[0]&&a<=b[1];this._show()}},options:{where:"1=1",url:null,useEsriOptions:!1},_requiredParams:["url"],_convertEsriOptions:function(a){var b=
{};if(!(a.minScale==void 0||a.maxScale==void 0)){var d=this._scaleToLevel(a.minScale),e=this._scaleToLevel(a.maxScale);e==0&&(e=20);b.scaleRange=[d,e]}if(a.drawingInfo&&a.drawingInfo.renderer)b.symbology=this._renderOptionsToSymbology(a.drawingInfo.renderer);return b},_getEsriOptions:function(){this._makeJsonpRequest(this._originalOptions.url+"?f=json&callback="+this._globalPointer+"._processEsriOptions")},_processEsriOptions:function(a){var b=this._originalOptions;b.esriOptions=a;this.initialize(b)},
_scaleToLevel:function(a){var b=[5.91657527591555E8,2.95828763795777E8,1.47914381897889E8,7.3957190948944E7,3.6978595474472E7,1.8489297737236E7,9244648.868618,4622324.434309,2311162.217155,1155581.108577,577790.554289,288895.277144,144447.638572,72223.819286,36111.909643,18055.954822,9027.977411,4513.988705,2256.994353,1128.497176,564.248588,282.124294];if(a==0)return 0;for(var d=0,e=0;e<b.length-1;e++){var c=b[e+1];if(a<=b[e]&&a>c){d=e;break}}return d},_renderOptionsToSymbology:function(a){symbology=
{};switch(a.type){case "simple":symbology.type="single";symbology.vectorOptions=this._parseSymbology(a.symbol);break;case "uniqueValue":symbology.type="unique";symbology.property=a.field1;for(var b=[],d=0;d<a.uniqueValueInfos.length;d++){var e=a.uniqueValueInfos[d],c={};c.value=e.value;c.vectorOptions=this._parseSymbology(e.symbol);c.label=e.label;b.push(c)}symbology.values=b;break;case "classBreaks":symbology.type="range";symbology.property=rend.field;b=[];e=a.minValue;for(d=0;d<a.classBreakInfos.length;d++){var c=
a.classBreakInfos[d],f={};f.range=[e,c.classMaxValue];e=c.classMaxValue;f.vectorOptions=this._parseSymbology(c.symbol);f.label=c.label;b.push(f)}symbology.ranges=b}return symbology},_parseSymbology:function(a){var b={};switch(a.type){case "esriSMS":case "esriPMS":a=L.icon({iconUrl:"data:"+a.contentType+";base64,"+a.imageData,shadowUrl:null,iconSize:new L.Point(a.width,a.height),iconAnchor:new L.Point(a.width/2+a.xoffset,a.height/2+a.yoffset),popupAnchor:new L.Point(0,-(a.height/2))});b.icon=a;break;
case "esriSLS":b.weight=a.width;b.color=this._parseColor(a.color);b.opacity=this._parseAlpha(a.color[3]);break;case "esriSFS":a.outline?(b.weight=a.outline.width,b.color=this._parseColor(a.outline.color),b.opacity=this._parseAlpha(a.outline.color[3])):(b.weight=0,b.color="#000000",b.opacity=0),a.style!="esriSFSNull"?(b.fillColor=this._parseColor(a.color),b.fillOpacity=this._parseAlpha(a.color[3])):(b.fillColor="#000000",b.fillOpacity=0)}return b},_parseColor:function(a){red=this._normalize(a[0]);
green=this._normalize(a[1]);blue=this._normalize(a[2]);return"#"+this._pad(red.toString(16))+this._pad(green.toString(16))+this._pad(blue.toString(16))},_normalize:function(a){return a<1&&a>0?Math.floor(a*255):a},_pad:function(a){return a.length>1?a.toUpperCase():"0"+a.toUpperCase()},_parseAlpha:function(a){return a/255},_getFeatures:function(){var a=this.options.url+"query?returnGeometry=true&outSR=4326&f=json&outFields="+this.options.fields+"&where="+this.options.where+"&callback="+this._globalPointer+
"._processFeatures";this.options.showAll||(a+="&inSR=4326&spatialRel=esriSpatialRelIntersects&geometryType=esriGeometryEnvelope&geometry="+this.options.map.getBounds().toBBoxString());this._makeJsonpRequest(a)}});lvector.A2E=lvector.AGS.extend({initialize:function(a){for(var b=0,d=this._requiredParams.length;b<d;b++)if(!a[this._requiredParams[b]])throw Error('No "'+this._requiredParams[b]+'" parameter found.');this._globalPointer="A2E_"+Math.floor(Math.random()*1E5);window[this._globalPointer]=this;a.url.substr(a.url.length-1,1)!=="/"&&(a.url+="/");this._originalOptions=L.Util.extend({},a);if(a.esriOptions)if(typeof a.esriOptions=="object")L.Util.extend(a,this._convertEsriOptions(a.esriOptions));else{this._getEsriOptions();
return}lvector.Layer.prototype.initialize.call(this,a);if(this.options.where)this.options.where=encodeURIComponent(this.options.where);this._vectors=[];if(this.options.map){if(this.options.scaleRange&&this.options.scaleRange instanceof Array&&this.options.scaleRange.length===2)a=this.options.map.getZoom(),b=this.options.scaleRange,this.options.visibleAtScale=a>=b[0]&&a<=b[1];this._show()}if(this.options.autoUpdate&&this.options.esriOptions.editFeedInfo){this._makeJsonpRequest("http://cdn.pubnub.com/pubnub-3.1.min.js");
var e=this;this._pubNubScriptLoaderInterval=setInterval(function(){window.PUBNUB&&e._pubNubScriptLoaded()},200)}},_pubNubScriptLoaded:function(){clearInterval(this._pubNubScriptLoaderInterval);this.pubNub=PUBNUB.init({subscribe_key:this.options.esriOptions.editFeedInfo.pubnubSubscribeKey,ssl:!1,origin:"pubsub.pubnub.com"});var a=this;this.pubNub.subscribe({channel:this.options.esriOptions.editFeedInfo.pubnubChannel,callback:function(){a._getFeatures()},error:function(){}})}});lvector.GeoIQ=lvector.GeoJSONLayer.extend({initialize:function(a){for(var b=0,d=this._requiredParams.length;b<d;b++)if(!a[this._requiredParams[b]])throw Error('No "'+this._requiredParams[b]+'" parameter found.');lvector.Layer.prototype.initialize.call(this,a);this._globalPointer="GeoIQ_"+Math.floor(Math.random()*1E5);window[this._globalPointer]=this;this._vectors=[];if(this.options.map){if(this.options.scaleRange&&this.options.scaleRange instanceof Array&&this.options.scaleRange.length===2)a=this.options.map.getZoom(),
b=this.options.scaleRange,this.options.visibleAtScale=a>=b[0]&&a<=b[1];this._show()}},options:{dataset:null},_requiredParams:["dataset"],_getFeatures:function(){var a="http://geocommons.com/datasets/"+this.options.dataset+"/features.json?geojson=1&callback="+this._globalPointer+"._processFeatures&limit=999";this.options.showAll||(a+="&bbox="+this.options.map.getBounds().toBBoxString()+"&intersect=full");this._makeJsonpRequest(a)}});lvector.CartoDB=lvector.GeoJSONLayer.extend({initialize:function(a){for(var b=0,d=this._requiredParams.length;b<d;b++)if(!a[this._requiredParams[b]])throw Error('No "'+this._requiredParams[b]+'" parameter found.');lvector.Layer.prototype.initialize.call(this,a);this._globalPointer="CartoDB_"+Math.floor(Math.random()*1E5);window[this._globalPointer]=this;this._vectors=[];if(this.options.map){if(this.options.scaleRange&&this.options.scaleRange instanceof Array&&this.options.scaleRange.length===2)a=
this.options.map.getZoom(),b=this.options.scaleRange,this.options.visibleAtScale=a>=b[0]&&a<=b[1];this._show()}},options:{version:1,user:null,table:null,fields:"*",where:null,limit:null,uniqueField:"cartodb_id"},_requiredParams:["user","table"],_getFeatures:function(){var a=this.options.where||"";if(!this.options.showAll)for(var b=this.options.map.getBounds(),d=b.getSouthWest(),b=b.getNorthEast(),e=this.options.table.split(",").length,c=0;c<e;c++)a+=(a.length?" AND ":"")+(e>1?this.options.table.split(",")[c].split(".")[0]+
".the_geom":"the_geom")+" && st_setsrid(st_makebox2d(st_point("+d.lng+","+d.lat+"),st_point("+b.lng+","+b.lat+")),4326)";this.options.limit&&(a+=(a.length?" ":"")+"limit "+this.options.limit);a=a.length?" "+a:"";this._makeJsonpRequest("http://"+this.options.user+".cartodb.com/api/v"+this.options.version+"/sql?q="+encodeURIComponent("SELECT "+this.options.fields+" FROM "+this.options.table+(a.length?" WHERE "+a:""))+"&format=geojson&callback="+this._globalPointer+"._processFeatures")}});lvector.PRWSF=lvector.GeoJSONLayer.extend({initialize:function(a){for(var b=0,d=this._requiredParams.length;b<d;b++)if(!a[this._requiredParams[b]])throw Error('No "'+this._requiredParams[b]+'" parameter found.');a.url.substr(a.url.length-1,1)!=="/"&&(a.url+="/");lvector.Layer.prototype.initialize.call(this,a);this._globalPointer="PRWSF_"+Math.floor(Math.random()*1E5);window[this._globalPointer]=this;this._vectors=[];if(this.options.map){if(this.options.scaleRange&&this.options.scaleRange instanceof
Array&&this.options.scaleRange.length===2)a=this.options.map.getZoom(),b=this.options.scaleRange,this.options.visibleAtScale=a>=b[0]&&a<=b[1];this._show()}},options:{geotable:null,srid:null,geomFieldName:"the_geom",geomPrecision:"",fields:"",where:null,limit:null,uniqueField:null},_requiredParams:["url","geotable"],_getFeatures:function(){var a=this.options.where||"";if(!this.options.showAll){var b=this.options.map.getBounds(),d=b.getSouthWest(),b=b.getNorthEast();a+=a.length?" AND ":"";a+=this.options.srid?
this.options.geomFieldName+" && transform(st_setsrid(st_makebox2d(st_point("+d.lng+","+d.lat+"),st_point("+b.lng+","+b.lat+")),4326),"+this.options.srid+")":"transform("+this.options.geomFieldName+",4326) && st_setsrid(st_makebox2d(st_point("+d.lng+","+d.lat+"),st_point("+b.lng+","+b.lat+")),4326)"}this.options.limit&&(a+=(a.length?" ":"")+"limit "+this.options.limit);d=(this.options.fields.length?this.options.fields+",":"")+"st_asgeojson(transform("+this.options.geomFieldName+",4326)"+(this.options.geomPrecision?
","+this.options.geomPrecision:"")+") as geojson";this._makeJsonpRequest(this.options.url+"v1/ws_geo_attributequery.php?parameters="+encodeURIComponent(a)+"&geotable="+this.options.geotable+"&fields="+encodeURIComponent(d)+"&format=json&callback="+this._globalPointer+"._processFeatures")}});lvector.GISCloud=lvector.GeoJSONLayer.extend({initialize:function(a){for(var b=0,d=this._requiredParams.length;b<d;b++)if(!a[this._requiredParams[b]])throw Error('No "'+this._requiredParams[b]+'" parameter found.');lvector.Layer.prototype.initialize.call(this,a);this._globalPointer="GISCloud_"+Math.floor(Math.random()*1E5);window[this._globalPointer]=this;this._vectors=[];if(this.options.map){if(this.options.scaleRange&&this.options.scaleRange instanceof Array&&this.options.scaleRange.length===2)a=
this.options.map.getZoom(),b=this.options.scaleRange,this.options.visibleAtScale=a>=b[0]&&a<=b[1];this._show()}},options:{mapID:null,layerID:null,uniqueField:"id"},_requiredParams:["mapID","layerID"],_getFeatures:function(){var a="http://api.giscloud.com/1/maps/"+this.options.mapID+"/layers/"+this.options.layerID+"/features.json?geometry=geojson&epsg=4326&callback="+this._globalPointer+"._processFeatures";this.options.showAll||(a+="&bounds="+this.options.map.getBounds().toBBoxString());this.options.where&&
(a+="&where="+encodeURIComponent(this.options.where));this._makeJsonpRequest(a)}});lvector.GitSpatial=lvector.GeoJSONLayer.extend({initialize:function(a){for(var b=0,d=this._requiredParams.length;b<d;b++)if(!a[this._requiredParams[b]])throw Error('No "'+this._requiredParams[b]+'" parameter found.');lvector.Layer.prototype.initialize.call(this,a);this._globalPointer="GitSpatial_"+Math.floor(Math.random()*1E5);window[this._globalPointer]=this;this._vectors=[];if(this.options.map){if(this.options.scaleRange&&this.options.scaleRange instanceof Array&&this.options.scaleRange.length===
2)a=this.options.map.getZoom(),b=this.options.scaleRange,this.options.visibleAtScale=a>=b[0]&&a<=b[1];this._show()}},options:{},_requiredParams:["user","repo","featureSet"],_getFeatures:function(){var a="http://gitspatial.com/api/v1/"+this.options.user+"/"+this.options.repo+"/"+this.options.featureSet+"?callback="+this._globalPointer+"._processFeatures";this.options.showAll||(a+="&bbox="+this.options.map.getBounds().toBBoxString());this._makeJsonpRequest(a)}});

