$(function(){
    var $mapContainer = $('#subsite-map');
    var mapsDefaults = MapasCulturais.mapsDefaults;
    var obj = MapasCulturais.entity.object;
    
    var config = {
        zoomControl: false,
        zoomMax: obj.zoom_max || mapsDefaults.zoomMax,
        zoomMin: obj.zoom_min || mapsDefaults.zoomMin,
        zoom: obj.zoom_default || mapsDefaults.zoomDefault,
        center: new L.LatLng(obj.latitude || mapsDefaults.latitude, obj.longitude || mapsDefaults.longitude)
    };
    
    var openStreetMap = L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
        attribution: 'Dados e Imagens &copy; <a href="http://www.openstreetmap.org/copyright">Contrib. OpenStreetMap</a>, ',
        maxZoom: config.zoomMax,
        minZoom: config.zoomMin
    });
    
    var map = new L.Map('subsite-map', config).addLayer(openStreetMap);
    
    (new L.Control.Zoom({position: 'bottomright'})).addTo(map);
    
    var setState = function(event){
        var center = map.getCenter();
        var zoom = event.target._zoom;
        
        $('#latitude').editable('setValue', center.lat);
        $('#longitude').editable('setValue', center.lng);
        $('#zoom_default').editable('setValue', zoom);
    };
    
//    $('#latitude, #longitude, #zoom_default').editable('disabled')
    
    map.on('zoomend', setState);
    map.on('moveend', setState);
    
    $('#tab-mapa').on('click', function(){
        setTimeout(function(){
            map.invalidateSize();
        },50)
    })
    
    
    $('.js-editable.js-color').on('save', function(e, editable){
        
        var $this = $(this);
        $this.css('background-color', editable.newValue);
        
    }).on('shown', function(e,editable){
//        $(editable.container.$form.find('div.editable-input:first')[0]).colorpicker();
    }).each(function(){
        var color = $(this).text();
        if(color){
            $(this).css('background-color', color);
        }
    });
    
    var interval = setInterval(function(){
       
       $('.js-editable.js-color').removeClass('editable-unsaved editable-bg-transition');                

    },10);
});
