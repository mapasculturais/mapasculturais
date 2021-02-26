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
    
    var openStreetMap = L.tileLayer(MapasCulturais.mapsTileServer, {
        attribution: 'Dados e Imagens &copy; <a href="http://www.openstreetmap.org/copyright" rel="noopener noreferrer">Contrib. OpenStreetMap</a>, ',
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
        
    }).each(function(){
        var color = $(this).text();
        if(color){
            $(this).css('background-color', color);
        }
    });
    
    var interval = setInterval(function(){
       
       $('.js-editable.js-color').removeClass('editable-unsaved editable-bg-transition');

    },10);
    
    
    $('.js-editable--subsite-text').on('shown', function(e, editable){
        var $this = $(this);
        var prop = $this.data('edit');
        var examples = $this.data('examples');
        var val = $this.editable('getValue')[prop];
        var parents = editable.input.$input.parents();
        var labels = MapasCulturais.gettext.entityApp;
        
        setTimeout(function(){
            var $container = $(parents[parents.length - 1]);
            $container.width($container.width());
            
            if(examples && editable.input.$input.parent().find('.examples')){
                examples = '"' + examples.join('", "') + '"';
                editable.input.$input.after('<div class="examples hltip"><strong>' + labels['examples'] + '</strong> ' + examples + '</div>');
                
            }
        },5);
        
        if(val === ''){
            editable.input.$input.val($this.data('placeholder'));
            
        }
    }).on('save', function(e, params) {
        var $this = $(this);
        
        if(params.newValue === $this.data('placeholder')){
            $(this).removeClass('editable-unsaved');
            params.newValue = '';
            params.submitValue = '';
        }
        return;
    });
    
    $('.show-all input').on('change', function(){
        if(this.checked){
            $(this).parents('section.filter-section').find('p.js-text-config.hidden').removeClass('hidden');
        } else {
            $(this).parents('section.filter-section').find('p.js-text-config.js-optional').addClass('hidden');
            
        }
    });
    
    function searchByKeyword(){
        var keyword = $('.js-subsite-map-search--input').val();

        MapasCulturais.geocoder.geocode({fullAddress: keyword}, function(r){
            map.setView(r);
        });
    }
    
    $('.js-subsite-map-search--button').on('click', function(e){
        searchByKeyword();
    });
    
    $('.js-subsite-map-search--input').on('keypress', function(e){
        if(e.keyCode === 13){
            searchByKeyword();
        }
    });
});
