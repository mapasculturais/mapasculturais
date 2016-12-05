MapasCulturais = MapasCulturais || {};

function charCounter(obj){
    if($('#charCounter').text() == '')
        return;

    var max = $('#charCounter').text().split('/');
        $('#charCounter').text(($(obj).val().length + '/' + max[1]));
}

$(function(){
//    $.fn.select2.defaults.separator = '; ';
//    $.fn.editabletypes.select2.defaults.viewseparator = '; ';
    
    var labels = MapasCulturais.gettext.mapas;
    
    MapasCulturais.TemplateManager.init();
    MapasCulturais.Modal.initKeyboard('.js-dialog');
    MapasCulturais.Modal.initDialogs('.js-dialog');
    MapasCulturais.Modal.initButtons('.js-open-dialog');

    MapasCulturais.EditBox.initBoxes('.js-editbox');
    MapasCulturais.EditBox.initButtons('.js-open-editbox');


    MapasCulturais.Video.setupVideoGallery('.js-videogallery');
    MapasCulturais.Search.init(".js-search");

    // bind alert close buttons
    $('.alert .close').click(function(){
        $(this).parent().slideUp('fast');
    }).css('cursor', 'pointer');

    if(MapasCulturais.request.controller === 'app'){
        MapasCulturais.App.init();
    }

    // dropdown

    if(MapasCulturais.request.controller === 'project') {
        var $els = $('#tab-inscricoes,#tab-inscritos').parent();
        if(!MapasCulturais.entity.useRegistrations){
            $els.hide();
        }
        $('#editable-use-registrations').on('hidden', function(e, reason) {
            if($(this).editable('getValue', true) == '1'){
                $els.fadeIn('fast');
            }else{
                $els.fadeOut('fast');
            }
        });
    }

    //main nav submenus toggle on click
    $('body').on('click', '.js-submenu-toggle', function(){
        var $self = $(this),
            $target = eval($self.data('submenu-target'));
        //hides by clicking outside
        $('body').one('click', function(event){
            if($self.find(event.target).length == 0){
                $target.fadeOut(0);
            }
        });
        $target.fadeToggle(100);
    });

    $('body').on('click', '.dropdown .placeholder', function(){
        var $dropdown = $(this).parents('.dropdown'),
            $submenu = $dropdown.find('.submenu-dropdown');

        if($submenu.is(':visible')){
            $submenu.hide();
        }else{
            $submenu.show();
        }

        if(!$dropdown.data('init')){
            $dropdown.data('init', true);

            if($dropdown.data('closeonclick')){
                $submenu.click(function(){
                    $submenu.hide();
                });
            }
            //mouse leave disabled
            // var $timeout;
            // $submenu.on('mouseleave', function(){
            //     $timeout = setTimeout(function(){
            //         $submenu.hide();
            //     }, 100);
            // });
            // $submenu.on('mouseenter', function(){
            //     clearTimeout($timeout);
            // });
            // $dropdown.on('mouseenter', function(){
            //     clearTimeout($timeout);
            // });
            $('body').on('click', function(event){
                if($submenu.find(event.target).length == 0 && $dropdown.find(event.target).length == 0){
                    $submenu.hide();
                }
            });
        }
    });

     if($('#funcao-do-agente').length){
        $('#funcao-do-agente .js-options li').click(function(){
            var roleToRemove = $('#funcao-do-agente .js-selected span').data('role');
            var roleToAdd = $(this).data('role');
            var label = $(this).find('span').html();

            var change = function(){
                $('#funcao-do-agente .js-selected span').html(label);
                $('#funcao-do-agente .js-selected span').data('role', roleToAdd);
            };

            if(roleToRemove)
                $.post(MapasCulturais.baseURL + 'agent/removeRole/' + MapasCulturais.entity.id, {role: roleToRemove}, function(r){ if(r && !r.error) change(); });

            if(roleToAdd)
                $.post(MapasCulturais.baseURL + 'agent/addRole/' + MapasCulturais.entity.id, {role: roleToAdd}, function(r){ if(r && !r.error) change(); });


        });
    }

    MapasCulturais.spinnerURL = MapasCulturais.assetURL + '/img/spinner.gif';


    // identify Internet Explorer
    if(navigator.appName != 'Microsoft Internet Explorer' && !(navigator.appName == 'Netscape' && navigator.userAgent.indexOf('Trident') !== -1)){
        //not IE
    }else{
        // is IE
        $('body').addClass('ie');
        //identify version
        var ua = navigator.userAgent.toLowerCase();
        if(!isNaN(version = parseInt(ua.split('msie')[1]))){ // 7, 8, 9, 10
            $('body').addClass('ie'+version);
        }else if( parseInt(ua.split('rv:')[1]) === 11) { // 11
            $('body').addClass('ie11');
        }
    }

    if(MapasCulturais.entity){
        MapasCulturais.entity.getTypeName = function(){
            switch(MapasCulturais.request.controller){
                case 'agent' : return labels['agente']; break;
                case 'space' : return labels['espaço']; break;
                case 'event' : return labels['evento']; break;
                case 'project' : return labels['projeto']; break;
                case 'seal' : return labels['selo']; break;
            }
        };
    }


    // confirm

    $('a.js-confirm-before-go').click(function() {
        if (!confirm($(this).data('confirm-text')))
            return false;
    });



    // confirm

    $('a.js-confirm-before-go').click(function() {
        if (!confirm($(this).data('confirm-text')))
            return false;
    });


    // positioning agent details box on mobile

    if ($(window).width() < 768) {
        $('.agentes-relacionados .avatar').on('click hover', function () {
            $('.descricao-do-agente').hide();

            var descAgent = $(this).find('.descricao-do-agente');
            var descAgentHeight = descAgent.outerHeight();
            descAgent.show().css('top',-((descAgentHeight)+10));
        });
    }
});

MapasCulturais.utils = {
    getObjectProperties: function (obj) {
        var keys = [];
        for (var key in obj) {
            keys.push(key);
        }
        return keys;
    },
    sortOjectProperties: function(obj){
        var newObj = {};

        this.getObjectProperties(obj).sort().forEach(function(e){
            newObj[e] = obj[e];
        });

        return newObj;
    },

    isObjectEquals: function(obj1, obj2){
//        console.log(JSON.stringify(this.sortOjectProperties(obj1)), JSON.stringify(this.sortOjectProperties(obj2)));
        return JSON.stringify(this.sortOjectProperties(obj1)) === JSON.stringify(this.sortOjectProperties(obj2));
    },

    inArray: function(array, obj){
        for(var i in array){
            if(this.isObjectEquals(array[i], obj)){
                return true;
            }
        }
        return false;
    },

    arraySearch: function(array, obj){
        for(var i in array){
            if(this.isObjectEquals(array[i], obj)){
                return i;
            }
        }
        return false;
    }
};

MapasCulturais.createUrl = function(controller_id, action_name, args){
    var shortcuts = this.routes.shortcuts,
        actions = this.routes.actions,
        controllers = this.routes.controllers,

        u = MapasCulturais.utils,
        route = '';

    action_name = action_name || this.routes.default_action_name;

    if(args){
        args = u.sortOjectProperties(args);
    }

    if(args && u.inArray(shortcuts, [controller_id, action_name, args])){
        route = u.arraySearch(shortcuts, [controller_id, action_name, args]) + '/';
        args = null;
    }else if(u.inArray(shortcuts, [controller_id, action_name])){
        route = u.arraySearch(shortcuts, [controller_id, action_name]) + '/';
    }else{
        if(u.inArray(controllers, controller_id)){
            route = u.arraySearch(controllers, controller_id) + '/';
        }else{
            route = controller_id + '/';
        }

        if(action_name !== this.routes.default_action_name){
            if(u.inArray(actions, action_name)){
                route += u.arraySearch(actions, action_name) + '/';
            }else{
                route += action_name + '/';
            }
        }
    }

    if(args){
        for(var key in args){
            var val = args[key];
            if(key == parseInt(key)){ // is integer
                route += val + '/';
            }else{
                route += key + ':' + val + '/';
            }
        }
    }

    return MapasCulturais.baseURL + route;
};

MapasCulturais.auth = {
    cb: null,
    require: function(cb){
        MapasCulturais.auth.cb = cb;
            $('#require-authentication').attr('src',MapasCulturais.baseURL + 'panel/requireAuth').fadeIn();
    },

    finish: function(){
        if(MapasCulturais.auth.cb){
            MapasCulturais.auth.cb();
        }

        MapasCulturais.auth.cb = null;
        $('#require-authentication').fadeOut();
    }
}

MapasCulturais.TemplateManager = {
    templates: {},
    init: function(){
        var $templates = $('.js-mustache-template');
        var $this = this;
        $templates.each(function(){
            $this.templates[$(this).attr('id')] = $(this).text();
            $(this).remove();
        });
    },

    getTemplate: function(id){
        if(this.templates[id])
            return this.templates[id];
        else
            return null;
    }
};

MapasCulturais.App = {
    init: function(){
        $('.js-select-on-click').on('click', function(){
            var selector = $(this).data('selectTarget');
            $(selector).trigger('doubleClick');
        });

        if($('.js-input--app-key').length && $('.js-input--app-key--toggle').length){
            $('.js-input--app-key--toggle').on('click', function(){
                if($('.js-input--app-key').attr('type') === 'password'){
                    $('.js-input--app-key').attr('type', 'text');
                }else{
                    $('.js-input--app-key').attr('type', 'password');
                }

                return false;
            });
        }
    }
};

MapasCulturais.defaultAvatarURL = MapasCulturais.assets.avatarAgent;

function editableEntityAddHash(){
    $('#editable-entity').find('.js-toggle-edit').each(function(){
        var href = $(this).attr('href'),
            cleanHref = href.indexOf('#') !== -1 ? href.split('#')[0] : href;
        $(this).attr('href', cleanHref+location.hash);
    });
}
jQuery(document).ready(function(){
    editableEntityAddHash();
    $(window).on('hashchange', function(){
        editableEntityAddHash();
    });
});


MapasCulturais.Messages = {
    delayToFadeOut: 5000,
    fadeOutSpeed: 'slow',
    showMessage: function(type, message) {
        var $container = $('#editable-entity');
        var $message = $('<div class="alert ' + type + '">"').html(message);
        var $mainSection = $('#main-section');
        var delayToFadeOut = this.delayToFadeOut;
        $container.append($message);

        if($container.hasClass('js-not-editable')){
            $container.slideDown('fast');
            $mainSection.animate({marginTop: parseInt($mainSection.css('margin-top')) + 42}, 'fast', function(){
                $message.css('display', 'inline-block').css('display', 'inline-block').delay(delayToFadeOut).fadeOut(this.fadeOutSpeed, function() {
                    $(this).remove();
                    if($container.find('>').length === 0){
                        $container.slideUp('fast');
                        $mainSection.animate({marginTop: parseInt($mainSection.css('margin-top')) - 42}, 'fast');
                    }
                });
            });
        }else{
            $message.css('display', 'inline-block').css('display', 'inline-block').delay(delayToFadeOut).fadeOut(this.fadeOutSpeed, function() {
                $(this).remove();
            });
        }

    },
    success: function(message) {
        this.showMessage('success', message);
    },
    error: function(message) {
        this.showMessage('danger', message);
    },
    help: function(message) {
        this.showMessage('info', message);
    },
    alert: function(message) {
        this.showMessage('warning', message);
    }

}

MapasCulturais.confirm = function (message, cb){
    if(confirm(message))
        cb();
};

MapasCulturais.Modal = {
    time: 'fast',
    initKeyboard: function (selector){
        $(document.body).keyup(function (e){
            if(e.keyCode == 27){
                $(selector).each(function(){
                   if($(this).is(':visible')) {
                       $(this).find('.js-close').click();
                   }
                });
            }
        });
    },

    initDialogs: function(selector){
        $(selector).each(function(){
            $('body').append($(this));
            if($(this).find('.js-dialog-disabled').length)
                return;

            if($(this).data('dialog-init'))
                return;

            var $dialog = $(this);
            /*$dialog.hide();  Moved to style.css */

            $dialog.data('dialog-init', 1);
            $dialog.prepend('<h2>' + $(this).attr('title') + '</h2>');
            $dialog.prepend('<a href="#" class="js-close icon icon-close"></a>');

            // close button
            $dialog.find('.js-close').click(function (){
                MapasCulturais.Modal.close(selector);
                return false;
            });
        });
    },

    initButtons: function(selector){

        if($(selector).data('button-initialized')) return false;
        else $(selector).data('button-initialized', true);
        $(selector).each(function(){
            var dialog_selector = $(this).data('dialog');
            var $dialog = $(dialog_selector);
            if($dialog.find('.js-dialog-disabled').length)
                $(this).addClass('inactive').addClass('hltip').attr('title', $dialog.find('.js-dialog-disabled').data('message'));

        });
        $(selector).click(function(){
            if($(this).hasClass('inactive'))
                return false;

            var dialog_selector = $(this).data('dialog');
            MapasCulturais.Modal.open(dialog_selector);
            if( $(this).data('dialog-callback') )
                eval( $(this).data('dialog-callback'))($(this));
            return false;
        });
    },

    close: function(selector){
        $('body').css('overflow','auto');
        var $dialog = $(selector);
        //alert('closing');
        $dialog.find('.editable').editable('hide');
        $dialog.hide();
        return;
    },

    open: function(selector){
        var $dialog = $(selector);

        $dialog.find('div.alert.danger').html('').hide();
        $dialog.find('.js-ajax-upload-progress').hide();
        $dialog.css('opacity',0).show();
        setTimeout(function(){
           var top = $dialog.height() + 100 > $(window).height() ? $(window).scrollTop() + 100 : $(window).scrollTop() + ( $(window).height() - $dialog.height()) / 2 - 50;

           $dialog.css({
                top: top,
                left: '50%',
                marginLeft: -$dialog.width() / 2,
                opacity: 1
            });
        },25);


        return;
    }
};


MapasCulturais.EditBox = {
    time: 'fast',

    setPosition: function($box, target){
        if($box.hasClass('mc-left')){
            $box.position({
                my: 'right-10 center',
                at: 'left center',
                of: target
            });

        }else if($box.hasClass('mc-right')){
            $box.position({
                my: 'left+10 center',
                at: 'right center',
                of: target
            });

        }else if($box.hasClass('mc-top')){
            $box.position({
                my: 'center bottom-10',
                at: 'center top',
                of: target
            });

        }else if($box.hasClass('mc-bottom')){
            $box.position({
                my: 'center top+10',
                at: 'center bottom',
                of: target
            });
        }
    },

    initKeyboard: function (selector){
        $(document.body).keyup(function (e){
            if(e.keyCode == 27){
                $(selector).each(function(){
                   if($(this).is(':visible')) {
                       $(this).find('.js-close').click();
                   }
                });
            }
        });
    },

    initBoxes: function(selector){
        
        var labels = MapasCulturais.gettext.mapas;

        $(selector).each(function(){
            var $dialog = $(this);

            if($dialog.find('.js-dialog-disabled').length)
                return;

            if($dialog.data('dialog-init'))
                return;

            if($dialog.data('init'))
                return;

            $dialog.data('init', true);

            /*$dialog.hide();  Moved to style.css */

            $dialog.addClass('edit-box');

            $dialog.data('dialog-init', 1);
            if($dialog.attr('title')){
                $dialog.prepend('<header><h1>' + $(this).attr('title') + '</h1></header>');
            }
            var submit_label = $dialog.data('submit-label') ? $dialog.data('submit-label') : labels['Enviar'];
            var cancel_label = $dialog.data('cancel-label') ? $dialog.data('cancel-label') : labels['Cancelar'];

            $dialog.append('<footer><button class="mc-cancel btn btn-default">' + cancel_label + '</button> <button type="submit" class="mc-submit">' + submit_label + '</button> </footer><div class="mc-arrow"></div>');

            // close button
            $dialog.find('.mc-cancel').click(function (){
                MapasCulturais.EditBox.close($dialog);
                return false;
            });

            // submit form
            $dialog.find('footer button.mc-submit').click(function(){
                $dialog.find('form').submit();
            });
        });
    },

    initButtons: function(selector){
        $(selector).each(function(){
            var $button = $(this);
            var dialog_selector = $(this).data('target');
            var $dialog = $(dialog_selector);
            if($dialog.find('.js-dialog-disabled').length)
                $(this).addClass('inactive').addClass('hltip').attr('title', $dialog.find('.js-dialog-disabled').data('message'));

        });
        $(selector).click(function(){
            var $button = $(this);
            if($button.hasClass('inactive'))
                return false;

            var dialog_selector = $button.data('target');

            MapasCulturais.EditBox.open(dialog_selector, $button);

            if($button.data('dialog-title'))
                $(dialog_selector).find('header h1').html($button.data('dialog-title'));

            if( $button.data('dialog-callback') )
                eval( $button.data('dialog-callback'))($button);

            return false;
        });
    },

    close: function(selector){
        $('body').css('overflow','auto');
        var $dialog = $(selector);
        //alert('closing');
        $dialog.find('.editable').editable('hide');
        $dialog.hide();
        return;
    },

    open: function(selector, $button){
        var $dialog = $(selector);
        $dialog.find('div.alert.danger').html('').hide();

        MapasCulturais.AjaxUploader.resetProgressBar(selector);

        $dialog.show();
        $dialog.find('input,textarea').not(':hidden').first().focus();
        $dialog.css('opacity',0);
        MapasCulturais.EditBox.setPosition($dialog, $button);
        setTimeout(function(){
            MapasCulturais.EditBox.setPosition($dialog, $button);
            $dialog.css('opacity',1);
        },25);

        return;
    }
};


MapasCulturais.Video = {
    collection : {},
    parseURL : function(url){
        return purl(url);
    },
    isYoutube : function(parsedURL){
            return parsedURL.attr('host').indexOf('youtube') != -1;
    },
    getYoutubeData : function(youtubeVideoID){
        return {
            thumbnalURL : 'http://img.youtube.com/vi/'+videoID+'/0.jpg',
            playerURL : '//www.youtube.com/embed/'+videoID+'?html5=1'
        }
    },
    getVideoBasicData : function(url){
        var parsedURL = this.parseURL(url);
        var host = parsedURL.attr('host');
        var provider = '';
        var videoID = '';
        if(parsedURL.attr('host').indexOf('youtube') != -1 ){
            provider = 'youtube';
            videoID = parsedURL.param('v');
        }else if(parsedURL.attr('host').indexOf('vimeo') != -1){
            provider = 'vimeo';
            videoID = parsedURL.attr('path').split('/')[1];
        }
        return {
            'parsedURL' : parsedURL,
            'provider' : provider,
            'videoID' : videoID
        }
    },
    setupVideoGallery : function(gallerySelector){

        if($(gallerySelector).length == 0)
            return false;

        $( gallerySelector + ' .js-metalist-item-display').each(function(){
            MapasCulturais.Video.getAndSetVideoData($(this).data('videolink'), $(this).parent(), MapasCulturais.Video.setupVideoGalleryItem);
        });
        var $firstItem = $( gallerySelector + ' .js-metalist-item-display:first').parent();
        if(!$firstItem.length) {
            $('#video-player').hide();
            return false;
        }
        $('iframe#video_display').attr('src', $firstItem.data('player-url'));
        $firstItem.addClass('active');
    },
    setupVideoGalleryItem : function(videoData, $element){
        //$Element should be a.js-metalist-item-display
        $element.attr('href', '#video');
        $element.data('player-url', videoData.playerURL);
        $element.find('img').attr('src', videoData.thumbnailURL);

        var video_id = $element.attr('id');

        $element.on('click', function(){
            $('iframe#video_display').attr('src', videoData.playerURL);
            $('iframe#video_display').data('open-video', $(this).attr('id'));
            $(this).parent().find('.active').removeClass('active');
            $(this).addClass('active');
        });

        var $container = $element.parent();
        if($element.find('.js-remove-item').length){
            $element.find('.js-remove-item').data('remove-callback', function(){
                if($('iframe#video_display').data('open-video') == video_id)
                    $('iframe#video_display').attr('src', '');

                if($container.find('>li').length == 0)
                    $('#video-player').hide();
            });
        }

        if($container.find('>li').length == 1){
            $element.addClass('active');
            $('iframe#video_display').attr('src', videoData.playerURL);
        }

    },
    setTitle : function(videoData, $element){
        $element.val(videoData.videoTitle);
    },
    getAndSetVideoData : function (videoURL, $element, callback){
        videoURL = videoURL.trim();
        var videoData = {
            parsedURL : purl(videoURL),
            provider : '',
            videoID : '',
            thumbnailURL : '',
            playerURL : '',
            details : {}
        };

        if(videoData.parsedURL.attr('host').indexOf('youtube') != -1){
            videoData.provider = 'youtube';
            videoData.videoID = videoData.parsedURL.param('v');
            videoData.thumbnailURL = 'http://img.youtube.com/vi/'+videoData.videoID+'/0.jpg';
            videoData.playerURL = '//www.youtube.com/embed/'+videoData.videoID+'?html5=1';
            callback(videoData, $element);
            $.getJSON('https://gdata.youtube.com/feeds/api/videos/'+videoData.videoID+'?v=2&alt=json', function(data) {
                videoData.details = data;
                videoData.videoTitle = data.entry.title.$t;
                MapasCulturais.Video.collection[videoURL] = videoData;
                callback(videoData, $element);
                return videoData;
            });
        }else if(videoData.parsedURL.attr('host').indexOf('vimeo') != -1){
            videoData.provider = 'vimeo';
            var tmpArray = videoData.parsedURL.attr('path').split('/');
            videoData.videoID = tmpArray[tmpArray.length-1];
            $.getJSON('http://www.vimeo.com/api/v2/video/'+videoData.videoID+'.json?callback=?', {format: "json"}, function(data) {
                videoData.details = data[0];
                videoData.thumbnailURL = data[0].thumbnail_small;
                videoData.playerURL = '//player.vimeo.com/video/'+videoData.videoID+'';
                videoData.videoTitle = data[0].title;
                callback(videoData, $element);
                MapasCulturais.Video.collection[videoURL] = videoData;
                return videoData;
            });
        }else{
            //no valid provider
            videoData.thumbnailURL = 'http://www.bizreport.com/images/shutterstock/2013/04/onlinevideo_135877229-thumb-380xauto-2057.jpg';
            callback(videoData, $element);
            return videoData;
        }

        var withDetails = function(){
            // get details from youtube api
            if(!videoData.details) {
                $.getJSON('https://gdata.youtube.com/feeds/api/videos/'+videoData.videoID+'?v=2&alt=json', {format: "json"}, function(data) {
                    videoData.details = data;
                    functionName(videoData, $element);
                    return videoData;
                });
            }
        };
    }
};

MapasCulturais.Search = {
    limit: 10,

    init : function(selector){
        if( $(selector).length === 0 || $(selector).hasClass('select2-offscreen')) return false;

        $(selector).each(function(){
            var $selector = $(this);

            $selector.editable({
                type:'select2',
                name: $selector.data('field-name') ? $selector.data('field-name') : null,
                select2:{
//                    multiple: $selector.data('multiple'),
//                    tokenSeparators: [";",";"],
//                    separator:'; ',
//                    viewseparator: '; ',
                    width: $selector.data('search-box-width'),
                    placeholder: $selector.data('search-box-placeholder'),
                    minimumInputLength: 0,
                    allowClear: $selector.data('allow-clear'),
                    initSelection: function(e,cb){
                        cb({id: $selector.data('value'), name: $selector.data('editable').$element.html()});
                    },
                    ajax: {
                        url: function(){
                            var format = $selector.data('selection-format');
                            var apiMethod = 'find';
                            if(MapasCulturais.Search.formats[format] && MapasCulturais.Search.formats[format].apiMethod)
                                apiMethod =  MapasCulturais.Search.formats[format].apiMethod;
                            return MapasCulturais.baseURL + 'api/' + $selector.data('entity-controller') + '/' + apiMethod;
                        },
                        dataType: 'json',
                        quietMillis: 350,
                        data: function (term, page) { // page is the one-based page number tracked by Select2
                            var searchParams = MapasCulturais.Search.getEntityController(term, page, $selector);

                            var format = $selector.data('selection-format');

                            if(MapasCulturais.Search.formats[format] && MapasCulturais.Search.formats[format].ajaxData)
                                searchParams = MapasCulturais.Search.formats[format].ajaxData(searchParams, $selector);
                            else
                                searchParams = MapasCulturais.Search.ajaxData(searchParams, $selector);

                            return searchParams;
                        },
                        results: function (data, page) {
                            var more = data.length == MapasCulturais.Search.limit;
                            // notice we return the value of more so Select2 knows if more results can be loaded

                            return {results: data, more: more};
                        }
                    },
                    formatResult: function(entity){
                        var format = $selector.data('selection-format');
                        if(MapasCulturais.Search.formats[format] && MapasCulturais.Search.formats[format].result)
                            return MapasCulturais.Search.formats[format].result(entity, $selector);
                        else
                            return MapasCulturais.Search.formatResult(entity, $selector);
                    }, // omitted for brevity, see the source of this page

                    formatSelection: function(entity){
                        var format = $selector.data('selection-format');
                        return MapasCulturais.Search.formats[format].selection(entity, $selector);
                    }, // omitted for brevity, see the source of this page

                    formatNoMatches: function(term){
                        var format = $selector.data('selection-format');
                        return MapasCulturais.Search.formats[format].noMatches(term, $selector);
                    },
                    escapeMarkup: function (m) { return m; }, // we do not want to escape markup since we are displaying html in results
                }
            });
            if($selector.data('disable-buttons') ){
                $selector.on('shown', function(e, editable) {
                    var format = $selector.data('selection-format');

                    /*Hide editable buttons because the option showbuttons:false autosubmitts the form adding two agents...
                    Also Auto-open select2 on editable.shown and auto-close editable popup on select2-close*/

                    e.stopPropagation();
                    if (arguments.length == 2) {
                        setTimeout(function() {
                            editable.input.$input.parents('.control-group').addClass('editable-popup-botoes-escondidos');
                        }, 0);
                        setTimeout(function() {
                            editable.input.$input.select2("open");
                        }, 200);
                        editable.input.$input.on('select2-close', function(){
                            setTimeout(function() {
                                $selector.editable('hide');
                            }, 200);
                        });
                    }
                });
            }
            if($selector.data('auto-open') ){
                $selector.on('shown', function(e, editable) {
                    setTimeout(function() {
                        editable.input.$input.select2("open");
                    }, 200);
                });
            }
            var format = $selector.data('selection-format');
            $selector.on('save',function(){
                try{
                    MapasCulturais.Search.formats[format].onSave($selector);
                }catch(e){
                    console.log("EXECEPTION: ", e.message);
                }
            });
            $selector.on('hidden',function(){
                try{
                    MapasCulturais.Search.formats[format].onHidden($selector);
                }catch(e){
                    console.log("EXECEPTION: ", e.message);
                }
            });

        });
    },



    getEntityController : function(term, page, $selector){

        var entitiyControllers = {
            'default':{
                name: 'ilike(*'+term.replace(' ', '*')+'*)', //search term
                '@select': 'id,name,terms,type',
                '@limit': MapasCulturais.Search.limit, // page size
                '@page': page,
                '@order':'name ASC', // page number
                '@files':'(avatar.avatarSmall):url'
            },
            'agent':{ //apenas adicionei a shortDescription
                name: 'ilike(*'+term.replace(' ', '*')+'*)', //search term,
                '@select': 'id,name,terms,type',
                '@limit': MapasCulturais.Search.limit, // page size
                '@page': page,
                '@order':'name ASC', // page number
                '@files':'(avatar.avatarSmall):url'
            }
        };

        if(entitiyControllers[$selector.data('entity-controller')]){
            return entitiyControllers[$selector.data('entity-controller')];
        }else{
            return entitiyControllers['default'];
        }
    },

    processEntity: function(entity, $selector){
        entity.areas = function(){
            if(this.terms && this.terms.area)
                return this.terms.area.join(', ');
            else
                return '';
        };

        entity.linguagens = function(){
            if(this.terms && this.terms.linguagem)
                return this.terms.linguagem.join(', ');
            else
                return '';
        };

        entity.tags = function(){
            if(this.terms && this.terms.tags)
                return this.terms.tags.join(', ');
            else
                return '';
        };

        entity.thumbnail = function(){
            var entityDefaultAvatar = MapasCulturais.assets['avatar'+$selector.data('entity-controller')[0].toUpperCase() + $selector.data('entity-controller').slice(1)];
            if(this['@files:avatar.avatarSmall'])
                return this['@files:avatar.avatarSmall'].url;
            else
                return entityDefaultAvatar;
        };

    },

    renderTemplate : function(template, entity, $selector){
        this.processEntity(entity, $selector);
        return Mustache.render(template, entity);
    },

    getEntityThumbnail : function (entity, thumbName){
        if(!entity.files || entity.files.length == 0 || !entity.files[thumbName] || entity.files[thumbName].length == 0)
            return '';
        else{
            if(entity.files[thumbName].files['avatarSmall'])
                return entity.files[thumbName].files['avatarSmall'].url;
        }
    },

    formatResult : function (entity, $selector) {
        var searchResultTemplate = $($selector.data('search-result-template')).text();
        return this.renderTemplate(searchResultTemplate, entity, $selector);
    },

    ajaxData: function(searchParams, $selector){
        var excludedIds = MapasCulturais.request.controller === $selector.data('entity-controller') && MapasCulturais.entity.id ? [MapasCulturais.entity.id] : [];

        if(excludedIds.length > 0)
            searchParams.id = '!in(' + excludedIds.toString() + ')';

        return searchParams;
    },

    formats: {
        chooseProject:{
            //apiMethod : 'findByUserApprovedRegistration',
            onSave: function($selector){
                var entity = $selector.data('entity');
                $selector.data('value', entity.id);
                $selector.data('value-name', entity.name);
            },
            selection: function(entity, $selector){
                $selector.data('entity', entity);
                return entity.name;
            },

            noMatches: function(term, $selector){
                return 'Nenhum projeto encontrado.';
            },

            onClear: function($selector){
            },

            ajaxData: function(searchParams){
                searchParams['@permissions'] = 'requestEventRelation';
                return searchParams;
            }
        },

        chooseSpace: {
            onSave: function($selector){
                var entity = $selector.data('entity'),
                    avatarUrl = MapasCulturais.defaultAvatarURL,
                    shortDescription = entity.shortDescription ? entity.shortDescription.replace("\n",'<br/>') : '';

                $selector.data('value', entity.id);

                try{
                    avatarUrl = entity.files.avatar.files.avatarSmall.url;
                }catch(e){};

                $selector.parents('.js-space').find('.js-space-avatar').attr('src', avatarUrl);
                $selector.parents('.js-space').find('.js-space-description').html(shortDescription);

                $selector.parents('form').find('input[name="spaceId"]').val(entity.id);
            },
            onHidden : function ($selector) {
                $selector.removeClass('editable-unsaved');
            },

            selection: function(entity, $selector){
                $selector.data('entity', entity);
                return entity.name;
            },

            noMatches: function(term, $selector){
                return 'Nenhum espaço encontrado.';
            },

            onClear: function($selector){ },

            ajaxData: function(searchParams, $selector){

                if($selector.data('value')){
                    searchParams.id = '!in('+$selector.data('value')+')';
                }

                //if(!MapasCulturais.cookies.get('mapasculturais.adm'))
                //    searchParams.owner = 'in(@me.spaces)';

                searchParams['@select'] += ',shortDescription';
//                searchParams['@permissions'] += '@control';
                return searchParams;
            }
        },

        createAgentRelation: {
            selection: function (entity, $selector) {
                var $selectionTarget =   $($selector.data('selection-target'));
                var targetAction =      $selector.data('target-action');
                var selectionTemplate = $($selector.data('selection-template')).text();

                var groupname = $selector.parents('.js-related-group').find('.js-related-group-name').text();

                $.post(
                    MapasCulturais.baseURL + MapasCulturais.request.controller + '/createAgentRelation/id:' + MapasCulturais.entity.id,
                    {agentId: entity.id, group:groupname, has_control: '0'}
                );


                if(targetAction == 'append'){
                    MapasCulturais.RelatedAgents.addAgentToGroup(groupname, entity);
                    return $selector.data('search-box-placeholder');
                }else{
                    $selectionTarget.html(markup);
                    return entity.name;
                }
            },

            noMatches: function (term, $selector){
                var noResultTemplate = $($selector.data('no-result-template')).text();
                $('#dialog-adicionar-integrante').data('group-element', $selector.parents('.js-related-group').find('.js-relatedAgentsContainer'));
                //return term + ' HAHAHA!';
                return noResultTemplate.replace('{{group}}', $selector.parents('.js-related-group').find('.js-related-group-name').text());
            },

            ajaxData: function(searchParams, $selector){
                var group = $selector.parents('.js-related-group').find('.js-related-group-name').text();

                var excludedIds = MapasCulturais.request.controller === 'agent' && MapasCulturais.entity.id? [MapasCulturais.entity.id] : [];
                try{
                    if(MapasCulturais.agentRelationGroupExludeIds[group])
                        excludedIds = excludedIds.concat(MapasCulturais.agentRelationGroupExludeIds[group]);
                }catch(e){}

                excludedIds = excludedIds.concat (
                    $selector.parents('.js-related-group').find('.agentes .avatar .selos').map(
                        function(){ return $(this).data('id'); }
                ).toArray());


                if ( excludedIds.length > 0)
                    searchParams.id = '!in('+excludedIds.toString()+')';

                return searchParams;
            }
        },

        parentSpace:{
            selection: function(entity, $selector){
                return entity.name;
            },

            noMatches: function(term, $selector){
                return 'Nenhum espaço encontrado.';
            },

            onClear: function($selector){
            },

            ajaxData: function(searchParams, $selector){
                if(MapasCulturais.entity.id){
                    var excludeIds = [MapasCulturais.entity.id].concat(MapasCulturais.entity.childrenIds);
                    if(excludeIds){
                        searchParams['id'] = '!IN(' + excludeIds + ')';
                    }

                }
                return searchParams;
            }
        },

        parentProject:{
            selection: function(entity, $selector){
                return entity.name;
            },

            noMatches: function(term, $selector){
                return 'Nenhum projeto encontrado.';
            },

            onClear: function($selector){
            },

            ajaxData: function(searchParams, $selector){
                if(MapasCulturais.entity.id){
                    var excludeIds = [MapasCulturais.entity.id].concat(MapasCulturais.entity.childrenIds);
                    if(excludeIds){
                        searchParams['id'] = '!IN(' + excludeIds + ')';
                    }

                }

                return searchParams;
            }
        },

        projectRegistration: {
            onSave: function($selector){
                var entity = $selector.data('entity'),
                    avatarUrl = MapasCulturais.defaultAvatarURL,
                    shortDescription = entity.shortDescription.replace("\n",'<br/>');

                $('#registration-agent-id').val(entity.id);

            },

            onHidden : function ($selector) {
                $selector.removeClass('editable-unsaved');
            },

            selection: function(entity, $selector){
                $selector.data('entity', entity);
                return entity.name;
            },

            noMatches: function(term, $selector){
                return 'Nenhum agente encontrado.';
            },

            onClear: function($selector){ },

            ajaxData: function(searchParams, $selector){
                var excludedIds = MapasCulturais.request.controller === 'agent' && MapasCulturais.entity.id? [MapasCulturais.entity.id] : [];

                excludedIds.push($selector.data('value'));

                if ( excludedIds.length > 0)
                    searchParams.id = '!in('+excludedIds.toString()+')';

                if(!MapasCulturais.cookies.get('mapasculturais.adm'))
                    searchParams.user = 'eq(@me)';

                if($selector.data('profiles-only'))
                    searchParams.isUserProfile = 'eq(true)';

                searchParams['@select'] += ',shortDescription,';
                return searchParams;
            }

        },

        changeOwner:{
            onSave: function($selector){
                var entity = $selector.data('entity'),
                    avatarUrl = MapasCulturais.defaultAvatarURL,
                    shortDescription = entity.shortDescription.replace("\n",'<br/>');

                $selector.data('value', entity.id);

                try{
                    avatarUrl = entity.files.avatar.files.avatarSmall.url;
                }catch(e){};

                $selector.parents('.js-owner').find('.js-owner-avatar').attr('src', avatarUrl);
                $selector.parents('.js-owner').find('.js-owner-description').html(shortDescription);

            },

            selection: function(entity, $selector){
                $selector.data('entity', entity);
                return entity.name;
            },

            noMatches: function(term, $selector){
                return 'Nenhum agente encontrado.';
            },

            onClear: function($selector){ },

            ajaxData: function(searchParams, $selector){
                var excludedIds = [$selector.editable('getValue').ownerId];

                if(MapasCulturais.request.controller === 'agent' && MapasCulturais.entity.id){
                    excludedIds.push(MapasCulturais.entity.id);
                    if(MapasCulturais.entity.childrenIds){
                        excludedIds = excludedIds.concat(MapasCulturais.entity.childrenIds);
                    }
                }

                if ( excludedIds.length > 0)
                    searchParams.id = '!in('+excludedIds.toString()+')';

                searchParams['@select'] += ',shortDescription';
                searchParams['@permissions'] = '@control';
                return searchParams;
            }
        }
    }
};


MapasCulturais.cookies = {
    get: function(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for(var i=0;i < ca.length;i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1,c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
        }
        return null;
    },

    set: function(key, value, options){
        options = $.extend({}, options);

        if (value == null) {
            options.expires = -1;
        }

        if (typeof options.expires === 'number') {
            var days = options.expires, t = options.expires = new Date();
            t.setDate(t.getDate() + days);
            options.expires = options.expires.toUTCString();
        }else{
            options.expires = 'Session';
        }

        value = String(value);

        return (document.cookie = [
            encodeURIComponent(key), '=', options.raw ? value : encodeURIComponent(value),
            options.expires ? '; expires=' + options.expires : '', // use expires attribute, max-age is not supported by IE
            options.path    ? '; path=' + options.path : '',
            options.domain  ? '; domain=' + options.domain : '',
            options.secure  ? '; secure' : ''
            ].join(''));
    }
};
