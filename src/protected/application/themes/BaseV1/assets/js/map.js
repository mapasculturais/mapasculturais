(function($){
    MapasCulturais.Map = {};
    L.Icon.Default.imagePath = MapasCulturais.assetURL + 'img/';

    MapasCulturais.Map.initialize = function(initializerOptions) {

        var config = MapasCulturais.mapsDefaults,
            mapSelector = initializerOptions.mapSelector,
            defaultIconOptions = {
                shadowUrl: MapasCulturais.assets.pinShadow,
                iconSize: [35, 43], // size of the icon
                shadowSize: [40, 16], // size of the shadow
                iconAnchor: [20, 30], // point of the icon which will correspond to marker's location
                shadowAnchor: [6, 3], // the same for the shadow
                popupAnchor: [-3, -76] // point from which the popup should open relative to the iconAnchor
            },
            iconTypeMapping = {
                agent: MapasCulturais.assets.pinAgent,
                coletivo: MapasCulturais.assets.pinAgent,
                space: MapasCulturais.assets.pinSpace,
                event: MapasCulturais.assets.pinEvent,
                location: MapasCulturais.assets.pinMarker
            };

        MapasCulturais.Map.iconOptions = {};

        for(var iconType in iconTypeMapping){
            var opts = (JSON.parse(JSON.stringify(defaultIconOptions)));
            opts.iconUrl = iconTypeMapping[iconType];
            MapasCulturais.Map.iconOptions[iconType] = {
                icon: L.icon(opts)
            };
        }

        if(initializerOptions.exportToGlobalScope){
            window.leaflet = {};
            window.leaflet.iconOptions = MapasCulturais.Map.iconOptions;
        }

        $(mapSelector).each(function() {
            if($(this).data('init')){
                return;
            }
            $(this).data('init',true);
            var id = $(this).attr('id');
            var isEditable = initializerOptions.isMapEditable===false ? false : MapasCulturais.isEditable;
            if (!isEditable)
                $('#' + id + ':active').css({'cursor': 'default'});
            var $dataTarget = $('#map-target');
            var isPositionDefined = $(this).data('lat') ? true : false;
            var defaultZoom = isPositionDefined ? config.zoomPrecise : config.zoomDefault;
            var defaultLocateMaxZoom = config.zoomPrecise;
            var defaultAproximatePrecisionZoom = config.zoomApproximate;
            var defaultMaxCircleRadius = 1000;
            var mapCenter = isPositionDefined ? new L.LatLng($(this).data('lat'), $(this).data('lng')) : new L.LatLng(config.latitude, config.longitude);
            var options = $(this).data('options') ? $(this).data('options') : {dragging: true, zoomControl: true, doubleClickZoom: true, scrollWheelZoom: true};

            var locateMeControl = initializerOptions.locateMeControl ? true : false;

            if(initializerOptions.mapCenter){
                options.center = new L.LatLng( initializerOptions.mapCenter.lat, initializerOptions.mapCenter.lng);
            }else{
                options.center = mapCenter;
            }

            options.zoom = defaultZoom;
            options.zoomControl = false;
            options.minZoom = config.zoomMin;
            var openStreetMap = L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
                attribution: 'Dados e Imagens &copy; <a href="http://www.openstreetmap.org/copyright">Contrib. OpenStreetMap</a>, ',
                maxZoom: config.zoomMax
            });

            var map = new L.Map(id, options).addLayer(openStreetMap);
            $(this).data('leaflet-map', map);
            var timeout;
            $(window).scroll(function() {
                map.scrollWheelZoom.disable();
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    if(!MapasCulturais.reenableScrollWheelZoom)
                        map.scrollWheelZoom.enable();
                }, 400);
            });

            var marker = new L.marker(map.getCenter(), {draggable: isEditable && MapasCulturais.request.controller !== 'event' });
            var markerIcon = {};
            if (MapasCulturais.request.controller == 'agent' || MapasCulturais.request.controller == 'space')
                markerIcon = MapasCulturais.Map.iconOptions[MapasCulturais.request.controller].icon;
            else if(MapasCulturais.request.controller == 'event')
                markerIcon = MapasCulturais.Map.iconOptions['space'].icon;

            if(Object.keys(markerIcon).length){
                marker.setIcon(markerIcon);
                map.addLayer(marker);
            }

            if (isPositionDefined) {
                marker.setLatLng(mapCenter).addTo(map);
            }

            /* Events */
            marker.on('move', function(e) {
                if (isEditable) {
                    $dataTarget.editable('setValue', [e.latlng.lng, e.latlng.lat]);
                }
            });

            map.on('locationfound', function(e) {
                var radius = e.accuracy / 2;
                if (true || radius > defaultMaxCircleRadius)
                    radius = defaultMaxCircleRadius;

                marker.setLatLng(e.latlng);
            });

            map.on('locationerror', function(e) {
                /** @TODO feedback pro usuario **/
                // console.log(e.message);
            });

            map.on('click', function(e) {
                if (isEditable && MapasCulturais.request.controller !== 'event')
                    marker.setLatLng(e.latlng);
            });

            $('#locate-me').click(function() {
                map.locate({setView: true, maxZoom: defaultLocateMaxZoom});
            });

            L.Polygon.prototype.getCenter = function() {
                var pts = this._latlngs;
                var off = pts[0];
                var twicearea = 0;
                var x = 0;
                var y = 0;
                var nPts = pts.length;
                var p1, p2;
                var f;
                for (var i = 0, j = nPts - 1; i < nPts; j = i++) {
                    p1 = pts[i];
                    p2 = pts[j];
                    f = (p1.lat - off.lat) * (p2.lng - off.lng) - (p2.lat - off.lat) * (p1.lng - off.lng);
                    twicearea += f;
                    x += (p1.lat + p2.lat - 2 * off.lat) * f;
                    y += (p1.lng + p2.lng - 2 * off.lng) * f;
                }
                f = twicearea * 3;
                return new L.LatLng(
                    x / f + off.lat,
                    y / f + off.lng
                    );
            };

            // callback to handle geolocation result
            function geocode_callback(results) {
                if (results) {
                    var foundLocation = new L.latLng(results.lat, results.lon);
                    map.setView(foundLocation, config.zoomPrecise);
                    marker.setLatLng(foundLocation);
                }
            }

            $('.js-editable').on('save', function(e, params) {
                if ($(this).data('edit') == 'endereco') {
                    $(this).trigger('changeAddress', params.newValue);
                }
            });

            $('.js-editable[data-edit="endereco"]').on('changeAddress', function(event, strAddress){
                var streetName = $('#En_Nome_Logradouro').editable('getValue', true);
                var number = $('#En_Num').editable('getValue', true);
                var neighborhood = $('#En_Bairro').editable('getValue', true);
                var city = $('#En_Municipio').editable('getValue', true);
                var state = $('#En_Estado').editable('getValue', true);
                var cep = $('#En_CEP').editable('getValue', true);
                MapasCulturais.geocoder.geocode({
                    streetName: streetName,
                    number: number,
                    neighborhood: neighborhood,
                    city: city,
                    state: state,
                    postalCode: cep
                }, geocode_callback);
            });

            //Mais controles
            if (isEditable) {
                var locateMeControl = L.Control.extend({
                    options: {
                        position: 'topright'
                    },
                    onAdd: function(map) {
                        var controlDiv = L.DomUtil.create('div', 'leaflet-control-command');
                        L.DomEvent
                            .addListener(controlDiv, 'click', L.DomEvent.stopPropagation)
                            .addListener(controlDiv, 'click', L.DomEvent.preventDefault)
                            .addListener(controlDiv, 'click', function() {
                                map.locate({setView: true, maxZoom: defaultLocateMaxZoom});
                            });

                        var controlUI = L.DomUtil.create('div', 'leaflet-control-command-interior', controlDiv);
                        controlUI.title = 'Localizar sua posição através do navegador';
                        controlUI.innerHTML = '<span class="icon icon-show-map"></span> Localize-me';
                        return controlDiv;
                    }
                });

                if (initializerOptions.locateMeControl)
                    map.addControl(new locateMeControl({}));
            }


            var camadasBase = {};
            camadasBase['OpenStreetMap'] = openStreetMap;

            if(config.includeGoogleLayers && typeof google !== 'undefined') {
                var googleSatelite = new L.Google();

                var googleMapa = new L.Google();
                googleMapa._type = 'ROADMAP';
                googleMapa.options.maxZoom = 23;

                var googleHibrido = new L.Google();
                googleHibrido._type = 'HYBRID';

                var googleRelevo = new L.Google();
                googleRelevo._type = 'TERRAIN';
                googleRelevo.options.maxZoom = 15;

                /*Criação do Mapa*/
                var camadasGoogle = {
                    "Google Satélite": googleHibrido,
                    "Google Mapa": googleMapa,
                    "Google Satélite Puro": googleSatelite,
                    "Google Relevo": googleRelevo
                };

                for ( var key in camadasGoogle) {
                    camadasBase[key] = camadasGoogle[key];
                };
            }

            var geoDivisions = new lvector.PRWSF({
                url: MapasCulturais.vectorLayersURL,
                geotable: '"geo_division"',
                fields: "id,name",
                //where: 'true',
                geomFieldName: MapasCulturais.mapGeometryFieldQuery,
                uniqueField: "id",
                srid: 4326,
                showAll: true,
                mouseoverEvent: function(feature, event) {
                    var labelText = feature.properties.name;
                    var vector = event.target;
                    vector.bindLabel('<b style="text-transform: capitalize;">' + labelText.toLowerCase() + '</b>');
                    map.showLabel(vector.label.setLatLng(vector.getCenter()));
                },
                singlePopup: true,
                symbology: {
                    type: "single",
                    vectorOptions: {
                        // @TODO: rename this class
                        className : 'vetorial-sp'
                    }
                }
            });

            /*Controles*/
            (new L.Control.FullScreen({position: 'bottomright', title: 'Tela Cheia'})).addTo(map);
            (new L.Control.Zoom({position: 'bottomright'})).addTo(map);
            var geoDivisionsObj = {};
            for(var div_id in MapasCulturais.geoDivisionsHierarchy){
                var div_label = MapasCulturais.geoDivisionsHierarchy[div_id];
                geoDivisionsObj['<span class="js-geo-division" data-type="' + div_id + '">' + div_label + '</span>'] = {onAdd:function(map){return;}, onRemove:function(map){return;}};
            };

            var layersControl = new L.Control.Layers(camadasBase, geoDivisionsObj);

            layersControl.addTo(map);
            function setGeoChecboxes(type) {
                $('.js-geo-division').each(function () {
                    if ($(this).data('type') != type)
                        $(this).parents('label').find('input:checkbox').prop('checked', false);
                });
            }

            $('.js-geo-division').each(function(){
                var $checkbox = $(this).parents('label').find('input:checkbox');
                var type = $(this).data('type');

                $checkbox.on('click', function(event){
                    geoDivisions.setMap(null);

                    if ($(this).prop('checked') === true) {
                        setGeoChecboxes(type);

                        geoDivisions.options.where = "type='" + type.toLowerCase() + "'";
                        geoDivisions.options.geoDivisionType = type;
                        geoDivisions.setMap(map);
                    } else {
                        geoDivisions.setMap(null);
                    }
                });
            });

            geoDivisions._makeJsonpRequest = function(url){
                $('#resultados span[ng-if="!spinnerCount"]').hide();
                $('#resultados span[ng-show="spinnerCount > 0"]').removeClass('ng-hide');
                $.ajax({
                    url: url,
                    dataType: 'jsonp',
                    //jsonpCallback: myCallback,
                    cache: true,
                    success: function(data) {
                        geoDivisions._processFeatures(data);
                        $('#resultados span[ng-if="!spinnerCount"]').show();
                        $('#resultados span[ng-show="spinnerCount > 0"]').addClass('ng-hide');

                        setGeoChecboxes(geoDivisions.options.geoDivisionType);
                    }
                });

            };

            if (initializerOptions.exportToGlobalScope) {
                window.leaflet.map = map;
                window.leaflet.marker = marker;
            }
        });

        $('.js-leaflet-control').each(function(){
            var $control = $(this);
            $control.addClass('leaflet-control');
            $('.leaflet-control-container').each(function(){
                $(this).find($control.data('leaflet-target')).append($control);
            });
        });

        $('.js-leaflet-control').on('click dblclick mousedown startdrag', function(e){
            e.stopPropagation();
        });
    };

    $(function(){

        if($('body').hasClass('controller-agent')){
            if(MapasCulturais.isEditable){
                var publicLocation = $('[data-edit="publicLocation"]').editable('getValue').publicLocation;
                var empty = publicLocation === undefined;

                if(!empty){
                    MapasCulturais.Map.initialize({mapSelector: '.js-map', locateMeControl: false, exportToGlobalScope: true, mapCenter:MapasCulturais.mapCenter});
                }else{
                    $('.js-map').parent().hide();
                }

                $('[data-edit="publicLocation"]').on('hidden', function(){
                    var publicLocation = $(this).data('editable').value;
                    var empty = publicLocation === 'null';
                    if(!empty){
                        $('.js-map-container').show();
                        MapasCulturais.Map.initialize({mapSelector: '.js-map', locateMeControl: false, exportToGlobalScope: true, mapCenter:MapasCulturais.mapCenter});
                    }else{
                        $('#map-target').editable('setValue', [0, 0]);
                        $('.js-map-container').hide();
                    }

                });
            }else{
                MapasCulturais.Map.initialize({mapSelector: '.js-map', locateMeControl: false, exportToGlobalScope: true, mapCenter:MapasCulturais.mapCenter});
            }
        }

        if($('body').hasClass('controller-space') || $('body').hasClass('controller-event')){
            MapasCulturais.Map.initialize({mapSelector: '.js-map', locateMeControl: false, exportToGlobalScope: true, mapCenter:MapasCulturais.mapCenter});
        }

        var timeout;
        $(window).scroll(function() {
            try{
                leaflet.map.scrollWheelZoom.disable();
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    leaflet.map.scrollWheelZoom.enable();
                }, 400);
            }catch(e){ }
        });

    });

    // Fix Leaflet FUllScreen control that not allows keyboard inputs
    (function(){
        window.fullScreenApi.requestFullScreen = function(el) {

            //Change the element to use <html> tag in Search
            if(MapasCulturais.request.controller === 'site'){
                el = document.querySelector('html');
            }

            //Add permission to allow keyboard input
            return (this.prefix === '') ?
                el.requestFullscreen(Element.ALLOW_KEYBOARD_INPUT)
            :
                el[this.prefix + 'RequestFullScreen'](Element.ALLOW_KEYBOARD_INPUT);

            //Scroll the window to the bottom
            //didn't work window.scrollTo(0,document.body.scrollHeight);
        };
    })();
})(jQuery);
