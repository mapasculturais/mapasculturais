$(function(){
    window.evaluationForms = {};

    const insideEmbedTools = MapasCulturais.insideEmbedTools;

    if($('#documentary-evaluation-form').length || insideEmbedTools && MapasCulturais?.evaluationConfiguration?.type?.id == 'documentary'){
        var template = MapasCulturais.TemplateManager.getTemplate('documentary-evaluation-form-template');

        var $container = $('#documentary-evaluation-form--container');

        function getForm($field){
            var $form = window.evaluationForms[$field.attr('id')];
            if(!$form){
                var id = $field.data('fieldId');
                var label = $field.find('label:first').text().trim();

                if(!label && $field.hasClass('registration-list-item')){
                    label = $field.find('.registration-label').text();
                    id = label;
                }
                if(!id) return;

                if(label[0] === '*'){
                    label = label.substr(1).trim();
                }

                if(label[label.length - 1] === ':'){
                    label = label.substr(0, label.length -1).trim();
                }

                var data = {
                    id: id,
                    label: label
                };

                var val = MapasCulturais.evaluation ? MapasCulturais.evaluation.evaluationData[id] : null;
                if(val){
                    data.empty = true;
                    data.invalid = false;
                    data.valid = false;

                    if(val.evaluation == 'invalid'){
                        data.empty = false;
                        data.invalid = true;
                    } else if(val.evaluation == 'valid'){
                        data.empty = false;
                        data.valid = true;
                    }

                    data.obs = val.obs;
                    data.obs_items = val.obs_items;
                } else {
                    data.empty = true;
                    data.invalid = false;
                    data.obs = '';
                    data.obs_items = '';
                }

                $form = $(Mustache.render(template, data));
                $container.append($form);
                window.evaluationForms[$field.attr('id')] = $form;

                $form.find('input').on('click', function(){
                    let className;
                    if($(this).is(':checked') == 1){
                        if($(this).val() == 'valid'){
                            className = 'evaluation-valid';
                        } else if($(this).val() == 'invalid'){
                            className = 'evaluation-invalid';
                        } else {
                            className = 'evaluation-empty';
                        }
                    }

                    if(insideEmbedTools) {
                        window.parent.postMessage({
                            type: 'evaluationRegistration.setClass',
                            className: className,
                        });
                    } else {
                        setClass(className);
                    }
                });
            }


            return $form;
        }
        $('li.registration-list-item').each(function(){
            if (insideEmbedTools) {
                window.parent.postMessage({
                    type: 'evaluationForm.getForm',
                    element: this.outerHTML
                })
            } else {
                getForm($(this));
            }
        });

        var intervalCount = 0;
        var interval = setInterval(function(){
            intervalCount += 50;
            // espera o angular renderizar a lista
            if(intervalCount >= 500){
                clearInterval(interval);
            }
            if(MapasCulturais.evaluation && MapasCulturais.evaluation.evaluationData){
                for (var id in MapasCulturais.evaluation.evaluationData){
                    var $field = $('#field_' + id);
                    var val = MapasCulturais.evaluation.evaluationData[id];

                    $('li.registration-list-item').each(function(){
                        var agentFieldId = $(this).find('.registration-label').text();

                        if(agentFieldId === id){
                            $field = $(this);
                        }

                    });

                    if (insideEmbedTools) {
                        window.parent.postMessage({
                            type: 'evaluationForm.getForm',
                            element: $field.get(0)?.outerHTML
                        })
                    } else {
                        getForm($field);
                    }
                    if(val.evaluation && val.evaluation == 'invalid'){
                        $field.addClass('evaluation-invalid');

                    } else if(val.evaluation && val.evaluation == 'valid'){
                        $field.addClass('evaluation-valid');

                    } else {
                        $field.addClass('evaluation-empty');
                    }
                }
            }

            $('li.js-field, li.registration-list-item').css('cursor', 'pointer');
        }, 50);

        setTimeout(function() {
            let $lastField;
            let c;
            $('li.js-field, li.registration-list-item').each(function () {                
                c += 100;
                $lastField = $(this);
                let $self = $(this);
                setTimeout(function() {
                    $self.click();
                }, c);
            })
            
            setTimeout(function() {
                if($lastField){
                    $lastField.click();
                }
            }, c+5000);
        }, 200);

        $('body').on('click', 'li.js-field, li.registration-list-item', function(){
            var $field = $(this);
            
            if($field.hasClass('field-shadow')){
                $field.removeClass('field-shadow');

                if (insideEmbedTools) {
                    window.parent.postMessage({
                        type: 'evaluationForm.closeForm',
                        element: $field.get(0).outerHTML
                    })
                } else {
                    closeForm($field);
                }
            } else {
                $('li.js-field.field-shadow, li.registration-list-item.field-shadow').removeClass('field-shadow');
                $field.addClass('field-shadow');

                if (insideEmbedTools) {
                    window.parent.postMessage({
                        type: 'evaluationForm.openForm',
                        element: $field.get(0).outerHTML,
                        fieldName: $field.get(0).id,
                        fieldId: $field.get(0).dataset.fieldId,
                        fieldType: $field.get(0).dataset.fieldType
                    })
                } else {
                    openForm($field);
                }
            }
        });
    }

    function openForm($field) {
        $('.documentary-evaluation-form--field').hide();
        $('#documentary-evaluation-info').slideUp('fast');
        
        var $form = getForm($field);
        $form.fadeIn('fast');
    }

    function closeForm($field) {
        $('#documentary-evaluation-info').slideDown('fast');
        $('.documentary-evaluation-form--field').hide();
    }

    function setClass(className) {
        const $field = $('li.js-field.field-shadow, li.registration-list-item.field-shadow')

        $field.removeClass('evaluation-empty');
        $field.removeClass('evaluation-valid');
        $field.removeClass('evaluation-invalid');

        $field.addClass(className);
    }

    function getSealAvatarUrl(seal) {
        const transformations = seal?.files?.avatar?.transformations;
        return transformations?.avatarSmall?.url || transformations?.avatarMedium?.url || transformations?.avatarBig?.url || null;
    }

    function renderSealValidatorAvatars($field, seals) {
        $field.find('.seal-validator-field__seals').remove();

        if(!seals || seals.length === 0) {
            return;
        }

        const $seals = $('<div class="seal-validator-field__seals" aria-label="Selos validadores do campo"></div>');

        seals.forEach((seal) => {
            const $seal = $('<span class="seal-validator-field__seal"></span>');
            const avatarUrl = getSealAvatarUrl(seal);

            if(avatarUrl) {
                $('<img class="seal-validator-field__seal-image" alt="">')
                    .attr('src', avatarUrl)
                    .attr('title', seal.name || 'Selo validador')
                    .appendTo($seal);
            } else {
                $seal
                    .addClass('seal-validator-field__seal--fallback')
                    .attr('title', seal.name || 'Selo validador')
                    .text('✓');
            }

            $seals.append($seal);
        });

        $field.append($seals);
    }

    function getSealValidatorStatusClass(seals) {
        if (!(seals || []).some((seal) => seal.hasSealRelation || seal.validateDate)) {
            return 'seal-validator-field--missing';
        }

        if ((seals || []).some((seal) => seal.fieldStatus === 'expired')) {
            return 'seal-validator-field--expired';
        }

        if ((seals || []).some((seal) => seal.fieldStatus === 'about_to_expire')) {
            return 'seal-validator-field--about-to-expire';
        }

        return 'seal-validator-field--valid';
    }

    function setSealValidatorMarker(fieldId, isValidator, hasInvalidator, seals = []) {
        const $field = $(`#field_${fieldId}`);
        if(!$field.length) {
            return;
        }

        $field.removeClass('seal-validator-field');
        $field.removeClass('seal-validator-field--valid seal-validator-field--about-to-expire seal-validator-field--expired seal-validator-field--missing');
        $field.find('.seal-validator-field__seals').remove();

        if(isValidator) {
            $field.addClass('seal-validator-field');
            renderSealValidatorAvatars($field, seals);
            $field.addClass(getSealValidatorStatusClass(seals));
        }
    }

    function setSealValidatorMarkers(fields) {
        $('.seal-validator-field, .seal-validator-field--valid, .seal-validator-field--about-to-expire, .seal-validator-field--expired, .seal-validator-field--missing')
            .find('.seal-validator-field__seals')
            .remove()
            .end()
            .removeClass('seal-validator-field seal-validator-field--valid seal-validator-field--about-to-expire seal-validator-field--expired seal-validator-field--missing');

        (fields || []).forEach((field) => {
            setSealValidatorMarker(field.fieldId, field.isValidator, field.hasInvalidator, field.seals);
        });
    }

    function clearStyles(className) {
        const $field = $('li.js-field.field-shadow, li.registration-list-item.field-shadow')
        $field.removeClass('field-shadow');
    }

    window.addEventListener("message", function(event) {
        
        switch (event?.data?.type) {
            case 'evaluationForm.openForm':
                openForm($(event.data.element));
            break;
            case 'evaluationForm.closeForm':
                closeForm();
            break;
            case 'evaluationForm.closeForm':
                getForm($(event.data.element));
            break;

            case 'evaluationRegistration.setClass':
                setClass(event.data.className);
            break;

            case 'evaluationRegistration.setSealValidator':
                setSealValidatorMarker(event.data.fieldId, event.data.isValidator, event.data.hasInvalidator, event.data.seals);
            break;

            case 'evaluationRegistration.setSealValidatorFields':
                setSealValidatorMarkers(event.data.fields);
            break;
        
            case 'evaluationRegistration.clearStyles':
                clearStyles();
            break;
        }
    });
});
