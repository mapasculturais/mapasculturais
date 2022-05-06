$(function() {
    function setAgendaCount(){
        var count = $('#agenda-count-hidden').val() || MapasCulturais.gettext.agendaSingles['none'];
        $('#agenda-count').text(count);
        $('#agenda-count-plural').css('display', count == 1 || count == MapasCulturais.gettext.agendaSingles['none'] ? 'none' : 'inline');
    }

    function formatDate(date){
        return date ? date.split('/').reverse().join('-') : {};
    }

    function setSpreadsheetUrl(){
        var apiExportURL = MapasCulturais.baseURL + 'api/';
        var selectData = MapasCulturais.searchQueryFields;
        var entity = 'event';
        var dateFrom = formatDate($('#agenda-from-visible').val());
        var dateTo = formatDate($('#agenda-to-visible').val());
        var searchData = {};
        var action = 'findByLocation';
        var Description = MapasCulturais.EntitiesDescription[entity];
        var querystring = '';
        var exportSelect = ['singleUrl,type,terms'];
        var dontExportSelect = {
            user: true,
            publicLocation: true,
            status: true
        }

        //event listener para mudança nos campos de data
        $('#agenda-from-visible, #agenda-to-visible').on('change', function(){
            setSpreadsheetUrl();
        });

        //agente(owner), espaço ou projeto
        var entityToFilter;

        if(MapasCulturais.entity.object.controllerId === 'space' || MapasCulturais.entity.object.controllerId === 'project'){
            entityToFilter = MapasCulturais.entity.object.controllerId;
        }else{
            entityToFilter = 'owner';
        }
        
        //itens referentes aos eventos
        selectData += ',classificacaoEtaria,project.name,project.singleUrl,occurrences.{*,space.{*}}';
        searchData['@from'] = dateFrom;
        searchData['@to'] = dateTo;
        searchData[entityToFilter] = 'EQ('+MapasCulturais.entity.id+')';
        searchData['@select'] = 'id,name,location';
        searchData['@order'] = 'name ASC';
        Object.keys(Description).forEach(function(prop) {
            if(prop[0] == '_'){
                return;
            }
            if (dontExportSelect[prop])
                return;
                
            var def = Description[prop];
            var selectProperty = def['@select'] || prop;
            if(def.isMetadata || (!def.isMetadata && !def.isEntityRelation)){
                
                // Não adiciona os metadados geograficos que devem ser ocultos (que começam com "_")
                if (prop.substr(0,4) == 'geo_')
                    return;
                
                exportSelect.push(selectProperty); 
            } else if(def.isEntityRelation) {
                if(def.isOwningSide){
                    exportSelect.push(prop + '.{id,name,singleUrl}');
                } else if (prop == 'occurrences') {
                    exportSelect.push('occurrences.{space.{id,name,singleUrl,En_CEP,' + 
                                'En_Nome_Logradouro,En_Num,En_Complemento,En_Bairro,En_Municipio,En_Estado},rule}');
                }
            }
        });
        var queryString_apiExport = '@select='+exportSelect.join(',');

        //removes type column from event export
        if(apiExportURL.indexOf('event/findByLocation') !== -1)
            queryString_apiExport = queryString_apiExport.replace(',type','');
        else
            apiExportURL += entity + '/' + action + '/?';

        for(var att in searchData) {
            querystring += "&"+att+"="+searchData[att];
            if(att != '@select' && att!='@page' && att!='@limit' && att!='@files')
                queryString_apiExport += "&"+att+"="+searchData[att];
        }
        
        var spreadsheetUrl = apiExportURL+queryString_apiExport+'&@type=excel';
        $('#agenda-spreadsheet-button').attr('href', spreadsheetUrl);
    }
    
    function submit(){
        $('img.spinner').show();
        if(MapasCulturais.request.controller == 'registration') {
            var url = MapasCulturais.baseURL+'agent/agendaSingle/'+MapasCulturais.entity.ownerId;
        } else {
            var url = MapasCulturais.baseURL+MapasCulturais.request.controller+'/agendaSingle/'+MapasCulturais.entity.id;
        }

        $.get(url, {from:$('#agenda-from').val(),to:$('#agenda-to').val()}, function(result){
            $('#agenda-content').html(result);
            setAgendaCount();
            $('img.spinner').hide();
        });
    };
        
    $('.js-agenda-singles-dates').each(function() {
        var fieldSelector = '#' + $(this).attr('id');
        var altFieldSelector = $(this).data('alt-field') ? $(this).data('alt-field') : fieldSelector.replace('-visible', '');
        if ($(altFieldSelector).length == 0) {
            return;
        }
        $(this).datepicker({
            dateFormat: $(this).data('date-format') ? $(this).data('date-format') : 'dd/mm/yy',
            altFormat: $(this).data('alt-format') ? $(this).data('alt-format') : 'yy-mm-dd',
            altField: altFieldSelector,
            beforeShow: function() {
                setTimeout(function() {
                    $('.ui-datepicker').css('z-index', 99999999999999);
                }, 0);
            }
        });

        $(this).on('change', submit);
    });
    
    $('#tab-sobre').parent().after($('#tab-agenda').parent());

    setSpreadsheetUrl();
    setAgendaCount();
    submit();
});
