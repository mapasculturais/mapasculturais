$(function(){
    MapasCulturais.EventOccurrenceManager.initMapTogglers('.toggle-mapa');
});


MapasCulturais.eventOccurrenceUpdateDialog = function ($caller){    
    $("#dialog-event-occurrence").addClass('occurrence-open');
    var $dialog = $($caller.data('dialog'));
    $dialog.find('h2').html($caller.data('dialog-title'));
    var template = MapasCulturais.TemplateManager.getTemplate('event-occurrence-form');
    var item = $caller.data('item') || {};

    if($caller.data('form-action') == 'edit'){
        item.formAction = item.editUrl;
        item.rule['freq_'+item.rule.frequency] = true;
        item.rule['monthly_'+item.rule.monthly] = true;

    }else{
        item.formAction = MapasCulturais.baseURL + 'eventOccurrence/create';
    }
    $dialog.find('.js-dialog-content').html(Mustache.render(template, item));

    MapasCulturais.EventOccurrenceManager.init($dialog.find('form'));
    MapasCulturais.EventHumanReadableManager.init($dialog.find('form'));


    $dialog.find('form').data('action', $caller.data('form-action'));

    MapasCulturais.Search.init('.js-search-occurrence-space');

    MapasCulturais.EventDates.init('.js-event-dates');

    var $startsAt = $dialog.find('form').find('.js-event-time');
    var $duration = $dialog.find('form').find('.js-event-duration');
    var $endsAt = $dialog.find('form').find('.js-event-end-time');
    var eventId = $dialog.find('form').find('.event-id');
    
    if(!MapasCulturais.eventId){
        MapasCulturais.eventId =  MapasCulturais.entity.id;
    }
    eventId.val(MapasCulturais.eventId);

    $startsAt.mask('00:00', {
      onComplete: function(time) {
        var mtime = moment(time, 'HH:mm');
        var duration = $duration.val();

        if(mtime.isValid() && $.isNumeric(duration)) {
            $endsAt.val(mtime.add(duration, 'minutes').format('HH:mm'));
        }
      }});

    $duration.mask('99999999')
    $duration.change(function(event) {
        var mtime = moment($startsAt.val(), 'HH:mm');
        var duration = $(this).val();

        if(mtime.isValid() && $.isNumeric(duration)) {
            $endsAt.val(mtime.add(duration, 'minutes').format('HH:mm'));
        }
    });

    $endsAt.mask('00:00', {
      onComplete: function(time) {
        var mendtime = moment(time, 'HH:mm');
        var mtime = moment($startsAt.val(), 'HH:mm');

        if(mtime.isValid() && mendtime.isValid()) {
            if(mtime > mendtime){
                mendtime = mendtime.add('days', 1);
            }
            $duration.val(Math.abs(mendtime.diff(mtime, 'minutes')));
        }
      }});
};

MapasCulturais.EventOccurrenceManager = {
    localeDateOptions : {
        locale : 'pt-BR',
        dateOptions : {
            //weekday: 'long',
            day: '2-digit', month:'2-digit', year:'numeric'
        }
    },
    formatDate : function (value){
        if(!value)
            return '';
        else {
            value = value.replace(/-/g, ' '); // Corrige Firefox
            return new Date(value + ' 12:00:00 GMT')
                .toLocaleDateString(this.localeDateOptions.locale, this.localeDateOptions.dateOptions);
        }
    },
    init : function(selector) {
        var labels = MapasCulturais.gettext.singleEvents;
        $(selector).ajaxForm({
            success: function (response, statusText, xhr, $form) {
                var modal_form = $form[0]['className'];
                var modal_id = $form[0]['id'];

                if (modal_id && modal_form && (modal_form === "create-entity is-attached")) {
                    var new_space = response.id;
                    var space = response.name;
                    if (new_space && space) {
                        /*
                         @TODO: usar string localizada
                        */
                        MapasCulturais.Messages.success("Espaço criado com sucesso!");

                        $('.js-search-occurrence-space').data('value', new_space).text(space);
                        $('#espaco-do-evento').val(new_space);

                        var toggle = modal_id.replace('form-for-', '');
                        toggleEventModal();
                        $('.modal-loading').hide();
                    }
                } else {
                    $form.find('.danger').not('.alert').remove();
                    if(response.error){
                        var $element = null,
                            message;

                        for(i in response.data) {
                            message = response.data[i].join(', ').toLowerCase();

                            if(i == 'space') $element = $form.find('.js-space');
                            else $element = $form.find('[name="'+i+'"]').parents('.grupo-de-campos').find('label');
                            // $element.append('<span class="danger hltip" data-hltip-classes="hltip-danger" title="'+labels['Erro:']+message+'"/>');
                            $element.append('<span class="danger hltip" data-hltip-classes="hltip-danger" title="' + labels['Erro'] + ':' + message + '"/>');
                            //$form.find('[name="'+i+'"]')
                        }
                        $form.parent().scrollTop(0);
                        $form.find('div.alert.danger').html(labels['correctErrors'])
                            .fadeIn(MapasCulturais.Messages.fadeOutSpeed)
                            .delay(MapasCulturais.Messages.delayToFadeOut)
                            .fadeOut(MapasCulturais.Messages.fadeOutSpeed);

                        return;

                    }

                    response.pending = xhr.status === 202;

                    var isEditing = $form.data('action') == 'edit';
                    var template = MapasCulturais.TemplateManager.getTemplate('event-occurrence-item');

                    if (response.rule) {
                        response.rule.screen_startsOn = MapasCulturais.EventOccurrenceManager.formatDate(response.rule.startsOn);
                        response.rule.screen_until = MapasCulturais.EventOccurrenceManager.formatDate(response.rule.until);
                        response.rule.screen_frequency = MapasCulturais.frequencies[response.rule.frequency];
                    }

                    var $renderedData = $(Mustache.render(template, response));
                    var $editBtn = $renderedData.find('.js-open-dialog');
                    $editBtn.data('item', response);
                    if(isEditing){
                        $('#event-occurrence-'+response.id).replaceWith($renderedData);
                    }else{
                        $('.event-occurrence-list').removeClass("hidden");
                        $('.js-event-occurrence').append($renderedData);
                    }
                    MapasCulturais.Modal.initButtons($editBtn);
                    MapasCulturais.Modal.close('#dialog-event-occurrence');
                    $("#dialog-event-occurrence").removeClass('occurrence-open');

                    //Por enquanto sempre inicializa o mapa
                    if($('#occurrence-map-'+response.id).length){
                        MapasCulturais.Map.initialize({mapSelector:'#occurrence-map-'+response.id,locateMeControl:false});
                        MapasCulturais.EventOccurrenceManager.initMapTogglers($('#event-occurrence-'+response.id).find('.toggle-mapa'));
                    }
                    
                    if(response.pending){
                        $(".pendin-space-"+response.space.id).html(' Aguardando confirmação do espaço <strong>'+ response.space.name + '</strong>');
                        $(".pendin-space-"+response.space.id).toggle('hidden');
                        console.log(response.space)
                        MapasCulturais.Messages.alert(labels['requestAddToSpace'].replace('%s', '<strong>' + response.space.name + '</strong>'));
                    }
                }
            },
            error: function(xhr, textStatus, errorThrown, $form) {
                $form.parent().scrollTop(0);

                if(xhr.status === 403){
                    $form.find('div.alert.danger').html(labels['notAllowed'])
                        .fadeIn(MapasCulturais.Messages.fadeOutSpeed)
                        .delay(MapasCulturais.Messages.delayToFadeOut)
                        .fadeOut(MapasCulturais.Messages.fadeOutSpeed);
                }else{
                    $form.find('div.alert.danger').html(labels['unexpectedError'])
                        .fadeIn(MapasCulturais.Messages.fadeOutSpeed)
                        .delay(MapasCulturais.Messages.delayToFadeOut)
                        .fadeOut(MapasCulturais.Messages.fadeOutSpeed);
                }
            },
            dataType:  'json',
            beforeSubmit: function(arr, $form, options) {
                if (arr && arr.length == 5) {
                    $('.modal-loading').show();
                    return true;
                } else {
                    if ($form.find('input[name="description"]').data('synced') != 1)
                        return confirm(labels['confirmDescription']);

                    return true;
                }
            }
        });

        $(selector).find('.js-select-frequency').change(function(){
            $(selector).find('.js-freq-hide').not('.js-' + $(this).val())
                .hide()
                .find('input').not('[type=checkbox]').val('');
            $(selector).find('.js-freq-hide.js-' + $(this).val()).show();
            $(selector).find('.js-freq-hide').not('.js-' + $(this).val()).find('input[type=checkbox]').attr('checked', false);
        });
        $(selector).find('.js-select-frequency').change();
    },
    initMapTogglers : function(selector){
        $(selector).click(function() {
            var $map = $(this).closest('.regra').find('.mapa');
            MapasCulturais.reenableScrollWheelZoom = false;
            if($map.is(':visible')){
                $map.slideUp('fast');
                $(this).parent().find('.ver-mapa').show();
                $(this).parent().find('.ocultar-mapa').hide();

            }else{
                $map.slideDown('fast', function(){
                    $map.data('leaflet-map').invalidateSize();
                    $map.data('leaflet-map').scrollWheelZoom.disable();
                });
                $(this).parent().find('.ver-mapa').hide();
                $(this).parent().find('.ocultar-mapa').show();
            }

            return false;
        });
    }
};

MapasCulturais.EventDates = {
    init : function (selector) {
        $(selector).each(function(){
            var fieldSelector = '#'+$(this).attr('id');
            var altFieldSelector = $(this).data('alt-field') ? $(this).data('alt-field') : fieldSelector.replace('-visible', '');
            if($(altFieldSelector).length == 0){
                return;
            }
            $(this).datepicker({
                dateFormat: $(this).data('date-format') ? $(this).data('date-format') : 'dd/mm/yy',
                altFormat: $(this).data('alt-format') ? $(this).data('alt-format') : 'yy-mm-dd',
                altField: altFieldSelector
            });
        });
    }
};

MapasCulturais.EventHumanReadableManager = {

    init : function(selector) {

        var $form = $(selector);

        // Add onChange events to the fields
        $(selector).find('input[type="checkbox"], select[name="frequency"], #horario-de-inicio, .js-start-date, .js-end-date').change(function() {
            MapasCulturais.EventHumanReadableManager.updateSuggestion($form);
        });

        // If there are values, run it at init
        var date_s = $(selector).find('.js-start-date').val();
        var hour = $(selector).find('#horario-de-inicio').val();
        if (date_s && hour)
            MapasCulturais.EventHumanReadableManager.updateSuggestion($form);

        $(selector).find('.grupo-descricao-automatica > a').click(function() {
            $(selector).find('input[name="description"]').val( $(selector).find('#descricao-automatica').html() ).data('synced', 1);
        });

        //On init, we assume we are allways synced, even if the values are different, because the could have been manually edited
        $(selector).find('input[name="description"]').data('synced', 1);
    },
    updateSuggestion: function(selector) {
        var human = MapasCulturais.EventHumanReadableManager.getSuggestion(selector);
        $(selector).find('#descricao-automatica').html(human);
        if (human == $(selector).find('input[name="description"]').val())
            $(selector).find('input[name="description"]').data('synced', 1);
        else
            $(selector).find('input[name="description"]').data('synced', 0);
    },
    getSuggestion: function(selector) {

        var date_s = $(selector).find('.js-start-date').val();
        var hour = $(selector).find('#horario-de-inicio').val();
        var frequency = $(selector).find('select[name="frequency"]').val();
        var date_e = $(selector).find('.js-end-date').val();
        var weekDays = [];
        $(selector).find('input[type="checkbox"]:checked').each(function() { if ($(this).is(':checked')) weekDays.push($(this).attr('name').replace(/[^\d]/g, '')) });

        var mdate_s = false;
        var mdate_e = false;

        if (date_s) mdate_s = moment(date_s, 'DD/MM/YYYY');
        if (date_e) mdate_e = moment(date_e, 'DD/MM/YYYY');

        return MapasCulturais.createHumanReadableOccurrences(frequency, mdate_s, mdate_e, weekDays, hour);

    }
};
