$(function(){
    var template = MapasCulturais.TemplateManager.getTemplate('documentary-evaluation-form-template');

    var $container = $('#documentary-evaluation-form--container');


    function getForm($field){
        var $form = $field.data('$form');
        if(!$form){
            var id = $field.data('fieldId');
            var label = $field.find('label:first').text().trim();
            
            if(!label && $field.hasClass('registration-list-item')){
                label = $field.find('.registration-label').text();
                id = label;
            }

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

            var evaluation = MapasCulturais.evaluation ? MapasCulturais.evaluation.evaluationData[id] : null;

            if(evaluation){
                if(evaluation.invalid){
                    data.invalid = true;
                } else {
                    data.invalid = false;
                }
                data.obs = evaluation.obs;
            } else {
                data.invalid = false;
                data.obs = '';
            }

            $form = $(Mustache.render(template, data));
            $container.append($form);
            $field.data('$form', $form);

            $form.find('input').on('click', function(){
                if($(this).is(':checked') == 1){
                    $field.removeClass('evaluation-empty');
                    $field.addClass('evaluation-invalid');
                } else {
                    $field.removeClass('evaluation-invalid');
                    $field.addClass('evaluation-empty');
                }
            });
        }

        return $form;
    }
    $('li.registration-list-item').each(function(){
        getForm($(this));
    });

    if(MapasCulturais.evaluation && MapasCulturais.evaluation.evaluationData){
        setTimeout(function(){
            for (var id in MapasCulturais.evaluation.evaluationData){
                var $field = $('#registration-field-' + id);
                var evaluation = MapasCulturais.evaluation.evaluationData[id];
                
                $('li.registration-list-item').each(function(){
                    var agentFieldId = $(this).find('.registration-label').text();
                    
                    if(agentFieldId === id){
                        $field = $(this);
                    }
                    
                });
                
                getForm($field);
                if(evaluation.invalid){
                    $field.addClass('evaluation-invalid');
                } else {
                    $field.addClass('evaluation-empty');
                }
            }
            
            
        }, 50);
    }

    setTimeout(function(){
        $('li.js-field, li.registration-list-item').css('cursor', 'pointer');
    },50);

    $('body').on('click', 'li.js-field, li.registration-list-item', function(){
        var $field = $(this);
        var $form = getForm($field);


        // $('window,html,body').animate({scrollTop: $field.offset().top - 75},200);
        $('.documentary-evaluation-form--field').hide();

        if($field.hasClass('field-shadow')){
            $('#documentary-evaluation-info').slideDown('fast');
            $('.documentary-evaluation-form--field').hide();
            $field.removeClass('field-shadow');
        } else {
            $('#documentary-evaluation-info').slideUp('fast');
            $('li.js-field.field-shadow, li.registration-list-item.field-shadow').removeClass('field-shadow');
            $field.addClass('field-shadow');
            $form.fadeIn('fast');
        }
    });
});
