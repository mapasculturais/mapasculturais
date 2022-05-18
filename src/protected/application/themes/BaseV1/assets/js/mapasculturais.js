MapasCulturais = MapasCulturais || {};

function charCounter(obj) {
    if ($('#charCounter').text() == '')
        return;

    var max = $('#charCounter').text().split('/');
    $('#charCounter').text(($(obj).val().length + '/' + max[1]));
}

function toggleEventModal(){
    var modal = $("#evt-date-local").siblings().find('div').attr('id');
    $("div#" + modal).toggle();
    $("#evt-date-local").toggle();
}

$(document).
    on('click', '.btn-toggle-attached-modal', function () {
        var modal = $("#evt-date-local").siblings().find('div').attr('id');
        if (modal) {
            toggleEventModal();
        }
    }).
    on('click', '.close-attached-modal', function() {
        var modal = $(this).data('form-id');
        toggleEventModal();
});


function copyToClipboard(element) {
    if (document.selection) {
        var range = document.body.createTextRange();
        range.moveToElementText(element);
        range.select().createTextRange();
        document.execCommand("copy");
    } else if (window.getSelection) {
        var range = document.createRange();
        range.selectNode(element);
        window.getSelection().addRange(range);
        document.execCommand("copy");
    }
}

/**@TODO Internacionalizar */
function alertPublish(id){
    MapasCulturais.confirm('ATENÇÃO, Essa ação é uma ação irreversível. Caso a próxima fase seja uma prestação de contas, primeiro crie a fase de prestação de contas para só depois fazer a publicação.', function () {
        var url = MapasCulturais.createUrl('opportunity', 'publishRegistrations', [id]);
        $.get(url, function() {
            MapasCulturais.Messages.success('Resultado publicado');
        });
        location.reload();
    });
}

$(function () {
    //    $.fn.select2.defaults.separator = '; ';
    //    $.fn.editabletypes.select2.defaults.viewseparator = '; ';

    $("textarea.auto-height").each(function () {
        this.setAttribute("style", "width:100%;height:" + (this.scrollHeight) + "px;overflow-y:hidden;min-height:52px");
    }).on("input", function () {
        this.style.height = "auto";
        this.style.height = (this.scrollHeight+5) + "px";
    });

    $("form.create-entity").submit(function (e) {
        $('.modal-loading').show();
        $(this).hide();

        e.preventDefault();
        var _url = $(this).data('entity');
        var _entity = $(this).serializeArray();
        var _form = $(this).data('formid');
        var self = this;
        $.ajax({
            url: _url, type: 'POST',
            data: _entity,
            success: function (r) {
                
                if((r.hasOwnProperty('error'))){
                    $(".modal-loading").hide();
                    $(".create-entity").css("display", "block");
                }
                
                if (r.id) {
                    var name = r.name;
                    /*
                     @TODO: usar string localizada
                    */
                    var msg = name + " criado com sucesso!";
                    MapasCulturais.Messages.success(msg);

                    if (r.editUrl) {
                        $('.modal-loading').hide();
                        $(self).prev().show();
                        $(self).prev().find('.entidade').text(msg);
                        $(self).prev().find('.new-name').text(name);

                        var $view_btn = $(self).prev().find('.view-entity');
                        var $link = $(self).prev().find('.entity-url');
                        var $edit_btn = $(self).prev().find('.edit-entity');

                        $($edit_btn).attr('href', r.editUrl);
                        $($view_btn).attr('href', r.singleUrl);
                        $($link).attr('href', r.singleUrl);

                        if ($(self).hasClass('is-attached')) {
                            toggleAttachedModal(this, _form);
                        }
                        // $('.entity-modal').find('.js-close').click();
                    }

                } else if (r.error && r.data) {
                    for (var erro in r.data) {
                        var _msg = r.data[erro];
                        MapasCulturais.Messages.error(_msg);
                        alert(_msg);
                    }

                    return false;
                }
            }
        });
    });


    var labels = MapasCulturais.gettext.mapas;

    MapasCulturais.TemplateManager.init();
    MapasCulturais.Modal.initKeyboard('.js-dialog');
    MapasCulturais.Modal.initDialogs('.js-dialog');
    MapasCulturais.Modal.initButtons('.js-open-dialog');

    MapasCulturais.EditBox.initBoxes('.js-editbox');
    MapasCulturais.EditBox.initButtons('.js-open-editbox');

    MapasCulturais.AjaxUploader.init();
    MapasCulturais.Remove.init();

    MapasCulturais.Video.setupVideoGallery('.js-videogallery');
    MapasCulturais.Search.init(".js-search");

    // confirmação para excluir entidades
    $(".js--remove-entity-button").click(function (e) {
        return confirm(labels.confirmRemoveEntity);
    });

    // confirmação para excluir entidades
    $(".js--destroy-entity-button").click(function (e) {
        return confirm(labels.confirmDestroyEntity);
    });

    // bind alert close buttons
    $('.alert .close').click(function () {
        $(this).parent().slideUp('fast');
    }).css('cursor', 'pointer');

    if (MapasCulturais.request.controller === 'app') {
        MapasCulturais.App.init();
    }

    //main nav submenus toggle on click
    $('body').on('click', '.js-submenu-toggle', function () {
        var $self = $(this),
            $target = eval($self.data('submenu-target'));
        //hides by clicking outside
        $('body').one('click', function (event) {
            if ($self.find(event.target).length == 0) {
                $target.fadeOut(0);
            }
        });
        $target.fadeToggle(100);
    });

    $('body').on('click', '.dropdown .placeholder', function () {
        var $dropdown = $(this).parents('.dropdown'),
            $submenu = $dropdown.find('.submenu-dropdown');

        if ($submenu.is(':visible')) {
            $submenu.hide();
        } else {
            $submenu.show();
        }

        if (!$dropdown.data('init')) {
            $dropdown.data('init', true);

            if ($dropdown.data('closeonclick')) {
                $submenu.click(function () {
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
            $('body').on('click', function (event) {
                if ($submenu.find(event.target).length == 0 && $dropdown.find(event.target).length == 0) {
                    $submenu.hide();
                }
            });
        }
    });

    if ($('#funcao-do-agente').length) {
        $('#funcao-do-agente .js-options li').click(function () {
            var roleToRemove = $('#funcao-do-agente .js-selected span').data('role');
            var roleToAdd = $(this).data('role');
            var label = $(this).find('span').html();

            var change = function () {
                $('#funcao-do-agente .js-selected span').html(label);
                $('#funcao-do-agente .js-selected span').data('role', roleToAdd);
                console.log(label);
            };

            if (roleToAdd) {
                $.post(MapasCulturais.baseURL + 'agent/addRole/' + MapasCulturais.entity.id, { role: roleToAdd }, function (r) {
                    if (r && !r.error) change();
                });
            } else {
                $.post(MapasCulturais.baseURL + 'agent/removeRole/' + MapasCulturais.entity.id, { role: roleToRemove }, function (r) {
                    if (r && !r.error) change();
                });
            }


        });
    }

    MapasCulturais.spinnerURL = MapasCulturais.assetURL + '/img/spinner.gif';

    // identify Internet Explorer
    if (navigator.appName != 'Microsoft Internet Explorer' && !(navigator.appName == 'Netscape' && navigator.userAgent.indexOf('Trident') !== -1)) {
        //not IE
    } else {
        // is IE
        $('body').addClass('ie');
        //identify version
        var ua = navigator.userAgent.toLowerCase();
        if (!isNaN(version = parseInt(ua.split('msie')[1]))) { // 7, 8, 9, 10
            $('body').addClass('ie' + version);
        } else if (parseInt(ua.split('rv:')[1]) === 11) { // 11
            $('body').addClass('ie11');
        }
    }

    if (MapasCulturais.entity) {
        MapasCulturais.entity.getTypeName = function () {
            switch (MapasCulturais.request.controller) {
                case 'agent': return labels['agente']; break;
                case 'space': return labels['espaço']; break;
                case 'event': return labels['evento']; break;
                case 'project': return labels['projeto']; break;
                case 'seal': return labels['selo']; break;
            }
        };
    }


    // confirm

    $('a.js-confirm-before-go').click(function () {
        if (!confirm($(this).data('confirm-text')))
            return false;
    });


    // positioning agent details box on mobile

    if ($(window).width() < 768) {
        $('.agentes-relacionados .avatar').on('click hover', function () {
            $('.descricao-do-agente').hide();
            var descAgent = $(this).find('.descricao-do-agente');
            var descAgentHeight = descAgent.outerHeight();
            descAgent.show().css('top', -((descAgentHeight) + 10));
        });
    }

    function setEvaluationFormHeight() {
        var h = $(window).height() - $('#main-header').height();
        $('#registration-evaluation-form').height(h - 50);
    }

    setEvaluationFormHeight();

    $(window).resize(setEvaluationFormHeight);
});

//Restart entity form
function restartingCreateEntity() {
    if ($("#dialog-event-occurrence").hasClass('occurrence-open')) {
        $('.modal-loading, .modal-feedback, .create-entity').removeAttr("style");
        $('.create-entity').trigger("reset");
        $(".js-event-occurrence").html("");
    } else {
        $('.modal-loading, .modal-feedback, .create-entity').removeAttr("style");
        $('.js-dialog').attr('style', 'display: none');
        $('.create-entity').trigger("reset");
        $(".modal-feedback-event").css('display', 'none');
        $(".create-event").css('display', 'block');
        $(".js-event-occurrence").html("");
        $(".cancel-action").css('display', 'block');
        $(".btn-event").css('display', 'block');
        $('.event-occurrence-list').addClass("hidden");
    }

}

MapasCulturais.utils = {
    getObjectProperties: function (obj) {
        var keys = [];
        for (var key in obj) {
            keys.push(key);
        }
        return keys;
    },
    sortOjectProperties: function (obj) {
        var newObj = {};

        this.getObjectProperties(obj).sort().forEach(function (e) {
            newObj[e] = obj[e];
        });

        return newObj;
    },

    isObjectEquals: function (obj1, obj2) {
        return JSON.stringify(this.sortOjectProperties(obj1)) === JSON.stringify(this.sortOjectProperties(obj2));
    },

    inArray: function (array, obj) {
        for (var i in array) {
            if (this.isObjectEquals(array[i], obj)) {
                return true;
            }
        }
        return false;
    },

    arraySearch: function (array, obj) {
        for (var i in array) {
            if (this.isObjectEquals(array[i], obj)) {
                return i;
            }
        }
        return false;
    }
};

MapasCulturais.createUrl = function (controller_id, action_name, args) {
    var shortcuts = this.routes.shortcuts,
        actions = this.routes.actions,
        controllers = this.routes.controllers,

        u = MapasCulturais.utils,
        route = '';

    action_name = action_name || this.routes.default_action_name;

    if (args) {
        args = u.sortOjectProperties(args);
    }

    if (args && u.inArray(shortcuts, [controller_id, action_name, args])) {
        route = u.arraySearch(shortcuts, [controller_id, action_name, args]) + '/';
        args = null;
    } else if (u.inArray(shortcuts, [controller_id, action_name])) {
        route = u.arraySearch(shortcuts, [controller_id, action_name]) + '/';
    } else {
        if (u.inArray(controllers, controller_id)) {
            route = u.arraySearch(controllers, controller_id) + '/';
        } else {
            route = controller_id + '/';
        }

        if (action_name !== this.routes.default_action_name) {
            if (u.inArray(actions, action_name)) {
                route += u.arraySearch(actions, action_name) + '/';
            } else {
                route += action_name + '/';
            }
        }
    }

    if (args) {
        for (var key in args) {
            var val = args[key];
            if (key == parseInt(key)) { // is integer
                route += val + '/';
            } else {
                route += key + ':' + val + '/';
            }
        }
    }

    return MapasCulturais.baseURL + route;
};

MapasCulturais.auth = {
    cb: null,
    require: function (cb) {
        MapasCulturais.auth.cb = cb;
        $('#require-authentication').attr('src', MapasCulturais.baseURL + 'panel/requireAuth').fadeIn();
    },

    finish: function () {
        if (MapasCulturais.auth.cb) {
            MapasCulturais.auth.cb();
        }

        MapasCulturais.auth.cb = null;
        $('#require-authentication').fadeOut();
    }
}

MapasCulturais.TemplateManager = {
    templates: {},
    init: function () {
        var $templates = $('.js-mustache-template');
        var $this = this;
        $templates.each(function () {
            $this.templates[$(this).attr('id')] = $(this).text();
            $(this).remove();
        });
    },

    getTemplate: function (id) {
        if (this.templates[id])
            return this.templates[id];
        else
            return null;
    }
};

MapasCulturais.App = {
    init: function () {
        $('.js-select-on-click').on('click', function () {
            var selector = $(this).data('selectTarget');
            $(selector).trigger('doubleClick');
        });

        if ($('.js-input--app-key').length && $('.js-input--app-key--toggle').length) {
            $('.js-input--app-key--toggle').on('click', function () {
                if ($('.js-input--app-key').attr('type') === 'password') {
                    $('.js-input--app-key').attr('type', 'text');
                } else {
                    $('.js-input--app-key').attr('type', 'password');
                }

                return false;
            });
        }
    }
};

MapasCulturais.defaultAvatarURL = MapasCulturais.assets.avatarAgent;

function editableEntityAddHash() {
    $('#editable-entity').find('.js-toggle-edit').each(function () {
        var href = $(this).attr('href'),
            cleanHref = href.indexOf('#') !== -1 ? href.split('#')[0] : href;
        $(this).attr('href', cleanHref + location.hash);
    });
}
jQuery(document).ready(function () {
    editableEntityAddHash();
    $(window).on('hashchange', function () {
        editableEntityAddHash();
    });
}).on('click', '.close-modal', function () {
    MapasCulturais.Modal.close('.entity-modal');
    restartingCreateEntity();
});


MapasCulturais.Messages = {
    animated: false,
    delayToFadeOut: 5000,
    fadeOutSpeed: 'slow',
    showMessage: function (type, message) {
        var $container = $('#editable-entity');
        var $message = $('<div class="alert ' + type + '">"').html(message);
        var $mainSection = $('#main-section');
        var delayToFadeOut = this.delayToFadeOut;
        var marginTop = 42;
        $container.append($message);


        if ($container.hasClass('js-not-editable')) {

            function animateAndShow(cb) {
                MapasCulturais.Messages.animated = true;
                $mainSection.animate({ marginTop: parseInt($mainSection.css('margin-top')) + marginTop }, 'fast', cb);
            }
            $container.slideDown('fast');

            var cb = function (animate) {
                $message.css('display', 'inline-block').css('display', 'inline-block').delay(delayToFadeOut).fadeOut(this.fadeOutSpeed, function () {
                    $(this).remove();
                    if ($container.find('>').length === 0) {
                        $container.slideUp('fast');
                        $mainSection.animate({ marginTop: parseInt($mainSection.css('margin-top')) - marginTop }, 'fast', function () {
                            MapasCulturais.Messages.animated = false;
                        });
                    }
                });
            };

            if (MapasCulturais.Messages.animated) {
                cb();
            } else {
                animateAndShow(cb);
            }
        } else {
            $message.css('display', 'inline-block').css('display', 'inline-block').delay(delayToFadeOut).fadeOut(this.fadeOutSpeed, function () {
                $(this).remove();
            });
        }
        $(window).scroll();
    },
    success: function (message) {
        this.showMessage('success', message);
    },
    error: function (message) {
        this.showMessage('danger', message);
    },
    help: function (message) {
        this.showMessage('info', message);
    },
    alert: function (message) {
        this.showMessage('warning', message);
    }

}

MapasCulturais.confirm = function (message, cb) {
    if (confirm(message))
        cb();
};

MapasCulturais.Modal = {
    time: 'fast',
    initKeyboard: function (selector) {
        $(document.body).keyup(function (e) {
            if (e.keyCode == 27) {
                $(selector).each(function () {
                    if ($(this).is(':visible')) {
                        $(this).find('.js-close').click();
                    }
                });
            }
        });
    },

    initDialogs: function (selector) {
        $(selector).each(function () {
            $('body').append($(this));
            if ($(this).find('.js-dialog-disabled').length)
                return;

            if ($(this).data('dialog-init'))
                return;

            var $dialog = $(this);
            /*$dialog.hide();  Moved to style.css */


            var _title = $(this).attr('title');
            $dialog.data('dialog-init', 1);
            if (_title)
                $dialog.prepend('<h2>' + $(this).attr('title') + '</h2>');

            $dialog.prepend('<a href="#" class="js-close icon icon-close" rel="noopener noreferrer"></a>');

            // close button
            $dialog.find('.js-close').click(function () {
                MapasCulturais.Modal.close($dialog);
                restartingCreateEntity();
                return false;
            });
        });
    },

    initButtons: function (selector) {

        if ($(selector).data('button-initialized')) return false;
        else $(selector).data('button-initialized', true);
        $(selector).each(function () {
            var dialog_selector = $(this).data('dialog');
            var $dialog = $(dialog_selector);
            if ($dialog.find('.js-dialog-disabled').length)
                $(this).addClass('inactive').addClass('hltip').attr('title', $dialog.find('.js-dialog-disabled').data('message'));

        });
        $(selector).click(function () {
            if ($(this).hasClass('inactive'))
                return false;

            var dialog_selector = $(this).data('dialog');
            MapasCulturais.Modal.open(dialog_selector);
            if ($(this).data('dialog-callback'))
                eval($(this).data('dialog-callback'))($(this));
            return false;
        });
    },

    close: function (selector) {
        $('body').css('overflow', 'auto');
        var $dialog = $(selector);
        //alert('closing');
        $dialog.find('.editable').editable('hide');
        $dialog.hide();
        if ($('#blockdiv').is(':visible')) {
            $('#blockdiv').hide();
            $('body').css('overflow', 'visible');
        }
        return;
    },

    open: function (selector) {
        var $dialog = $(selector);

        $dialog.find('div.alert.danger').html('').hide();
        $dialog.find('.js-ajax-upload-progress').hide();
        $dialog.css('opacity', 0).show();
        setTimeout(function () {
            var top = $dialog.height() + 100 > $(window).height() ? $(window).scrollTop() + 100 : $(window).scrollTop() + ($(window).height() - $dialog.height()) / 2 - 50;

            $dialog.css({
                top: top,
                left: '50%',
                marginLeft: -$dialog.width() / 2,
                opacity: 1
            });
        }, 25);


        return;
    }
};

MapasCulturais.addEntity = function (e) {
    var _modal = e.context.dataset.dialog;
    if (_modal) {
        $('#blockdiv').show();
        $('body').css('overflow', 'hidden');
        MapasCulturais.Modal.open(_modal);
    }
};

MapasCulturais.EditBox = {
    time: 'fast',

    setPosition: function ($box, target) {
        if ($box.hasClass('mc-left')) {
            $box.position({
                my: 'right-10 center',
                at: 'left center',
                of: target
            });

        } else if ($box.hasClass('mc-right')) {
            $box.position({
                my: 'left+10 center',
                at: 'right center',
                of: target
            });

        } else if ($box.hasClass('mc-top')) {
            $box.position({
                my: 'center bottom-10',
                at: 'center top',
                of: target
            });

        } else if ($box.hasClass('mc-bottom')) {
            $box.position({
                my: 'center top+10',
                at: 'center bottom',
                of: target
            });
        }
    },

    initKeyboard: function (selector) {
        $(document.body).keyup(function (e) {
            if (e.keyCode == 27) {
                $(selector).each(function () {
                    if ($(this).is(':visible')) {
                        $(this).find('.js-close').click();
                    }
                });
            }
        });
    },

    initBoxes: function (selector) {

        var labels = MapasCulturais.gettext.mapas;

        $(selector).each(function () {
            var $dialog = $(this);

            if ($dialog.find('.js-dialog-disabled').length)
                return;

            if ($dialog.data('dialog-init'))
                return;

            if ($dialog.data('init'))
                return;

            $dialog.data('init', true);

            /*$dialog.hide();  Moved to style.css */

            $dialog.addClass('edit-box');

            $dialog.data('dialog-init', 1);
            if ($dialog.attr('title')) {
                $dialog.prepend('<header><h1>' + $(this).attr('title') + '</h1></header>');
            }
            var submit_label = $dialog.data('submit-label') ? $dialog.data('submit-label') : labels['Enviar'];
            var cancel_label = $dialog.data('cancel-label') ? $dialog.data('cancel-label') : labels['Cancelar'];

            $dialog.append('<footer><button class="mc-cancel btn btn-default">' + cancel_label + '</button> <button type="submit" class="mc-submit">' + submit_label + '</button> </footer><div class="mc-arrow"></div>');

            // close button
            $dialog.find('.mc-cancel').click(function () {
                MapasCulturais.EditBox.close($dialog);
                return false;
            });

            // submit form
            $dialog.find('footer button.mc-submit').click(function () {
                $dialog.find('form').submit();
            });
        });
    },

    initButtons: function (selector) {
        $(selector).each(function () {
            var $button = $(this);
            var dialog_selector = $(this).data('target');
            var $dialog = $(dialog_selector);
            if ($dialog.find('.js-dialog-disabled').length)
                $(this).addClass('inactive').addClass('hltip').attr('title', $dialog.find('.js-dialog-disabled').data('message'));

        });
        $(selector).click(function () {
            var $button = $(this);
            if ($button.hasClass('inactive'))
                return false;

            var dialog_selector = $button.data('target');

            MapasCulturais.EditBox.open(dialog_selector, $button);

            if ($button.data('dialog-title'))
                $(dialog_selector).find('header h1').html($button.data('dialog-title'));

            if ($button.data('dialog-callback'))
                eval($button.data('dialog-callback'))($button);

            return false;
        });
    },

    close: function (selector) {
        $('body').css('overflow', 'auto');
        var $dialog = $(selector);
        //alert('closing');
        $dialog.find('.editable').editable('hide');
        $dialog.hide();
        return;
    },

    open: function (selector, $button) {
        var $dialog = $(selector);
        $dialog.find('div.alert.danger').html('').hide();

        MapasCulturais.AjaxUploader.resetProgressBar(selector);

        $dialog.show();
        $dialog.find('input,textarea').not(':hidden').first().focus();
        $dialog.css('opacity', 0);
        MapasCulturais.EditBox.setPosition($dialog, $button);
        setTimeout(function () {
            MapasCulturais.EditBox.setPosition($dialog, $button);
            $dialog.css('opacity', 1);
        }, 25);

        return;
    }
};

MapasCulturais.Remove = {
    init: function () {
        $('body').on('click', '.js-remove-item', function (e) {
            e.stopPropagation();
            var $this = $(this);
            MapasCulturais.confirm('Deseja remover este item?', function () {
                var $target = $($this.data('target'));
                var href = $this.data('href');

                $.getJSON(href, function (r) {
                    if (r.error) {
                        MapasCulturais.Messages.error(r.data);
                    } else {
                        var cb = function () { };
                        if ($this.data('remove-callback'))
                            cb = $this.data('remove-callback');
                        $target.remove();
                        if (typeof cb === 'string')
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

MapasCulturais.AjaxUploader = {
    resetProgressBar: function (containerSelector, acivate) {
        var bar = $(containerSelector).find('.js-ajax-upload-progress .bar');
        var percent = $(containerSelector).find('.js-ajax-upload-progress .percent');
        var percentVal = '0%';
        bar.stop().width(percentVal);
        percent.html(percentVal);
        if (!acivate)
            $(containerSelector).find('.js-ajax-upload-progress .progress').addClass('inactive');
        else
            $(containerSelector).find('.js-ajax-upload-progress .progress').removeClass('inactive');

    },
    animationTime: 100,
    init: function (selector, extraOptions) {
        selector = selector || '.js-ajax-upload';
        extraOptions = extraOptions || {};

        $(selector).each(function () {

            if ($(this).data('initialized'))
                return;

            $(this).show();
            $(this).data('initialized', true);

            var bar = $(this).parent().find('.js-ajax-upload-progress .bar');
            var percent = $(this).parent().find('.js-ajax-upload-progress .percent');

            MapasCulturais.AjaxUploader.resetProgressBar($(this).parent(), false);
            var $this = $(this);
            // bind form using 'ajaxForm'
            $(this).ajaxForm(Object.assign({
                beforeSend: function (xhr) {
                    $this.data('xhr', xhr);
                    //@TODO validate size and type before upload
                },
                //target:        '#output1',   // target element(s) to be updated with server response
                beforeSubmit: function (arr, $form, options) {
                    MapasCulturais.AjaxUploader.resetProgressBar($form.parent(), true);
                },
                uploadProgress: function (event, position, total, percentComplete) {
                    var percentVal = percentComplete + '%';
                    bar.animate({ 'width': percentVal });
                    percent.html(percentVal);
                },
                success: function (response, statusText, xhr, $form) {

                    var percentVal = '100%';
                    bar.width(percentVal);
                    percent.html(percentVal);

                    if (response.error) {
                        var _animation = this.animationTime;

                        MapasCulturais.AjaxUploader.resetProgressBar($form.parent(), false);
                        var group = $form.data('group');
                        var error_message = typeof response.data == 'string' ? response.data : response.data[group];
                        $form.find('div.alert.danger').html(error_message).fadeIn(_animation).delay(5000).fadeOut(_animation);

                        setTimeout(function () {
                            $('.carregando-arquivo').hide();
                            $('.submit-attach-opportunity').show();
                        }, _animation);

                        return;
                    }


                    var $target = $($form.data('target'));
                    var group = $form.find('input:file').attr('name');

                    var template = $form.find('script').text();
                    if ($form.data('action')) {
                        switch ($form.data('action').toString()) {
                            case 'replace':
                                var html = Mustache.render(template, response[group]);
                                $target.replaceWith($(html));
                                break;
                            case 'set-content':

                                var html = Mustache.render(template, response[group]);
                                $target.html(html);
                                break;
                            case 'a-href':
                                try {
                                    $target.attr('href', response[group].url);
                                } catch (e) { }

                                break;
                            case 'image-src':
                                try {
                                    if ($form.data('transform'))
                                        $target.attr('src', response[group].files[$form.data('transform')].url);
                                    else
                                        $target.attr('src', response[group].url);
                                } catch (e) { }

                                break;
                            case 'background-image':
                                $('#remove-background-button').toggleClass('hide-background-button');
                                $('#remove-background-button').toggleClass('display-background-button');

                                $target.each(function () {
                                    try {
                                        if ($form.data('transform'))
                                            $(this).css('background-image', 'url(' + response[group].files[$form.data('transform')].url + ')');
                                        else
                                            $(this).css('background-image', 'url(' + response[group].url + ')');
                                    } catch (e) { }
                                });

                                $('#remove-background-button a').data('href', response[group].deleteUrl);
                                break;

                            case 'append':
                                for (var i in response[group]) {

                                    if (!response[group][i].description)
                                        response[group][i].description = response[group][i].name;

                                    var html = Mustache.render(template, response[group][i]);
                                    $target.append(html);
                                }
                                break;

                        }
                    }
                    $form.trigger('ajaxForm.success', [response]);

                    $form.get(0).reset();
                    if ($form.parents('.js-editbox').data('success-callback'))
                        eval($form.parents('.js-editbox').data('success-callback'));

                    $form.parents('.js-editbox').find('.mc-cancel').click();
                },

                // other available options:
                //url:       url         // override for form's 'action' attribute
                //type:      type        // 'get' or 'post', override for form's 'method' attribute
                dataType: 'json'        // 'xml', 'script', or 'json' (expected server response type)
                //clearForm: true        // clear all form fields after successful submit
                //resetForm: true        // reset the form after successful submit

                // $.ajax options can be used here too, for example:
                //timeout:   3000
            }, extraOptions));
        });


    }
};

MapasCulturais.Video = {
    collection: {},
    parseURL: function (url) {
        return purl(url);
    },
    isYoutube: function (parsedURL) {
        return parsedURL.attr('host').indexOf('youtube') != -1;
    },
    getYoutubeData: function (youtubeVideoID) {
        return {
            thumbnalURL: 'http://img.youtube.com/vi/' + videoID + '/0.jpg',
            playerURL: '//www.youtube.com/embed/' + videoID + '?html5=1'
        }
    },
    getVideoBasicData: function (url) {
        var parsedURL = this.parseURL(url);
        var host = parsedURL.attr('host');
        var provider = '';
        var videoID = '';
        if (parsedURL.attr('host').indexOf('youtube') != -1) {
            provider = 'youtube';
            videoID = parsedURL.param('v');
        } else if (parsedURL.attr('host').indexOf('vimeo') != -1) {
            provider = 'vimeo';
            videoID = parsedURL.attr('path').split('/')[1];
        }
        return {
            'parsedURL': parsedURL,
            'provider': provider,
            'videoID': videoID
        }
    },
    setupVideoGallery: function (gallerySelector) {

        if ($(gallerySelector).length == 0)
            return false;

        $(gallerySelector + ' .js-metalist-item-display').each(function () {
            MapasCulturais.Video.getAndSetVideoData($(this).data('videolink'), $(this).parent(), MapasCulturais.Video.setupVideoGalleryItem);
        });
        var $firstItem = $(gallerySelector + ' .js-metalist-item-display:first').parent();
        if (!$firstItem.length) {
            $('#video-player').hide();
            return false;
        }
        $('iframe#video_display').attr('src', $firstItem.data('player-url'));
        $firstItem.addClass('active');
    },
    setupVideoGalleryItem: function (videoData, $element) {
        //$Element should be a.js-metalist-item-display
        $element.attr('href', '#video');
        $element.data('player-url', videoData.playerURL);
        $element.find('img').attr('src', videoData.thumbnailURL);

        var video_id = $element.attr('id');

        $element.on('click', function () {
            $('iframe#video_display').attr('src', videoData.playerURL);
            $('iframe#video_display').data('open-video', $(this).attr('id'));
            $(this).parent().find('.active').removeClass('active');
            $(this).addClass('active');
            $('#video-player').show();
        });

        var $container = $element.parent();
        if ($element.find('.js-remove-item').length) {
            $element.find('.js-remove-item').data('remove-callback', function () {

                $('iframe#video_display').attr('src', '');
                $('iframe#video_display').data('open-video', '');
                $('#video-player').hide();
            });
        }

    },
    setTitle: function (videoData, $element) {
        $element.val(videoData.videoTitle);
    },
    getAndSetVideoData: function (videoURL, $element, callback) {
        videoURL = videoURL.trim();
        var videoData = {
            parsedURL: purl(videoURL),
            provider: '',
            videoID: '',
            thumbnailURL: '',
            playerURL: '',
            details: {}
        };

        if (videoData.parsedURL.attr('host').indexOf('youtube') != -1) {
            videoData.provider = 'youtube';
            videoData.videoID = videoData.parsedURL.param('v');
            videoData.thumbnailURL = 'http://img.youtube.com/vi/' + videoData.videoID + '/0.jpg';
            videoData.playerURL = '//www.youtube.com/embed/' + videoData.videoID + '?html5=1';
            callback(videoData, $element);
            $.getJSON('https://gdata.youtube.com/feeds/api/videos/' + videoData.videoID + '?v=2&alt=json', function (data) {
                videoData.details = data;
                videoData.videoTitle = data.entry.title.$t;
                MapasCulturais.Video.collection[videoURL] = videoData;
                callback(videoData, $element);
                return videoData;
            });
        } else if (videoData.parsedURL.attr('host').indexOf('vimeo') != -1) {
            videoData.provider = 'vimeo';
            var tmpArray = videoData.parsedURL.attr('path').split('/');
            videoData.videoID = tmpArray[tmpArray.length - 1];
            $.getJSON('http://www.vimeo.com/api/v2/video/' + videoData.videoID + '.json?callback=?', { format: "json" }, function (data) {
                videoData.details = data[0];
                videoData.thumbnailURL = data[0].thumbnail_small;
                videoData.playerURL = '//player.vimeo.com/video/' + videoData.videoID + '';
                videoData.videoTitle = data[0].title;
                callback(videoData, $element);
                MapasCulturais.Video.collection[videoURL] = videoData;
                return videoData;
            });
        } else {
            //no valid provider
            videoData.thumbnailURL = 'http://www.bizreport.com/images/shutterstock/2013/04/onlinevideo_135877229-thumb-380xauto-2057.jpg';
            callback(videoData, $element);
            return videoData;
        }

        var withDetails = function () {
            // get details from youtube api
            if (!videoData.details) {
                $.getJSON('https://gdata.youtube.com/feeds/api/videos/' + videoData.videoID + '?v=2&alt=json', { format: "json" }, function (data) {
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

    init: function (selector) {
        if ($(selector).length === 0 || $(selector).hasClass('select2-offscreen')) return false;

        $(selector).each(function () {
            var $selector = $(this);

            $selector.editable({
                type: 'select2',
                showbuttons: false,
                onblur: 'submit',
                mode:'inline',
                name: $selector.data('field-name') ? $selector.data('field-name') : null,
                select2: {
                    //                    multiple: $selector.data('multiple'),
                    //                    tokenSeparators: [";",";"],
                    //                    separator:'; ',
                    //                    viewseparator: '; ',
                    width: $selector.data('search-box-width'),
                    placeholder: $selector.data('search-box-placeholder'),
                    minimumInputLength: 0,
                    allowClear: $selector.data('allow-clear'),
                    initSelection: function (e, cb) {
                        cb({ id: $selector.data('value'), name: $selector.data('editable').$element.html() });
                    },
                    ajax: {
                        url: function () {
                            var format = $selector.data('selection-format');
                            var apiMethod = 'find';
                            if (MapasCulturais.Search.formats[format] && MapasCulturais.Search.formats[format].apiMethod)
                                apiMethod = MapasCulturais.Search.formats[format].apiMethod;
                            return MapasCulturais.baseURL + 'api/' + $selector.data('entity-controller') + '/' + apiMethod;
                        },
                        dataType: 'json',
                        quietMillis: 350,
                        data: function (term, page) { // page is the one-based page number tracked by Select2
                            var searchParams = MapasCulturais.Search.getEntityController(term, page, $selector);

                            var format = $selector.data('selection-format');

                            if (MapasCulturais.Search.formats[format] && MapasCulturais.Search.formats[format].ajaxData)
                                searchParams = MapasCulturais.Search.formats[format].ajaxData(searchParams, $selector);
                            else
                                searchParams = MapasCulturais.Search.ajaxData(searchParams, $selector);

                            return searchParams;
                        },
                        results: function (data, page) {
                            var more = data.length == MapasCulturais.Search.limit;
                            // notice we return the value of more so Select2 knows if more results can be loaded

                            return { results: data, more: more };
                        }
                    },
                    formatResult: function (entity) {
                        var format = $selector.data('selection-format');
                        if (MapasCulturais.Search.formats[format] && MapasCulturais.Search.formats[format].result)
                            return MapasCulturais.Search.formats[format].result(entity, $selector);
                        else
                            return MapasCulturais.Search.formatResult(entity, $selector);
                    }, // omitted for brevity, see the source of this page

                    formatSelection: function (entity) {
                        var format = $selector.data('selection-format');
                        return MapasCulturais.Search.formats[format].selection(entity, $selector);
                    }, // omitted for brevity, see the source of this page

                    formatNoMatches: function (term) {
                        var format = $selector.data('selection-format');
                        return MapasCulturais.Search.formats[format].noMatches(term, $selector);
                    },
                    escapeMarkup: function (m) { return m; }, // we do not want to escape markup since we are displaying html in results
                }
            });
            if ($selector.data('disable-buttons')) {
                $selector.on('shown', function (e, editable) {
                    var format = $selector.data('selection-format');

                    /*Hide editable buttons because the option showbuttons:false autosubmitts the form adding two agents...
                    Also Auto-open select2 on editable.shown and auto-close editable popup on select2-close*/

                    e.stopPropagation();
                    if (arguments.length == 2) {
                        setTimeout(function () {
                            editable.input.$input.parents('.control-group').addClass('editable-popup-botoes-escondidos');
                        }, 0);
                        setTimeout(function () {
                            editable.input.$input.select2("open");
                        }, 200);
                        editable.input.$input.on('select2-close', function () {
                            setTimeout(function () {
                                $selector.editable('hide');
                            }, 200);
                        });
                    }
                });
            }
            if ($selector.data('auto-open')) {
                $selector.on('shown', function (e, editable) {
                    setTimeout(function () {
                        editable.input.$input.select2("open");
                    }, 200);
                });
            }
            var format = $selector.data('selection-format');
            $selector.on('save', function () {
                try {
                    MapasCulturais.Search.formats[format].onSave($selector);
                } catch (e) {
                }
            });
            $selector.on('hidden', function () {
                try {
                    MapasCulturais.Search.formats[format].onHidden($selector);
                } catch (e) {
                }
            });

        });
    },



    getEntityController: function (term, page, $selector) {

        var entitiyControllers = {
            'default': {
                name: 'ilike(*' + term.replace(' ', '*') + '*)', //search term
                '@select': 'id,name,terms,type',
                '@limit': MapasCulturais.Search.limit, // page size
                '@page': page,
                '@order': 'name ASC', // page number
                '@files': '(avatar.avatarSmall):url'
            },
            'agent': { //apenas adicionei a shortDescription
                name: 'ilike(*' + term.replace(' ', '*') + '*)', //search term,
                '@select': 'id,name,terms,type',
                '@limit': MapasCulturais.Search.limit, // page size
                '@page': page,
                '@order': 'name ASC', // page number
                '@files': '(avatar.avatarSmall):url'
            }
        };

        if (entitiyControllers[$selector.data('entity-controller')]) {
            return entitiyControllers[$selector.data('entity-controller')];
        } else {
            return entitiyControllers['default'];
        }
    },

    processEntity: function (entity, $selector) {
        entity.areas = function () {
            if (this.terms && this.terms.area)
                return this.terms.area.join(', ');
            else
                return '';
        };

        entity.linguagens = function () {
            if (this.terms && this.terms.linguagem)
                return this.terms.linguagem.join(', ');
            else
                return '';
        };

        entity.tags = function () {
            if (this.terms && this.terms.tags)
                return this.terms.tags.join(', ');
            else
                return '';
        };

        entity.thumbnail = function () {
            var entityDefaultAvatar = MapasCulturais.assets['avatar' + $selector.data('entity-controller')[0].toUpperCase() + $selector.data('entity-controller').slice(1)];
            if (this['@files:avatar.avatarSmall'])
                return this['@files:avatar.avatarSmall'].url;
            else
                return entityDefaultAvatar;
        };

    },

    renderTemplate: function (template, entity, $selector) {
        this.processEntity(entity, $selector);
        return Mustache.render(template, entity);
    },

    getEntityThumbnail: function (entity, thumbName) {
        if (!entity.files || entity.files.length == 0 || !entity.files[thumbName] || entity.files[thumbName].length == 0)
            return '';
        else {
            if (entity.files[thumbName].files['avatarSmall'])
                return entity.files[thumbName].files['avatarSmall'].url;
        }
    },

    formatResult: function (entity, $selector) {
        var searchResultTemplate = $($selector.data('search-result-template')).text();
        return this.renderTemplate(searchResultTemplate, entity, $selector);
    },

    ajaxData: function (searchParams, $selector) {
        var excludedIds = MapasCulturais.request.controller === $selector.data('entity-controller') && MapasCulturais.entity.id ? [MapasCulturais.entity.id] : [];

        if (excludedIds.length > 0)
            searchParams.id = '!in(' + excludedIds.toString() + ')';

        return searchParams;
    },

    formats: {
        chooseProject: {
            //apiMethod : 'findByUserApprovedRegistration',
            onSave: function ($selector) {
                var entity = $selector.data('entity');
                $selector.data('value', entity.id);
                $selector.data('value-name', entity.name);
            },
            selection: function (entity, $selector) {
                $selector.data('entity', entity);
                return entity.name;
            },

            noMatches: function (term, $selector) {
                return 'Nenhum projeto encontrado.';
            },

            onClear: function ($selector) {
            },

            ajaxData: function (searchParams) {
                searchParams['@permissions'] = 'requestEventRelation';
                return searchParams;
            }
        },

        chooseSpace: {
            onSave: function ($selector) {
                var entity = $selector.data('entity'),
                    avatarUrl = MapasCulturais.defaultAvatarURL,
                    shortDescription = entity.shortDescription ? entity.shortDescription.replace("\n", '<br/>') : '';

                $selector.data('value', entity.id);

                try {
                    avatarUrl = entity.files.avatar.files.avatarSmall.url;
                } catch (e) { };

                $selector.parents('.js-space').find('.js-space-avatar').attr('src', avatarUrl);
                $selector.parents('.js-space').find('.js-space-description').html(shortDescription);

                $selector.parents('form').find('input[name="spaceId"]').val(entity.id);
            },
            onHidden: function ($selector) {
                $selector.removeClass('editable-unsaved');
            },

            selection: function (entity, $selector) {
                $selector.data('entity', entity);
                return entity.name;
            },

            noMatches: function (term, $selector) {
                return 'Nenhum espaço encontrado.';
            },

            onClear: function ($selector) { },

            ajaxData: function (searchParams, $selector) {

                if ($selector.data('value')) {
                    searchParams.id = '!in(' + $selector.data('value') + ')';
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
                var $selectionTarget = $($selector.data('selection-target'));
                var targetAction = $selector.data('target-action');
                var selectionTemplate = $($selector.data('selection-template')).text();

                var groupname = $selector.parents('.js-related-group').find('.js-related-group-name').text();

                $.post(
                    MapasCulturais.baseURL + MapasCulturais.request.controller + '/createAgentRelation/id:' + MapasCulturais.entity.id,
                    { agentId: entity.id, group: groupname, has_control: '0' }
                );


                if (targetAction == 'append') {
                    MapasCulturais.RelatedAgents.addAgentToGroup(groupname, entity);
                    return $selector.data('search-box-placeholder');
                } else {
                    $selectionTarget.html(markup);
                    return entity.name;
                }
            },

            noMatches: function (term, $selector) {
                var noResultTemplate = $($selector.data('no-result-template')).text();
                $('#dialog-adicionar-integrante').data('group-element', $selector.parents('.js-related-group').find('.js-relatedAgentsContainer'));
                //return term + ' HAHAHA!';
                return noResultTemplate.replace('{{group}}', $selector.parents('.js-related-group').find('.js-related-group-name').text());
            },

            ajaxData: function (searchParams, $selector) {
                var group = $selector.parents('.js-related-group').find('.js-related-group-name').text();

                var excludedIds = MapasCulturais.request.controller === 'agent' && MapasCulturais.entity.id ? [MapasCulturais.entity.id] : [];
                try {
                    if (MapasCulturais.agentRelationGroupExludeIds[group])
                        excludedIds = excludedIds.concat(MapasCulturais.agentRelationGroupExludeIds[group]);
                } catch (e) { }

                excludedIds = excludedIds.concat(
                    $selector.parents('.js-related-group').find('.agentes .avatar .selos').map(
                        function () { return $(this).data('id'); }
                    ).toArray());


                if (excludedIds.length > 0)
                    searchParams.id = '!in(' + excludedIds.toString() + ')';

                return searchParams;
            }
        },

        parentSpace: {
            selection: function (entity, $selector) {
                return entity.name;
            },

            noMatches: function (term, $selector) {
                return 'Nenhum espaço encontrado.';
            },

            onClear: function ($selector) {
            },

            ajaxData: function (searchParams, $selector) {
                if (MapasCulturais.entity.id) {
                    var excludeIds = [MapasCulturais.entity.id].concat(MapasCulturais.entity.childrenIds);
                    if (excludeIds) {
                        searchParams['id'] = '!IN(' + excludeIds + ')';
                    }

                }
                return searchParams;
            }
        },

        parentProject: {
            selection: function (entity, $selector) {
                return entity.name;
            },

            noMatches: function (term, $selector) {
                return 'Nenhum projeto encontrado.';
            },

            onClear: function ($selector) {
            },

            ajaxData: function (searchParams, $selector) {
                if (MapasCulturais.entity.id) {
                    var excludeIds = [MapasCulturais.entity.id].concat(MapasCulturais.entity.childrenIds);
                    if (excludeIds) {
                        searchParams['id'] = '!IN(' + excludeIds + ')';
                    }

                }

                return searchParams;
            }
        },

        projectRegistration: {
            onSave: function ($selector) {
                var entity = $selector.data('entity'),
                    avatarUrl = MapasCulturais.defaultAvatarURL,
                    shortDescription = entity.shortDescription.replace("\n", '<br/>');

                $('#registration-agent-id').val(entity.id);

            },

            onHidden: function ($selector) {
                $selector.removeClass('editable-unsaved');
            },

            selection: function (entity, $selector) {
                $selector.data('entity', entity);
                return entity.name;
            },

            noMatches: function (term, $selector) {
                return 'Nenhum agente encontrado.';
            },

            onClear: function ($selector) { },

            ajaxData: function (searchParams, $selector) {
                var excludedIds = MapasCulturais.request.controller === 'agent' && MapasCulturais.entity.id ? [MapasCulturais.entity.id] : [];

                excludedIds.push($selector.data('value'));

                if (excludedIds.length > 0)
                    searchParams.id = '!in(' + excludedIds.toString() + ')';

                if (!MapasCulturais.cookies.get('mapasculturais.adm'))
                    searchParams.user = 'eq(@me)';

                if ($selector.data('profiles-only'))
                    searchParams.isUserProfile = 'eq(true)';

                searchParams['@select'] += ',shortDescription,';
                return searchParams;
            }

        },

        changeOwner: {
            onSave: function ($selector) {
                var entity = $selector.data('entity'),
                    avatarUrl = MapasCulturais.defaultAvatarURL,
                    shortDescription = entity.shortDescription.replace("\n", '<br/>');

                $selector.data('value', entity.id);

                try {
                    avatarUrl = entity.files.avatar.files.avatarSmall.url;
                } catch (e) { };

                $selector.parents('.js-owner').find('.js-owner-avatar').attr('src', avatarUrl);
                $selector.parents('.js-owner').find('.js-owner-description').html(shortDescription);

            },

            selection: function (entity, $selector) {

                if ($selector.data('inputSelector')) {
                    var $input = $($selector.data('inputSelector'));
                    $input.val(entity.id);

                    var selectedEntity = $('input[name="objectType"]:checked').val();
                    //console.log(selectedEntity);
                }

                $selector.data('entity', entity);
                return entity.name;
            },

            noMatches: function (term, $selector) {
                return 'Nenhum agente encontrado.';
            },

            onClear: function ($selector) { },

            ajaxData: function (searchParams, $selector) {
                const value = $selector.editable('getValue').ownerId;

                var excludedIds = value ? [value] : [];

                if (MapasCulturais.request.controller === 'agent' && MapasCulturais.entity.id) {
                    excludedIds.push(MapasCulturais.entity.id);
                    if (MapasCulturais.entity.childrenIds) {
                        excludedIds = excludedIds.concat(MapasCulturais.entity.childrenIds);
                    }
                }

                if (excludedIds.length > 0)
                    searchParams.id = '!in(' + excludedIds.toString() + ')';

                searchParams['@select'] += ',shortDescription';
                searchParams['@permissions'] = '@control';
                return searchParams;
            }
        }
    }
};


MapasCulturais.cookies = {
    get: function (name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    },

    set: function (key, value, options) {
        options = $.extend({}, options);

        if (value == null) {
            options.expires = -1;
        }

        if (typeof options.expires === 'number') {
            var days = options.expires, t = options.expires = new Date();
            t.setDate(t.getDate() + days);
            options.expires = options.expires.toUTCString();
        } else {
            options.expires = 'Session';
        }

        value = String(value);

        return (document.cookie = [
            encodeURIComponent(key), '=', options.raw ? value : encodeURIComponent(value),
            options.expires ? '; expires=' + options.expires : '', // use expires attribute, max-age is not supported by IE
            options.path ? '; path=' + options.path : '',
            options.domain ? '; domain=' + options.domain : '',
            options.secure ? '; secure' : ''
        ].join(''));
    }
};

$(function () {
    if (MapasCulturais.request.controller === 'entityrevision') {
        var obj = MapasCulturais.entity.object;
        var nameContainer = obj.controllerId + '-map';
        var $mapContainer = $('#' + nameContainer);
        var mapsDefaults = MapasCulturais.mapsDefaults;

        var defaultIconOptions = {
            shadowUrl: MapasCulturais.assets.pinShadow,
            iconSize: [35, 43], // size of the icon
            shadowSize: [40, 16], // size of the shadow
            iconAnchor: [20, 43], // point of the icon which will correspond to marker's location
            shadowAnchor: [6, 16], // the same for the shadow
            popupAnchor: [-3, -76] // point from which the popup should open relative to the iconAnchor
        };

        var latitude = $('#latitude').val();
        var longitude = $('#longitude').val();
        var config = {
            zoomControl: false,
            zoomMax: obj.zoom_max || mapsDefaults.zoomMax,
            zoomMin: obj.zoom_min || mapsDefaults.zoomMin,
            zoom: mapsDefaults.zoomApproximate || mapsDefaults.zoomDefault,
            center: new L.LatLng(latitude || mapsDefaults.latitude, longitude || mapsDefaults.longitude)
        };

        var openStreetMap = L.tileLayer(MapasCulturais.mapsTileServer, {
            attribution: 'Dados e Imagens &copy; <a href="http://www.openstreetmap.org/copyright" rel="noopener noreferrer">Contrib. OpenStreetMap</a>, ',
            maxZoom: config.zoomMax,
            minZoom: config.zoomMin
        });

        var map = new L.Map(nameContainer, config).addLayer(openStreetMap);
        $(this).data('leaflet-map', map);

        var marker = new L.marker(map.getCenter(), { draggable: true });
        var markerIcon = {};
        var opts = (JSON.parse(JSON.stringify(defaultIconOptions)));
        opts.iconUrl = MapasCulturais.assets.pinAgent;
        markerIcon = {
            icon: L.icon(opts)
        };

        if (Object.keys(markerIcon).length) {
            marker.setIcon(markerIcon.icon);
            map.addLayer(marker);
        }

        (new L.Control.Zoom({ position: 'bottomright' })).addTo(map);

        var setState = function (event) {
            var center = map.getCenter();
            var zoom = event.target._zoom;

            $('#latitude').editable('setValue', center.lat);
            $('#longitude').editable('setValue', center.lng);
            $('#zoom_default').editable('setValue', zoom);
        };

        map.on('zoomend', setState);
        map.on('moveend', setState);
    }
});

(function () {
    if (MapasCulturais.assets.favicon) {
        var link = document.querySelector("link[rel*='icon']") || document.createElement('link');
        link.type = 'image/x-icon';
        link.rel = 'shortcut icon';
        link.href = MapasCulturais.assets.favicon;
        document.getElementsByTagName('head')[0].appendChild(link);
    }
}());

$(function () {
    if (MapasCulturais.request.controller === 'entityrevision') {
        var obj = MapasCulturais.entity.object;
        var nameContainer = obj.controllerId + '-map';
        var $mapContainer = $('#' + nameContainer);
        var mapsDefaults = MapasCulturais.mapsDefaults;

        var defaultIconOptions = {
            shadowUrl: MapasCulturais.assets.pinShadow,
            iconSize: [35, 43], // size of the icon
            shadowSize: [40, 16], // size of the shadow
            iconAnchor: [20, 43], // point of the icon which will correspond to marker's location
            shadowAnchor: [6, 16], // the same for the shadow
            popupAnchor: [-3, -76] // point from which the popup should open relative to the iconAnchor
        };

        var latitude = $('#latitude').val();
        var longitude = $('#longitude').val();
        var config = {
            zoomControl: false,
            zoomMax: obj.zoom_max || mapsDefaults.zoomMax,
            zoomMin: obj.zoom_min || mapsDefaults.zoomMin,
            zoom: mapsDefaults.zoomApproximate || mapsDefaults.zoomDefault,
            center: new L.LatLng(latitude || mapsDefaults.latitude, longitude || mapsDefaults.longitude)
        };

        var openStreetMap = L.tileLayer(MapasCulturais.mapsTileServer, {
            attribution: 'Dados e Imagens &copy; <a href="http://www.openstreetmap.org/copyright" rel="noopener noreferrer">Contrib. OpenStreetMap</a>, ',
            maxZoom: config.zoomMax,
            minZoom: config.zoomMin
        });

        var map = new L.Map(nameContainer, config).addLayer(openStreetMap);
        $(this).data('leaflet-map', map);

        var marker = new L.marker(map.getCenter(), { draggable: true });
        var markerIcon = {};
        var opts = (JSON.parse(JSON.stringify(defaultIconOptions)));
        opts.iconUrl = MapasCulturais.assets.pinAgent;
        markerIcon = {
            icon: L.icon(opts)
        };

        if (Object.keys(markerIcon).length) {
            marker.setIcon(markerIcon.icon);
            map.addLayer(marker);
        }

        (new L.Control.Zoom({ position: 'bottomright' })).addTo(map);

        var setState = function (event) {
            var center = map.getCenter();
            var zoom = event.target._zoom;

            $('#latitude').editable('setValue', center.lat);
            $('#longitude').editable('setValue', center.lng);
            $('#zoom_default').editable('setValue', zoom);
        };

        map.on('zoomend', setState);
        map.on('moveend', setState);
    }
});
