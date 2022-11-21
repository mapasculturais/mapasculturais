MapasCulturais = MapasCulturais || {};
tabIndex = function() { window.tabEnabled = true };

var isDateSupported = function () {
    var input = document.createElement('input');
    var value = 'a';
    input.setAttribute('type', 'date');
    input.setAttribute('value', value);
    return (input.value !== value);
};

jQuery(function(){
    $.fn.editable.defaults.mode = 'inline';
    $.fn.editableform.buttons = '<button type="button" class="editable-cancel btn btn-default">cancelar</button> <button type="submit" class="editable-submit">ok</button>';
    $.fn.select2.defaults.separator = '; ';
    $.fn.editabletypes.select2.defaults.viewseparator = '; ';
    MapasCulturais.Editables.init('#editable-entity');
    MapasCulturais.AjaxUploader.init();
    MapasCulturais.MetalistManager.init();

    var labels = MapasCulturais.gettext.editable;

    MapasCulturais.RemoveBanner.init();

    // Registration Valuer Include e Exclude list
    var registration_valuer_form = 'form.js--registration-valuers-include-exclude-form';
    var rix_list_timeout;
    $(registration_valuer_form + ' .sendable').on('change', function() {
        var $form = $(registration_valuer_form);
        clearTimeout(rix_list_timeout);
        rix_list_timeout = setTimeout(function(){
            var url = MapasCulturais.createUrl('registration', 'valuersExceptionsList', [MapasCulturais.entity.id]);
            $.ajax(url,{
                method: 'PATCH',
                data: $form.serializeArray()
            }).success(function(){
                MapasCulturais.Messages.success('Avaliador alterado com sucesso!');
            });
        },10);
    });

    $('.js-registration-action').click(function(){
        if($(this).hasClass('selected'))
            return false;

        var href = $(this).data('href');
        var data = {agentId: $(this).data('agent-id')};
        var $this = $(this);

        $.post(href, data, function(response){
            $this.parent().find('.js-registration-action').removeClass('selected');
            $this.addClass('selected');

            // status = 1 (aprovado)
            // status = -6 (rejeitado)

        });
        return false;
    });

    //Máscaras dos formulários de oportunidades
    $('.registration-edit-mode input').each(function() {
        if($(this).data('mask')){
            $(this).mask($(this).data('mask'));
        }
    });

    //Máscara de moeda BRL para campos com a classe .js-mask-currency
    if($('.js-mask-currency').length){
        $('.js-mask-currency').mask('###.###.###.###.###.##0,00', {
            reverse: true
        });
    }

    //Máscaras de telefone, CEP e hora
    $('.js-editable').on('shown', function(e, editable) {
        if ($(this).hasClass('js-editablemask')) {
            var mask = $(this).data('mask');

            editable.input.$input.mask(mask, {
                placeholder: $(this).data('placeholder'),
                clearIfNotMatch: true,
            });
        }

        if ($(this).hasClass('js-mask-phone')) {
            /* Phone masks is an array of masks that are used depending on the size of the string.
             * 
             * If you have more than one, the first mask has to have one optional character, and the next mask will have
             * this required character and, optionally, another optiona, and so on...
             * 
             * Example: ['00-0009', '000-009', '0000-000']
             * 
             */
            if (MapasCulturais.phoneMasks === false) return;
            var masks = MapasCulturais.phoneMasks ? MapasCulturais.phoneMasks : ['(00) 0000-00009', '(00) 00000-0000'];
            editable.input.$input.mask(masks[0], {onKeyPress:
                function(val, e, field, options) {
                    if (masks.length > 1) {
                        for (var ii=1; ii<masks.length; ii++) {
                            field.mask(val.length > masks[ii-1].length - 1 ? masks[ii] : masks[ii-1], options);
                        }

                    }

                }
            });
        }

        if ($(this).hasClass('js-mask-cep')) {
            if (MapasCulturais.postalCodeMask === false) return;
            var masks = MapasCulturais.postalCodeMask ? [MapasCulturais.postalCodeMask] : ['00000-000'];
            editable.input.$input.mask(masks[0], {onKeyPress:
               function(val, e, field, options) {
                   field.mask(masks[0], options) ;
               }
            });
        }

        if ($(this).hasClass('js-mask-cnpj')) {
            if (MapasCulturais.postalCodeMask === false) return;
            var masks = MapasCulturais.postalCodeMask ? [MapasCulturais.postalCodeMask] : ['00.000.000/0000-00'];
            editable.input.$input.mask(masks[0], {onKeyPress:
               function(val, e, field, options) {
                   field.mask(masks[0], options) ;
               }
            });
        }

        if ($(this).hasClass('js-mask-cpf')) {
            if (MapasCulturais.postalCodeMask === false) return;
            var masks = MapasCulturais.postalCodeMask ? [MapasCulturais.postalCodeMask] : ['000.000.000-00'];
            editable.input.$input.mask(masks[0], {onKeyPress:
               function(val, e, field, options) {
                   field.mask(masks[0], options) ;
               }
            });
        }

        if ($(this).hasClass('js-mask-time')) {
            //Mask
            var masks = ['00:00'];
            editable.input.$input.mask(masks[0], {onKeyPress:
               function(val, e, field, options) {
                   field.mask(masks[0], options) ;
               }
            });
        }

        // Fixes editable input size based on placeholder length
        var placeholder = editable.input.$input.attr('placeholder'),
        possibleSize = placeholder ? Math.max(placeholder.length, editable.value.length + 5) : 0;
        if (possibleSize > 20) {
            editable.input.$input.attr('size', possibleSize);
        }


        // Fixes padding right hardcoded on 24px, now 0;
        //editable.input.$input.css('padding-right', 10);
        $("[data-element='shortDescription']").on("keyup", function(){
            $("[data-element='countLength']").html(this.value.length);
        })
    });

    //Display Default Shortcuts on Editable Buttons and Focus on select2 input
    $('.editable').on('shown', function(e, editable) {

        editable.container.$form.find('.editable-cancel').attr('title', labels['cancel']);
        //textarea display default Ctrl+Enter and Esc shortcuts
        switch (editable.input.type.trim()) {
            case 'text|select':
                editable.container.$form.find('.editable-submit').attr('title', labels['confirm']);
                break;
            case 'textarea':
                editable.container.$form.find('.editable-submit').attr('title', labels['confirmC']);
                break;
            case 'select2':
                editable.container.$form.find('.editable-submit').attr('title', labels['confirmC']);
                setTimeout(function() {
                    editable.container.$form.find('.select2-input')
                        .focus()
                        .on('keydown', function(e){
                            if(e.which == 13 && e.ctrlKey) {
                                editable.container.$form.find('.editable-submit').click();
                            }
                        });
                }, 100);
                break;
        }

        //Place the buttons below the textarea
        if(window.innerWidth < 980){
            if(editable.input.type.trim() === 'textarea'){
                $('.editable-buttons').css('display', 'block');
            }
        }
        if(editable.input.type.trim() === 'dateuifield'){
            if (isDateSupported()) {
                //Remove calendar icon
                $('.ui-datepicker-trigger').css('display', 'none');
            }

        }

        //Experimental Tab Index
        if(window.tabEnabled) {
            var $el = editable.$element;
            editable.input.$input.on('keydown', function(e) {
                if(e.which == 9) {                                                      // when tab key is pressed
                    e.preventDefault();
                    if(e.shiftKey) {                                                    // shift + tab
                        $(this) .blur();
                        $el.parents().prevAll(":has(.editable):first") // find the parent of the editable before this one in the markup
                        .find(".js-editable:last").editable('show');                    // grab the editable and display it
                    } else {                                                            // just tab
                        $(this) .blur();
                        $el.parents().nextAll(":has(.editable):first") // find the parent of the editable after this one in the markup
                        .find(".js-editable:first").editable('show');                   // grab the editable and display it
                    }
                }
            });
            editable.$element.on('hidden', function(e, reason) {
                if(reason == 'save' || reason == 'nochange')
                    $el.parents().nextAll(":has(.editable):first").find(".editable:first").editable('show');
            });
        }
    });

    if($('.js-verified').length){
        $('.js-verified').click(function(){
            var $this = $(this);
            if($this.hasClass('active')){
                $this.removeClass('active');
                $('#is-verified-input').editable('setValue', 0);
            }else{
                $this.addClass('active');
                $('#is-verified-input').editable('setValue', 1);
            }
            return false;
        });
    }

    // Human Crop for images
    $('input.human_crop').change(function() {

        if (!window.FileReader)
            return; // browser não suporta

        var reader = new FileReader();
        var $form = $(this).closest('form');
        var $formEditBox = $form.closest('.js-editbox');
        var $sendButton = $('#editbox-human-crop').find('button[type="submit"]');

        var cropWidth = $form.data('crop-width');
        var cropHeight = $form.data('crop-height');

        $sendButton.html(labels['Crop']);

        reader.onload = function(event) {
            the_url = event.target.result
            $('#human-crop-image').attr('src', the_url);

            var croppedImage;

            var cropper = $('#human-crop-image').cropbox({
                width: cropWidth,
                height: cropHeight,
                showControls: 'always',
                zoom: 30,
                //controls: '<div class="cropControls"><span>Arraste para cortar</span><button class="cropZoomIn" type="button"></button><button class="cropZoomOut" type="button"></button></div>',
            }, function() {
                // on load
                $('.cropControls span').html(labels['CropHelp']); // it did not work set the controls options. the buttons did not work
            }).on('cropbox', function(e, data, img) {
                croppedImage = img.getBlob();
            });

            $sendButton.one('click',function() {
                var formData = new FormData();
                formData.append($form.data('group'), croppedImage);

                // Dont ask me how, but I found this way to manipulate AjaxForm options
                $._data($form[0], 'events')['submit'][0].data.processData = false;
                $._data($form[0], 'events')['submit'][0].data.formData = formData;

                $formEditBox.show();
                MapasCulturais.EditBox.close('#editbox-human-crop');

            });

        }

        reader.readAsDataURL(this.files[0]);

        // copy the classes from the original editBox, so we position our new editbox the same way
        $('#editbox-human-crop').attr('class', $formEditBox.attr('class')).width(cropWidth).height(cropHeight + 80);
        MapasCulturais.EditBox.open('#editbox-human-crop', $($form.data('target')));

        // hide original editBox
        $formEditBox.hide();

    });

});

function toggleRegistrationEvaluator(field) {
    var _ref = $(field).val();
    if (_ref) {
        var _evaluator = _ref.replace('ref-','');
        var _ev_field = 'input[value=' + _evaluator + ']';
        $(_ev_field).click();
    }
}

$(window).on('beforeunload', function(){
    var labels = MapasCulturais.gettext.editable;
    if($('.editable-unsaved').length){
        return labels['unsavedChanges'];
    }
});

MapasCulturais.RemoveBanner = {
    init: function(){
        $('body').on('click', '.banner-delete', function(){
            var href   = $(this).data('href');
            var result = window.confirm(MapasCulturais.gettext.editable['removeAgentBackground']);
            $('#remove-background-button').toggleClass('display-background-button');
            $('#remove-background-button').toggleClass('hide-background-button');

            if(result){
                $.getJSON(href, function(r){
                    if(r.error){
                        MapasCulturais.Messages.error(r.data);
                    }else{
                        $('#header-banner').css('background-image', 'url()');
                    }
                });
            }
            else{
                return false;
            }
        });
    }
};

MapasCulturais.Editables = {

    dataSelector: 'edit',
    baseTarget : '',

    init : function(editableEntitySelector) {
        this.baseTarget = MapasCulturais.baseURL+$(editableEntitySelector).data('entity');
        this.createAll();

        if(MapasCulturais.isEditable){
            this.setButton(editableEntitySelector);
            this.initTaxonomies();
            this.initTypes();

            if(MapasCulturais.request.controller === 'registration')
                this.initRegistrationCategories();

            if(MapasCulturais.request.controller === 'space')
                this.initSpacePublicEditable();
        }
    },

    initSpacePublicEditable: function(){

        var labels = MapasCulturais.gettext.editable;

        $('#editable-space-status').on('hidden', function(e, reason) {
            if($(this).editable('getValue', true) == '1'){
                $('#editable-space-status').html('<div class="venue-status"><div class="icon icon-publication-status-open"></div>'+labels['freePublish']+'</div><p class="venue-status-definition">'+labels['freePlublishDescription']+'</p>');
            }else{
                $('#editable-space-status').html('<div class="venue-status"><div class="icon icon-publication-status-locked"></div>'+labels['restrictedPublish']+'</div><p class="venue-status-definition">'+labels['restrictedPublishDescription']+'</p>');
            }
        });
    },

    initTaxonomies: function (){
        $('.js-editable-taxonomy').each(function(){
            var taxonomy = $(this).data('taxonomy');

            var select2_option = {
                tags: [],
                tokenSeparators: [";",";"],
                separator:'; '
            };


            if(MapasCulturais.taxonomyTerms[taxonomy])
                select2_option.tags = MapasCulturais.taxonomyTerms[taxonomy];

            if($(this).data('restrict'))
                select2_option.createSearchChoice = function() { return null; };


            var config = {
                name: 'terms[' + taxonomy + ']',
                type: 'select2',
                select2: select2_option
            };

            //change the default poshytip animation speed both from 300ms to:
            $.fn.poshytip.defaults.showAniDuration = 80;
            $.fn.poshytip.defaults.hideAniDuration = 40;

            $(this).editable(config);
        });
    },

    initRegistrationCategories: function(){
        $('.js-editable-registrationCategory').each(function(){
            var config = {
                name: 'category',
                type: 'select',
                source: MapasCulturais.entity.registrationCategories
            };
            $(this).editable(config);
        });
    },

    initTypes: function(){
        $('.js-editable-type').each(function(){
            var entity = $(this).data('entity');
            $.each(MapasCulturais.entityTypes[entity], function(i, obj){
                obj.text = obj.name;
                obj.value = obj.id;
            });
            var config = {
                name: 'type',
                type: 'select',
                source: MapasCulturais.entityTypes[entity]
            };
            $(this).editable(config);
        });
    },

    getEditableElements : function(){
        if(MapasCulturais.isEditable)
            return $('.js-editable, .js-editable-taxonomy, .js-editable-type, .js-editable-registrationCategory');
        else
            return $('.js-xedit');
    },

    createAll : function (){
        var entity = MapasCulturais.entity.definition;
        var labels = MapasCulturais.gettext.editable;
        MapasCulturais.Editables.getEditableElements().each(function(){

            var field_name = $(this).data(MapasCulturais.Editables.dataSelector);
            var input_type;

            if(!entity[field_name])
                return;

            var config = {
                name: field_name,
                type: 'text',
                maxlength : 20,
                emptytext: entity[field_name].label,
                placeholder: entity[field_name].label,
                showbuttons: false,
                onblur: 'submit'
            };

            var select_value = null;

            switch (entity[field_name].type){
                case 'text':
                    config.type = 'textarea';
                    config.tpl = '<textarea ></textarea>'
                    break;

                case 'select':
                    config.type = 'select';
                    config.source = [];
                    for(var k in entity[field_name].options){
                        var obj = {value: k, text: entity[field_name].options[k]};

                        config.source.push(obj);
                    }

                    break;

                case 'date':
                case 'datetime':
                    config.type = 'date';
                    if (isDateSupported()) {
                        $(this).removeAttr('data-showbuttons');
                        $(this).removeAttr('data-viewformat');
                        config.display = function (value) {
                            if(value){
                                $(this).html(moment(value).format('DD/MM/YYYY'));
                            }
                        };
                        config.tpl = '<input type="date" ></input>';
                    }else{
                        config.format     = 'yyyy-mm-dd';
                        config.viewformat = 'dd/mm/yyyy';
                        config.datepicker = {weekStart: 1, yearRange: $(this).data('yearrange') ? $(this).data('yearrange') : "1900:+10"};
                        delete config.placeholder;
                        config.clear = labels['Limpar'];
                    }
                    break;

                case 'multiselect':
                    var select2_option = {
                        tags: [],
                        tokenSeparators: [";", ";"],
                        separator: '; '
                    };


                    if (entity[field_name].options) {
                        select2_option.tags = [];
                        Object.keys(entity[field_name].options).forEach(function (k) {
                            select2_option.tags.push({
                                id: k,
                                text: entity[field_name].options[k]
                            });
                        });
                    }


                    select2_option.createSearchChoice = function () {
                        return null;
                    };


                    config.type = 'select2';
                    config.select2 = select2_option;

                    config.display = function (value, sourceData) {
                        if (value) {
                            var html = value.map(function (i) {
                                return entity[field_name].options[i];
                            }).join('; ');
                        }

                        $(this).html(html);

                    }

                    //change the default poshytip animation speed both from 300ms to:
                    $.fn.poshytip.defaults.showAniDuration = 80;
                    $.fn.poshytip.defaults.hideAniDuration = 40;
                    break;

                case 'tag':
                    var select2_option = {
                        tags: [],
                        tokenSeparators: [";", ";"],
                        separator: '; '
                    };


                    if (entity[field_name].options)
                        select2_option.tags = Object.keys(entity[field_name].options);


                    config.type = 'select2';
                    config.select2 = select2_option;

                    //change the default poshytip animation speed both from 300ms to:
                    $.fn.poshytip.defaults.showAniDuration = 80;
                    $.fn.poshytip.defaults.hideAniDuration = 40;
                    break;

                case 'boolean':
                    config.type = 'checklist';
                    config.source = [{value : 0, text: 'false' }, { value: 1, text: 'true' }];
                    config.emptytext = 'Não';
            }

            if(config.type !== 'date'){
                $(this).editable(config);
            }

            if(config.type === 'select'){
                var $e = $(this);
                var v = $e.html();
                config.source.forEach(function(e){
                    if(e.value === v){
                        $e.editable('setValue', v);
                    }
                });
            }

            if($(this).data('notext')){
                $(this).text('');
                var that = this;
                $(this).on('hidden', function(){
                    $(that).text('');
                });
            }

            if(config.type === 'date'){

                var $datepicker = $(this);

                if(!$(this).data('timepicker')){ //normal datepicker
                    $datepicker.editable(config);
                    $datepicker.on('hidden', function(e, editable) {
                        if($(this).editable('getValue', true) == null){
                            $(this).editable('setValue', '');
                        }
                    });
                }else{ //datepicker with related timepicker field
                    var $timepicker = $($datepicker.data('timepicker'));
                    var $hidden = $('<input class="js-include-editable" type="hidden">').insertAfter($timepicker);

                    $datepicker.attr('data-edit', $datepicker.data('edit') + '_datepicker');

                    $timepicker.editable();
                    $hidden.editable({name: $datepicker.data('edit')});
                    $datepicker.editable(config);

                    if($timepicker.data('datetime-value'))
                        $hidden.editable('setValue', moment($timepicker.data('datetime-value')).format('YYYY-MM-DD HH:mm'));
                    else
                        $hidden.editable('setValue', '');

                    $timepicker.on('save', function(e, params) {

                        if(!params.newValue){
                            params.newValue = '23:59';
                            $timepicker.editable('setValue', '23:59');
                        }
                        $hidden.editable('setValue',
                            moment($datepicker.editable('getValue', true)).format('YYYY-MM-DD') + ' ' + params.newValue
                        );
                    });

                    $datepicker.on('save', function(e, params) {
                        if(params.newValue){
                            if(!$timepicker.editable('getValue', true)){
                                $timepicker.editable('setValue', '23:59');
                            }

                            $hidden.editable('setValue',
                                moment(params.newValue).format('YYYY-MM-DD') + ' ' + $timepicker.editable('getValue', true)
                            );
                        }else{
                            $hidden.editable('setValue', '');
                            $timepicker.editable('setValue', '');
                        }
                    });

                    $timepicker.on('shown', function(e, editable) {
                        var $input = editable.input.$input;
                        $input.mask('00:00', {
                            onComplete: function(time) {

                        }});
                    });

                }

            }

        });

    },


    setButton : function (editableEntitySelector){
        var $submitButton = $('.js-submit-button'),
            $archiveButton = $('.js-archive-button');

        //Ctrl+S:save
        $(document.body).on('keydown', function(event){
            if(event.ctrlKey && event.keyCode === 83){
                event.preventDefault();
                event.stopPropagation();
                $submitButton.each(function(){
                    if($(this).data('status') == MapasCulturais.entity.status){
                        $(this).trigger('click');
                    }
                });
            }
        });

        $submitButton.click(function(){
            $('.editable-empty.editable-unsaved').each(function(){
                $(this).editable('setValue', '');
            });
            var target; //Vazio
            var $button = $(this); // Retorna submitButton
            var controller = MapasCulturais.request.controller; //Retorna controller da entidade atual
            var action = $(editableEntitySelector).data('action'); //"edit"
            var $editables = MapasCulturais.Editables.getEditableElements().add('.js-include-editable');

            var labels = MapasCulturais.gettext.editable;

            if(action === 'create'){
                target = MapasCulturais.createUrl(controller, 'index');

            }else{
                target = MapasCulturais.createUrl(controller, 'single', [$(editableEntitySelector).data('id')]);

                if(MapasCulturais.entity.status == 0 && $button.data('status') == 1){
                    var message = MapasCulturais.request.controller === 'event' ?
                        labels['confirmPublish'].replace('%s', labels['this_' + MapasCulturais.request.controller]) :
                        labels['confirmPublishFinal'].replace('%s', labels['this_' + MapasCulturais.request.controller]);

                    if(!confirm(message)){
                        return;
                    }
                }
            }

            if($submitButton.data('clicked'))
                return false;

            $submitButton.data('clicked', 'sim');

            if($editables.length === 1){
                $('body').append('<input type="hidden" id="fixeditable"/>');
                $editables = $editables.add($('#fixeditable'));
            }

            $editables.editable('submit', {
                url: target,
                data: { status: $button.data('status') },
                ajaxOptions: {
                    dataType: 'json', //assuming json response
                    type: action === 'create' ? 'post' : 'put',
                    statusCode: {
                        202: function(response, statusText, r) {
                            var createdRequests = JSON.parse(r.getResponseHeader('CreatedRequests')),
                                typeName = MapasCulturais.entity.getTypeName(),
                                name = '';

                            if(createdRequests && createdRequests.indexOf('ChildEntity') >= 0){
                                name = $('[data-field-name="parentId"]').text();
                                $('.js-pending-parent').show();
                                MapasCulturais.Messages.alert(labels['requestChild'].replace('%s', typeName).replace('%s', '<strong>'+name+'</strong>'));
                            }

                            if(createdRequests && createdRequests.indexOf('EventProject') >= 0){
                                name = $('[data-field-name="projectId"]').text();
                                $('.js-pending-project').show();
                                MapasCulturais.Messages.alert(labels['requestEventProject'].replace('%s', '<strong>'+name+'</strong>'));
                            }
                        }
                    }
                },
                success: function(response){
                    $('.js-pending-project, .js-pending-parent').hide();
                    $('.js-response-error').remove();
                    if(response.error){
                        var $field = null;
                        var errors = '';
                        var unknow_errors = [];
                        var field_found = false;
                        var firstShown = false;
                        for(var p in response.data){
                            $field = $('.js-editable[data-edit="' + p + '"]');
                            if(MapasCulturais.request.controller === 'event' && p === 'project'){
                                $field = $('.editable[data-field-name="projectId"');
                            }else if(p.substr(0,5) == 'term-'){
                                $field = $('#' + p);
                            }else if(p == 'type'){
                                $field = $('.js-editable-type');
                            }else if(MapasCulturais.request.controller === 'registration' && p === 'owner'){
                                firstShown = true; // don't show editable
                                $field = $('#agent_owner').parent().find('.registration-label span');
                            }

                            for(var k in response.data[p]){
                                if($field.length){
                                    field_found = true;
                                    var errorHtml = '<span title="' + response.data[p][k] + '" class="danger hltip js-response-error" data-hltip-classes="hltip-danger"></span>';
                                    try {
                                        if($field.parents("edit-box")["0"].localName === "edit-box"){
                                            $field.parents("edit-box").append(errorHtml);
                                        }
                                    }
                                    catch(e){
                                        $field.parent().append(errorHtml);
                                    }
                                }else{
                                    unknow_errors.push(response.data[p][k]);
                                }
                            }
                            if(!firstShown) {
                                firstShown = true;
                                // removido o comportamento de abrir o primemiro campo vazio. estava gerando erro em alguns casos
                                // mostrando um input text solto e também comportamento ruim quando havia campos obrigatorios
                                // em outras abas
                                //$field.editable('show');
                            }
                            $field.on('save', function(){
                                $(this).parent().find('.danger.hltip').remove();
                            });
                        }

                        if(field_found)
                            MapasCulturais.Messages.error(labels['correctErrors']);

                        if(unknow_errors){
                            for(var i in unknow_errors){
                                MapasCulturais.Messages.error(unknow_errors[i]);
                            }
                        }

                    }else{

                        $('body').trigger('entity-saved', response);

                        $('.js-geo-division-address').each(function(){
                            var r = response[$(this).data('metakey')];
                            $(this).html(r ? r : '');
                            $(this).parent().css('display', r ? 'block' : 'none');
                        });

                        MapasCulturais.Messages.success(labels['changesSaved']);

                        $('.editable-unsaved').
                                css('background-color','').
                                removeClass('editable-unsaved').
                                parent().
                                removeClass('danger');

                        
                        //parametro passado pelo backend para controllar o redirecionamento apos salvar a entidade
                        // exemplo no backend: 
                        // $json->redirect = "true";
                        // $json->url = ['controller'=>'aldirblanc', 'action'=>'selecionaragente'];
                        if(response.redirect == undefined || response.redirect === 'true' ) {
                            if(response.url && response.url.controller) {
                                
                                document.location = MapasCulturais.createUrl(response.url.controller, response.url.action, [response.id]);
                                
                            } else {

                                if(MapasCulturais.request.controller != 'registration' && (action === 'create' || response.status != MapasCulturais.entity.status)){
                                    if(response.status == 1) {
                                        document.location = MapasCulturais.createUrl(controller, 'single', [response.id]);
                                    } else {
                                        document.location = MapasCulturais.createUrl(controller, 'edit', [response.id]);
                                    }
                                }
                            }
                            
                        }

                        
                    }
                    $submitButton.data('clicked',false);
                },
                error : function(response){
                    $submitButton.data('clicked',false);
                    if(response.status === 401)
                        MapasCulturais.auth.require(function(){
                            $submitButton.click();
                        });
                    else{
                        MapasCulturais.Messages.error(labels['unexpectedError']);
                    }
                }
            });
        });

    }
};


MapasCulturais.MetalistManager = {
    init : function() {
        var labels = MapasCulturais.gettext.editable;
        // bind form using 'ajaxForm'
        $('.js-metalist-form').ajaxForm({
            //target:        '#output1',   // target element(s) to be updated with server response
            //beforeSubmit:  showRequest,  // pre-submit callback

            beforeSubmit:function(arr, $form, options){
                //por enquanto validando apenas o vídeo contendo vimeo ou youtube e o link contendo algum protocolo...
                var group = $form.parents('.js-editbox').data('metalist-group');
                var $linkField = $form.find('input.js-metalist-value');
                var $errorTag = $form.find('.alert.danger');
                $errorTag.html('');

                if(group === 'videos'){
                    if($.trim($form.find('input.js-metalist-title').val()) === ''){
                        $errorTag.html(labels['insertVideoTitle']).show();
                        return false;
                    }

                    var parsedURL = purl($linkField.val());
                    if (parsedURL.attr('host').indexOf('youtube') === -1 && parsedURL.attr('host').indexOf('vimeo')  === -1){
                        $errorTag.html(labels['insertVideoUrl']).show();

                        return false;
                    }
                }else if (group === 'links'){

                    if($.trim($form.find('input.js-metalist-title').val()) === ''){
                        $errorTag.html(labels['insertLinkTitle']).show();
                        return false;
                    }

                    var pattern = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
                    if (!pattern.test($linkField.val())){
                        $errorTag.html(labels['insertLinkUrl']).show();
                        return false;
                    }
                }
            },
            success: function (response, statusText, xhr, $form)  {
                if(response.error){
                    return;
                }

                var $target = $($form.data('response-target'));
                var group = $form.data('metalist-group');
                var action = $form.data('metalist-action');
                var template = $form.find('script.js-response-template').text();
                var $editBtn;

                var $html = $(Mustache.render(template, response));

                $editBtn = $html.find('.js-open-editbox');
                $editBtn.data('item', response);

                switch(action){

                    case 'edit':
                        $target.replaceWith($html);
                        $target = $html;
                        MapasCulturais.EditBox.initButtons($editBtn);
                        //if this metalist is of videos, update the new displayed item passing the video url
                        if(group === 'videos'){
                            MapasCulturais.Video.getAndSetVideoData(response.value, $target.find('.js-metalist-item-display'), MapasCulturais.Video.setupVideoGalleryItem);
                        }
                        break;

                    default: //append (insert)
                        $target.append($html);
                        MapasCulturais.EditBox.initButtons($editBtn);

                        //if this metalist is of videos, update the new displayed item passing the video url
                        if(group === 'videos'){
                            MapasCulturais.Video.getAndSetVideoData(response.value, $('#video-'+response.id), MapasCulturais.Video.setupVideoGalleryItem);

                            $('#video-player:hidden').show();

                        }

                }
                $form.parents('.js-editbox').find('.mc-cancel').click();
                //$form.get(0).reset();
            },

            // other available options:
            //url:       url         // override for form's 'action' attribute
            //type:      type        // 'get' or 'post', override for form's 'method' attribute
            dataType:  'json'        // 'xml', 'script', or 'json' (expected server response type)
            //clearForm: true        // clear all form fields after successful submit
            //resetForm: true        // reset the form after successful submit

            // $.ajax options can be used here too, for example:
            //timeout:   3000
        });

        // Delete Button
        $('.js-metalist-form .js-metalist-item-delete').on('click', function(){
            if(confirm('Tem Certeza de que deseja excluir este item?')){
                $form = $(this).parent();
                $form.find('input:hidden[name="metalist_action"]').val('delete');
                $form.find('input:submit').click();
                $form.parent().hide();
            }
        });
    },

    updateDialog: function ($caller){
        var $dialog = $($caller.data('target'));
        var $form = $dialog.find('.js-metalist-form');
        var group = $dialog.data('metalist-group');

        var item = $caller.data('item') || {};

        if(typeof item === 'string')
            item = JSON.parse(item);

        $form.data('metalist-action', $caller.data('metalist-action'));
        $form.data('metalist-group', group);

        if($caller.data('metalist-action') === 'edit'){
            if(group === 'videos')
                $dialog.removeClass('mc-top').addClass('mc-bottom');

            $form.find('input.js-metalist-group').attr('name', '').val('');
            $form.attr('action', MapasCulturais.baseURL + 'metalist/single/' + item.id);
        }else{
            if(group === 'videos')
                $dialog.removeClass('mc-bottom').addClass('mc-top');
            $form.find('input.js-metalist-group').attr('name', 'group').val(group);
            $form.attr('action', $dialog.data('action-url'));
        }

        $form.data('response-target', $caller.data('response-target'));

        // define os labels do form
        $form.find('input.js-metalist-title').attr('placeholder', $dialog.data('metalist-title-label'));
        $form.find('input.js-metalist-value').attr('placeholder', $dialog.data('metalist-value-label'));

        // define os valores dos inputs do form

        $form.find('input.js-metalist-title').val(item.title);
        $form.find('input.js-metalist-value').val(item.value);



        var responseTemplate = '';
        //If Edit or insert:
        if($caller.data('metalist-action') === 'edit'){
            responseTemplate = $dialog.data('response-template');
        }else{
            $dialog.find('h2').html($caller.data('dialog-title'));
            responseTemplate = $caller.data('response-template');
        }

        $form.find('script.js-response-template').text(responseTemplate);

        //if this metalist is of videos,changing a video url results in getting its title from its provider's api and set it to its title field
        if(group === 'videos') {
            $form.find('input.js-metalist-value').on('change', function(){
                MapasCulturais.Video.getAndSetVideoData($(this).val(), $form.find('input.js-metalist-title'), MapasCulturais.Video.setTitle);
            });
        }
    }
};


$(function(){
    function concatena_enderco(){
        var nome_logradouro = $('#En_Nome_Logradouro').editable('getValue', true);
        var cep = $('#En_CEP').editable('getValue', true);
        var numero = $('#En_Num').editable('getValue', true);
        var complemento = $('#En_Complemento').editable('getValue', true);
        var bairro = $('#En_Bairro').editable('getValue', true);
        var municipio = $('#En_Municipio').editable('getValue', true);
        var estado = $('#En_Estado').editable('getValue', true);
        if(cep && nome_logradouro && bairro && municipio && estado){
            var endereco = MapasCulturais.buildAddress(nome_logradouro, complemento, bairro, municipio, estado, cep);
            $('#endereco').editable('setValue', endereco);
            $('#endereco').trigger('changeAddress', endereco);
            $('.js-endereco').html(endereco);
        }


    };

    $('#En_Nome_Logradouro, #En_CEP,  #En_Complemento, #En_Bairro, #En_Municipio,  #En_Estado').on('hidden', function(e, params) {
        concatena_enderco();
    });

    $('#En_CEP').on('hidden', function(e, params){
        var cep = $('#En_CEP').editable('getValue', true);
        $.getJSON('/site/address_by_postalcode?postalcode='+cep, function(r){
            if (r.success) {
                $('#En_Nome_Logradouro').editable('setValue', r.streetName != null ? r.streetName : '');
                $('#En_Bairro').editable('setValue', r.neighborhood != null ? r.neighborhood : '');
                $('#En_Municipio').editable('setValue', r.city != null ? r.city.nome : '');
                $('#En_Estado').editable('setValue', r.state != null ? r.state.sigla : '');
                $('[data-edit="location').editable('setValue', [r.lon, r.lat]);
                $(".lat-txt").html(r.lat);
                $(".lon-txt").html(r.lon);
                concatena_enderco();
            }
        });
    });
});

(function ($) {
    "use strict";
    var Color = function (options) {
        this.init('color', options, Color.defaults);
    };
    $.fn.editableutils.inherit(Color, $.fn.editabletypes.abstractinput);
    $.extend(Color.prototype, {
        render: function() {
            this.$input = this.$tpl.find('input');
            this.$input.parent().colorpicker({
                container: this.$tpl,
                inline: true
            })
        },
        autosubmit: function() {
            this.$input.keydown(function (e) {
                if (e.which === 13) {
                    $(this).closest('form').submit();
                }
            });
        }
    });

    Color.defaults = $.extend({}, $.fn.editabletypes.abstractinput.defaults, {
        tpl: '<div class="editable-color"><div><input type="text" value="" class="form-control" style="width:130px !important" /></div></div>'
    });
    $.fn.editabletypes.color = Color;

}(window.jQuery));
