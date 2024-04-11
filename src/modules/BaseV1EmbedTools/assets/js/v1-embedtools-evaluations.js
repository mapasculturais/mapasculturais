$(function(){
    const insideEmbedTools = MapasCulturais.insideEmbedTools;

    if(insideEmbedTools){
         $(".js-evaluation-submit").attr("style", "display:none")
    }

    var $formContainer = $('#registration-evaluation-form');
    var $form = $formContainer.find('form');
    var $list = $('#registrations-list-container');
    var $header = $('#main-header');
    $(window).scroll(function(){
        var top = parseInt($header.css('top'));
        $formContainer.css('margin-top', top);
        $list.css('margin-top', top);
    });

    function saveEvaluation(){
        clearTimeout(window.saveEvaluationTimeout);
        window.saveEvaluationTimeout = setTimeout(() => {
            var data = $form.serialize();
            var url = MapasCulturais.createUrl('registration', 'saveEvaluation', {'0': MapasCulturais.request.id});
            $.post(url, data, function(r){
                window.parent.postMessage({type:'evaluation.save.success'});
            }).fail(function(rs) {
                if(rs.responseJSON && rs.responseJSON.error){
                    window.parent.postMessage({type:'evaluation.save.error', error: rs.responseJSON.data});
                }
            });
        }, 100);
    }

    function finishEvaluation(checkButton = null) {
        var $button = checkButton ? $(checkButton) : null;
        var url = MapasCulturais.createUrl('registration', 'saveEvaluation', {'0': MapasCulturais.request.id, 'status': 'evaluated'});
        var data = $form.serialize();

        if(!data){
            MapasCulturais.Messages.success("Preencha todos os campos");
        }

        $.post(url, data, function(r){
           window.parent.postMessage({type:'evaluation.send.success'});
        }).fail(function(rs) {
            if(rs.responseJSON && rs.responseJSON.error){
                window.parent.postMessage({type:'evaluation.send.error', error: rs.responseJSON.data});
            }
        });
    }

    window.addEventListener("message", function(event) {          
        if (event.data?.type == "evaluationForm.send") {
            finishEvaluation()
        }

        if (event.data?.type == "evaluationForm.save") {
            saveEvaluation()
        }
    });

    $formContainer.find('.js-evaluation-submit').on('click', function(){
        finishEvaluation(this)
    });


});
