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
    
    window.addEventListener("message", function(event) {            
        if (event.data == "registration.save") {
            window.$registrationScope.saveRegistration().success((result) => {
                window.parent.postMessage({type: 'registration.saved'}, '*');
            }).error((errors) => {
                window.parent.postMessage({type: 'registration.saved', ...errors}, '*');
            })
        }

        if (event.data?.type === 'evaluationRegistration.setSealValidatorFields') {
            $('.seal-validator-field, .seal-validator-field--invalidator')
                .removeClass('seal-validator-field seal-validator-field--invalidator');

            (event.data.fields || []).forEach((field) => {
                const $field = $(`#field_${field.fieldId}`);
                if (!$field.length || !field.isValidator) {
                    return;
                }

                $field.addClass('seal-validator-field');
                if (field.hasInvalidator) {
                    $field.addClass('seal-validator-field--invalidator');
                }
            });
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

    $(document).ready(function() {
        $("body").on("click", "a[href]", function(event) {
            event.preventDefault();
            window.open($(this).attr("href"), "_top");
        });
    });
  
})(jQuery)