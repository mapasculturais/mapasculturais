MapasCulturais.eventOccurrenceUpdateDialog = function ($caller){
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
    $dialog.find('form').data('action', $caller.data('form-action'));

    MapasCulturais.Search.init('.js-search-occurrence-space');

    MapasCulturais.EventDates.init('.js-event-dates');


    $dialog.find('form').find('.js-event-times').mask('00:00');
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
        else
            return new Date(value + ' 12:00:00 GMT')
                .toLocaleDateString(this.localeDateOptions.locale, this.localeDateOptions.dateOptions);
    },
    init : function(selector) {
        $(selector).ajaxForm({
            success: function (response, statusText, xhr, $form)  {
                $form.find('.erro').not('.mensagem').remove();
                if(response.error){
                    var $element = null,
                        message;
                    for(i in response.data) {
                        message = response.data[i].join(', ').toLowerCase();

                        if(i == 'space') $element = $form.find('.js-space');
                        else $element = $form.find('[name="'+i+'"]').parents('.grupo-de-campos').find('label');
                        $element.append('<span class="erro hltip" data-hltip-classes="hltip-erro" title="Erro:'+message+'"/>');
                        //$form.find('[name="'+i+'"]')
                    }
                    $form.find('div.mensagem.erro').html('Corrija os seguintes erros abaixo.')
                        .fadeIn(MapasCulturais.Messages.fadeOutSpeed)
                        .delay(MapasCulturais.Messages.delayToFadeOut)
                        .fadeOut(MapasCulturais.Messages.fadeOutSpeed);

                    return;

                }
                var isEditing = $form.data('action') == 'edit';
                var template = MapasCulturais.TemplateManager.getTemplate('event-occurrence-item');

                response.rule.screen_startsOn = MapasCulturais.EventOccurrenceManager.formatDate(response.rule.startsOn);
                response.rule.screen_until = MapasCulturais.EventOccurrenceManager.formatDate(response.rule.until);
                response.rule.screen_frequency = MapasCulturais.frequencies[response.rule.frequency];

                var $renderedData = $(Mustache.render(template, response));
                var $editBtn = $renderedData.find('.js-open-dialog');
                $editBtn.data('item', response);
                console.log('response', response);
                if(isEditing){
                    $('#event-occurrence-'+response.id).replaceWith($renderedData);
                }else{
                    $('.js-event-occurrence').append($renderedData);
                }
                MapasCulturais.Modal.initButtons($editBtn);
                $form.parents('.js-dialog').find('.js-close').click();

                //Por enquanto sempre inicializa o mapa
                MapasCulturais.Map.initialize({mapSelector:'#occurrence-map-'+response.id,locateMeControl:false});
            },
            error: function(xhr, textStatus, errorThrown, $form) {
                $form.find('div.mensagem.erro').html('Erro inesperado.')
                    .fadeIn(MapasCulturais.Messages.fadeOutSpeed)
                    .delay(MapasCulturais.Messages.delayToFadeOut)
                    .fadeOut(MapasCulturais.Messages.fadeOutSpeed);
            },
            dataType:  'json'
        });

        $(selector).find('.js-select-frequency').change(function(){
            $(selector).find('.js-freq-hide').not('.js-' + $(this).val())
                .hide()
                .find('input').val('').attr('checked', false);
            $(selector).find('.js-freq-hide.js-' + $(this).val()).show();
        });
        $(selector).find('.js-select-frequency').change();
    }
};

MapasCulturais.EventDates = {
    init : function (selector) {
        $(selector).each(function(){
            var fieldSelector = '#'+$(this).attr('id');
            var altFieldSelector = $(this).data('alt-field') ? $(this).data('alt-field') : fieldSelector.replace('-visible', '');
            if($(altFieldSelector).length == 0){
                console.log('EventDates could not find alternative visible field element. Exiting');
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