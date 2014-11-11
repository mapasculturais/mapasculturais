MapasCulturais = MapasCulturais || {};
tabIndex = function() { window.tabEnabled = true };
jQuery(function(){
    $.fn.editableform.buttons = '<button type="submit" class="editable-submit">ok</button><button type="button" class="editable-cancel"><span class="icone icon_close"></span></button>';
    $.fn.select2.defaults.separator = '; ';
    $.fn.editabletypes.select2.defaults.viewseparator = '; ';
    MapasCulturais.Editables.init('#editable-entity');
    MapasCulturais.AjaxUploader.init();
    MapasCulturais.MetalistManager.init();


    MapasCulturais.Remove.init();


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

    //Máscaras de telefone
    var masks = ['(00) 00000-0000', '(00) 0000-00009'];

    $('.js-editable').on('shown', function(e, editable) {
        if ($(this).hasClass('js-mask-phone')) {
            editable.input.$input.mask(masks[1], {onKeyPress:
               function(val, e, field, options) {
                   field.mask(val.length > 14 ? masks[0] : masks[1], options) ;
               }
            });
        }
    });

    //Display Default Shortcuts on Editable Buttons and Focus on select2 input
    $('.editable').on('shown', function(e, editable) {
        editable.container.$form.find('.editable-cancel').attr('title', 'Cancelar Alteração (Esc)');
        //textarea display default Ctrl+Enter and Esc shortcuts
        switch (editable.input.type.trim()) {
            case 'text|select':
                editable.container.$form.find('.editable-submit').attr('title', 'Confirmar Alteração (Enter)');
                break;
            case 'textarea':
                editable.container.$form.find('.editable-submit').attr('title', 'Confirmar Alteração (Ctrl+Enter)');
                break;
            case 'select2':
                editable.container.$form.find('.editable-submit').attr('title', 'Confirmar Alteração (Ctrl+Enter)');
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
});

$(window).on('beforeunload', function(){
    if($('.editable-unsaved').length){
        return 'Há alterações não salvas nesta página.';
    }
});

MapasCulturais.Remove = {
    init: function(){
        $('body').on('click','.js-remove-item', function(e){
            e.stopPropagation();
            var $this = $(this);
            MapasCulturais.confirm('Deseja remover este item?', function(){
                var $target = $($this.data('target'));
                var href = $this.data('href');

                $.getJSON(href,function(r){
                    console.log(r);
                    if(r.error){
                        MapasCulturais.Messages.error(r.data);
                    }else{
                        var cb = function(){};
                        if($this.data('remove-callback'))
                            cb = $this.data('remove-callback');
                        $target.remove();
                        if(typeof cb === 'string')
                            eval(cb);
                        else
                            cb();
                    }
                });
            });

            return false;
        });
    }
}

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
        $('#editable-space-status').on('hidden', function(e, reason) {

            if($(this).editable('getValue', true) == '1'){
                $('#editable-space-status').html('<div class="venue-status"><div class="icone icon_lock-open"></div>Publicação livre</div><p class="venue-status-definition">Qualquer pessoa pode criar eventos.</p>');
            }else{
                $('#editable-space-status').html('<div class="venue-status"><div class="icone icon_lock"></div>Publicação restrita</div><p class="venue-status-definition">Requer autorização para criar eventos.</p>');
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
        MapasCulturais.Editables.getEditableElements().each(function(){

            var field_name = $(this).data(MapasCulturais.Editables.dataSelector);

            var input_type;

            if(!entity[field_name])
                return;

            var config = {
                name: field_name,
                type: 'text',
                emptytext: entity[field_name].label,
                placeholder: entity[field_name].label
            };
            
            var select_value = null;

            switch (entity[field_name].type){
                case 'text':
                    config.type = 'textarea';
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
                    config.type = 'date';
                    config.format = 'yyyy-mm-dd';
                    config.viewformat = 'dd/mm/yyyy';
                    config.datepicker = { weekStart: 1, yearRange: "1900:+0" };
                    delete config.placeholder;

                    break;
            }

            $(this).editable(config);
            
            if(config.type === 'select')
                $(this).editable('setValue', $(this).html());

            if($(this).data('notext')){
                $(this).text('');
                var that = this;
                $(this).on('hidden', function(){
                    $(that).text('');
                });
            }
        });

    },


    setButton : function (editableEntitySelector){
        var $submitButton = $($(editableEntitySelector).data('submit-button-selector'));

        //Ctr+S:save
        $(document.body).on('keydown', function(event){
            if(event.ctrlKey && event.keyCode === 83){
                event.preventDefault();
                event.stopPropagation();
                $submitButton.trigger('click');
            }
        });

        $submitButton.click(function(){
            if($submitButton.data('clicked'))
                return false;

            $submitButton.data('clicked', 'sim');

            var action = $(editableEntitySelector).data('action');
            var target;
            if(action != 'create')
                target = MapasCulturais.Editables.baseTarget+'/single/'+$(editableEntitySelector).data('id');
            else
                target = MapasCulturais.Editables.baseTarget;
            
            MapasCulturais.Editables.getEditableElements().add('.js-include-editable').editable('submit', {
                url: target,
                ajaxOptions: {
                    dataType: 'json', //assuming json response
                    type: action == 'create' ? 'post' : 'post',//'put',
                    statusCode: {
                        202: function(response, statusText, r) {
                            var createdRequests = JSON.parse(r.getResponseHeader('CreatedRequests')),
                                typeName = MapasCulturais.entity.getTypeName(),
                                parentName = '';
                            if(createdRequests && createdRequests.indexOf('ChildEntity') >= 0){
                                parentName = $('[data-field-name="parentId"]').text();
                                MapasCulturais.Messages.alert('Sua requisição para fazer deste '+typeName+' filho de <strong>'+parentName+'</strong> foi enviada.');
                            }
                        }
                    }
                },
                success: function(response){
                    if(response.error){
                        var $field = null;
                        var errors = '';
                        var unknow_errors = [];
                        var field_found = false;
                        var firstShown = false;
                        $('.js-response-error').remove();
                        for(var p in response.data){
                            if(MapasCulturais.request.controller === 'event' && p === 'project'){
                                $field = $('.editable[data-field-name="projectId"');
                            }else if(p.substr(0,5) == 'term-'){
                                $field = $('#' + p);
                            }else if(p == 'type'){
                                $field = $('.js-editable-type');
                            }else{
                                $field = $('.js-editable[data-edit="' + p + '"]');
                            }
                            for(var k in response.data[p]){
                                if($field.length){
                                    field_found = true;
                                    var errorHtml = '<span title="'+'Erro: ' + response.data[p][k]+'" class="danger hltip js-response-error" data-hltip-classes="hltip-danger"></span>';
                                    $field.parent().append(errorHtml);
                                }else{
                                    unknow_errors.push(response.data[p][k]);
                                }
                            }
                            if(!firstShown) {
                                firstShown = true;
                                $field.editable('show');
                            }
                            $field.on('save', function(){
                                $(this).parent().find('.danger.hltip').remove();
                            });
                        }

                        if(field_found)
                            MapasCulturais.Messages.error('Corrija os erros indicados abaixo.');

                        if(unknow_errors){
                            for(var i in unknow_errors){
                                MapasCulturais.Messages.error(unknow_errors[i]);
                            }
                        }

                    }else{


                        $('.js-geo-division').each(function(){
                            var r = response[$(this).data('metakey')];
                            $(this).html(r ? r : '');
                        });

                        var $endereco = $('.js-editable[data-edit="endereco"]');
                        if($endereco.length && response['endereco']){
                            $endereco.editable('setValue', response['endereco']);
                            $endereco.trigger('changeAddress', response['endereco']);
                        }

                        MapasCulturais.Messages.success('Edições salvas.');

                        $('.editable-unsaved').
                                css('background-color','').
                                removeClass('editable-unsaved').
                                parent().
                                removeClass('danger');

                        if(action === 'create')
                            location.href = MapasCulturais.Editables.baseTarget+'/edit/'+response.id;
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
                        MapasCulturais.Messages.error('Um erro inesperado aconteceu.');
                    }
                }
            });
        });

    }
};

MapasCulturais.AjaxUploader = {
    resetProgressBar: function(containerSelector, acivate){
        var bar = $(containerSelector).find('.js-ajax-upload-progress .bar');
        var percent = $(containerSelector).find('.js-ajax-upload-progress .percent');
        var percentVal = '0%';
        bar.stop().width(percentVal);
        percent.html(percentVal);
        if(!acivate)
            $(containerSelector).find('.js-ajax-upload-progress .progress').addClass('inactive');
        else
            $(containerSelector).find('.js-ajax-upload-progress .progress').removeClass('inactive');

    },
    animationTime: 100,
    init: function() {
        var bar = $('.js-ajax-upload-progress .bar');
        var percent = $('.js-ajax-upload-progress .percent');
        // bind form using 'ajaxForm'
        $('.js-ajax-upload').ajaxForm({
            //target:        '#output1',   // target element(s) to be updated with server response
            beforeSubmit: function(arr, $form, options) {
                MapasCulturais.AjaxUploader.resetProgressBar($form.parents('.js-editbox'), true);
            },
            uploadProgress: function(event, position, total, percentComplete) {
                var percentVal = percentComplete + '%';
                bar.animate({'width':percentVal});
                percent.html(percentVal);
                console.log('percent',percentComplete);
            },
            success: function (response, statusText, xhr, $form)  {

                var percentVal = '100%';
                bar.width(percentVal);
                percent.html(percentVal);

                if(response.error){
                    MapasCulturais.AjaxUploader.resetProgressBar($form.parents('.js-editbox'), false);
                    var group = $form.data('group');
                    var error_message = typeof response.data == 'string' ? response.data : response.data[group];
                    $form.find('div.alert.danger').html(error_message).fadeIn(this.animationTime).delay(5000).fadeOut(this.animationTime);
                    return;
                }

                var $target = $($form.data('target'));
                var group = $form.find('input:file').attr('name');

                var template = $form.find('script').text();

                switch($form.data('action').toString()){
                    case 'replace':
                        var html = Mustache.render(template, response[group]);
                        $target.replaceWith($(html));
                    break;
                    case 'set-content':

                        var html = Mustache.render(template, response[group]);
                        $target.html(html);
                    break;
                    case 'a-href':
                        try{
                            $target.attr('href', response[group].url);
                        }catch (e){}

                    break;
                    case 'image-src':
                        try{
                            if($form.data('transform'))
                                $target.attr('src', response[group].files[$form.data('transform')].url);
                            else
                                $target.attr('src', response[group].url);
                        }catch (e){}

                    break;
                    case 'background-image':
                        $target.each(function(){
                            try{
                                if($form.data('transform'))
                                    $(this).css('background-image', 'url(' + response[group].files[$form.data('transform')].url + ')');
                                else
                                    $(this).css('background-image', 'url(' + response[group].url + ')');
                            }catch (e){}
                        });
                    break;

                    case 'append':
                        for(var i in response[group]){

                            if(!response[group][i].description)
                               response[group][i].description = response[group][i].name;

                           var html = Mustache.render(template, response[group][i]);
                           $target.append(html);
                       }
                    break;

                }

                $form.get(0).reset();
                if($form.parents('.js-editbox').data('success-callback'))
                    eval($form.parents('.js-editbox').data('success-callback'));

                $form.parents('.js-editbox').find('.mc-cancel').click();
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
    }
};

MapasCulturais.MetalistManager = {
    init : function() {
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
                        $errorTag.html('Insira um título para seu vídeo.').show();
                        return false;
                    }

                    var parsedURL = purl($linkField.val());
                    if (parsedURL.attr('host').indexOf('youtube') === -1 && parsedURL.attr('host').indexOf('vimeo')  === -1){
                        $errorTag.html('Insira uma url de um vídeo do YouTube ou do Vimeo.').show();

                        return false;
                    }
                }else if (group === 'links'){

                    if($.trim($form.find('input.js-metalist-title').val()) === ''){
                        $errorTag.html('Insira um título para seu link.').show();
                        return false;
                    }

                    var pattern = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
                    if (!pattern.test($linkField.val())){
                        $errorTag.html('A url do link é inválida, insira uma url completa como http://www.google.com/.').show();
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
