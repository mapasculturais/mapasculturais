(function($){
    MapasCulturais.Messages.showMessage = function(type, message) {
        if (type == "danger") {
            type = "error";
        }

        let sendMessage = {
            type: 'message',
            data: {
                type,
                message
            }
        }

        window.parent.postMessage(sendMessage, '*');
    }

    function getSealAvatarUrl(seal) {
        const transformations = seal?.files?.avatar?.transformations;
        return transformations?.avatarSmall?.url || transformations?.avatarMedium?.url || transformations?.avatarBig?.url || seal?.files?.avatar?.url || null;
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

    function applySealValidatorFields(fields) {
        $('.seal-validator-field, .seal-validator-field--valid, .seal-validator-field--about-to-expire, .seal-validator-field--expired, .seal-validator-field--missing')
            .find('.seal-validator-field__seals')
            .remove()
            .end()
            .removeClass('seal-validator-field seal-validator-field--valid seal-validator-field--about-to-expire seal-validator-field--expired seal-validator-field--missing');

        (fields || []).forEach((field) => {
            const $field = $(`#field_${field.fieldId}`);
            if (!$field.length || !field.isValidator) {
                return;
            }

            $field.addClass('seal-validator-field');
            renderSealValidatorAvatars($field, field.seals);
            $field.addClass(getSealValidatorStatusClass(field.seals));
        });
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

    function getParentSealValidatorFields() {
        try {
            const config = window.parent?.$MAPAS?.config?.evaluationFormSealValidators;
            if (!config?.enabled) {
                return [];
            }

            return (config.fields || []).map((field) => ({
                fieldId: field.fieldId,
                isValidator: true,
                hasInvalidator: field.hasInvalidator,
                seals: (field.validators || []).map((validator) => ({
                    sealId: validator.sealId,
                    name: validator.sealName,
                    fieldStatus: validator.fieldStatus,
                    expiryDate: validator.expiryDate,
                    isInvalidator: validator.isInvalidator,
                    hasSealRelation: validator.hasSealRelation,
                    validateDate: validator.validateDate,
                    files: validator.files,
                })),
            }));
        } catch (e) {
            return [];
        }
    }
    
    window.addEventListener("message", function(event) {            
        if (event.data == "registration.save") {
            window.$registrationScope.saveRegistration().success((result) => {
                window.parent.postMessage({type: 'registration.saved'}, '*');
            }).error((errors) => {
                window.parent.postMessage({type: 'registration.saved', ...errors}, '*');
            })
        }

        if (event.data?.type === 'evaluationRegistration.setSealValidatorFields') {
            applySealValidatorFields(event.data.fields);
        }
    });

    if(MapasCulturais.registration) {
        window.lastSentRegistrationFields = JSON.parse(JSON.stringify(MapasCulturais.registration));
    } else {
        window.lastSentRegistrationFields = {};
    }
    
    function postToParent() {
        if(!document.body) {
            return;
        }
        window.parent.postMessage({
            type: 'resize',
            data: {
                height: document.body.offsetHeight + 30,
            }
        }, '*');

        
        const registrationFields = {};

        for (let key in MapasCulturais.registration) {
            let val = null;
            if (key.indexOf('field_') === 0) {
                if(MapasCulturais.registration[key] instanceof Date){
                    val = moment(MapasCulturais.registration[key]).format("YYYY-MM-DD");
                }else if(MapasCulturais.registration[key] !== undefined) {
                    val = JSON.parse(JSON.stringify(MapasCulturais.registration[key]));
                }

                if(val instanceof Object) {
                    if(val['$$hashKey']) {
                        delete val['$$hashKey'];
                    }
                }

                if(val instanceof Array) {
                    for(let item of val) {
                        if(item['$$hashKey']) {
                            delete item['$$hashKey'];
                        }
                    }
                }
            } else {
                continue;
            }

            if(val !== undefined && JSON.stringify(val) !== JSON.stringify(window.lastSentRegistrationFields[key])) {
                window.lastSentRegistrationFields[key] = val;
                if(val instanceof Array) {
                    val = val.filter((item) => item !== '[]');
                } 
                registrationFields[key] = val;
            }
        }

        if(JSON.stringify(registrationFields) != '{}') {
            window.parent.postMessage({
                type: 'registration.update',
                data: registrationFields,
            }, '*');
        }
    }

    setInterval(postToParent, 50);

    [300, 1000, 2500, 5000].forEach((delay) => {
        setTimeout(() => applySealValidatorFields(getParentSealValidatorFields()), delay);
    });

    $(document).ready(function() {
        $("body").on("click", "a[href]", function(event) {
            event.preventDefault();
            window.open($(this).attr("href"), "_top");
        });
    });
  
})(jQuery)